<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';
require_once 'config.php';

// Recebe a requisição JSON do front-end
$dados = json_decode(file_get_contents('php://input'), true);

if (!$dados || empty($dados['itens']) || !isset($dados['total'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados do pedido inválidos ou incompletos.']);
    exit;
}

$itensCarrinho = $dados['itens'];
$total = floatval($dados['total']);
$metodo = isset($dados['metodo']) ? $dados['metodo'] : 'pix'; // 'pix', 'cartao', ou 'dinheiro'
$trocoPara = isset($dados['troco_para']) ? $dados['troco_para'] : null;

// Gera senha de atendimento aleatória entre 1 e 99
$senha = rand(1, 99);
$status = 'Pendente';
$itensJson = json_encode($itensCarrinho, JSON_UNESCAPED_UNICODE);

// Salva o pedido inicial no banco de dados
$stmt = $conn->prepare("INSERT INTO pedidos (senha, itens, total, status) VALUES (?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("isds", $senha, $itensJson, $total, $status);
    $stmt->execute();
    $pedidoId = $stmt->insert_id;
    $stmt->close();
} else {
    // Fallback caso prepare falhe
    $conn->query("INSERT INTO pedidos (senha, itens, total, status) VALUES ('$senha', '$itensJson', '$total', '$status')");
    $pedidoId = $conn->insert_id;
}

// Se o pagamento for em DINHEIRO, não precisa do Mercado Pago
if ($metodo === 'dinheiro') {
    echo json_encode([
        'sucesso' => true,
        'tipo' => 'dinheiro',
        'senha' => $senha,
        'pedido_id' => $pedidoId
    ]);
    $conn->close();
    exit;
}

// Se o token ainda não foi preenchido com as credenciais reais
if (MP_ACCESS_TOKEN === 'SEU_ACCESS_TOKEN_AQUI' || empty(MP_ACCESS_TOKEN)) {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Token do Mercado Pago não configurado. Por favor, adicione seu Access Token no arquivo config.php.'
    ]);
    $conn->close();
    exit;
}

// Monta os itens no formato da API de Preferências do Mercado Pago
$itensMp = [];
foreach ($itensCarrinho as $item) {
    $precoUnitario = floatval($item['preco']);
    $qtd = intval($item['quantidade']);
    if ($precoUnitario <= 0 || $qtd <= 0) continue;

    $itensMp[] = [
        "title" => substr($item['nome'], 0, 250),
        "quantity" => $qtd,
        "currency_id" => "BRL",
        "unit_price" => $precoUnitario
    ];
}

// Se por algum motivo de arredondamento a soma dos itens divergir ou itens vazios
if (empty($itensMp)) {
    $itensMp[] = [
        "title" => "Pedido Cantina SENAI #" . $senha,
        "quantity" => 1,
        "currency_id" => "BRL",
        "unit_price" => $total
    ];
}

// Configura métodos de pagamento excluídos caso o usuário tenha clicado especificamente em PIX ou Cartão
$paymentMethods = [
    "installments" => 6
];

if ($metodo === 'pix') {
    // Permite PIX (bank_transfer)
    $paymentMethods["excluded_payment_types"] = [
        ["id" => "ticket"] // exclui boleto bancário se quiser agilidade
    ];
}

$baseUrl = rtrim(BASE_URL, '/');

// Monta o payload da Preferência do Mercado Pago Checkout Pro
$preferenceData = [
    "items" => $itensMp,
    "payer" => [
        "name" => "Aluno / Cliente SENAI",
        "email" => "cliente@filazero.senai.br"
    ],
    "back_urls" => [
        "success" => "{$baseUrl}/pedido-final.html?senha={$senha}&status=aprovado&pedido={$pedidoId}",
        "pending" => "{$baseUrl}/pedido-final.html?senha={$senha}&status=pendente&pedido={$pedidoId}",
        "failure" => "{$baseUrl}/carrinho.html?status=falha"
    ],
    "auto_return" => "approved",
    "external_reference" => "PEDIDO-" . $pedidoId . "-SENHA-" . $senha,
    "statement_descriptor" => "CANTINA SENAI",
    "payment_methods" => $paymentMethods
];

// Chamada cURL nativa para a API oficial do Mercado Pago (sem necessidade de composer)
$ch = curl_init("https://api.mercadopago.com/checkout/preferences");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . MP_ACCESS_TOKEN
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preferenceData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro de conexão com o Mercado Pago: ' . $curlError
    ]);
    $conn->close();
    exit;
}

$mpResult = json_decode($response, true);

if ($httpCode >= 200 && $httpCode < 300 && isset($mpResult['init_point'])) {
    echo json_encode([
        'sucesso' => true,
        'tipo' => 'mercadopago',
        'senha' => $senha,
        'pedido_id' => $pedidoId,
        'init_point' => $mpResult['init_point'], // Link do Checkout Pro
        'sandbox_init_point' => isset($mpResult['sandbox_init_point']) ? $mpResult['sandbox_init_point'] : $mpResult['init_point']
    ]);
} else {
    $msgErro = isset($mpResult['message']) ? $mpResult['message'] : 'Falha ao gerar checkout no Mercado Pago.';
    echo json_encode([
        'sucesso' => false,
        'erro' => $msgErro,
        'detalhes' => $mpResult
    ]);
}

$conn->close();
?>
