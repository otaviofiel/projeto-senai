<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

$dados = json_decode(file_get_contents('php://input'), true);

if (!$dados) {
    echo json_encode(['sucesso' => false, 'erro' => 'Nenhum dado recebido.']);
    exit;
}

$itens = $dados['itens'];
$total = floatval($dados['total']);
$senha = rand(1, 99);
$status = 'Pendente';

if ($usarSupabase) {
    $novoPedido = [
        'senha' => $senha,
        'itens' => $itens,
        'total' => $total,
        'status' => $status
    ];

    $res = $supabase->insert('pedidos', $novoPedido);
    if ($res['sucesso']) {
        echo json_encode(['sucesso' => true, 'senha' => $senha]);
    } else {
        echo json_encode(['sucesso' => false, 'erro' => $res['erro']]);
    }
    exit;
}

// Fallback MySQL local
if ($conn && !$conn->connect_error) {
    $itensJson = json_encode($itens, JSON_UNESCAPED_UNICODE);
    $sql = "INSERT INTO pedidos (senha, itens, total, status) VALUES ('$senha', '$itensJson', '$total', '$status')";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['sucesso' => true, 'senha' => $senha]);
    } else {
        echo json_encode(['sucesso' => false, 'erro' => $conn->error]);
    }
    $conn->close();
    exit;
}

// Contingência offline caso nenhum banco esteja conectado
echo json_encode(['sucesso' => true, 'senha' => $senha]);
?>
