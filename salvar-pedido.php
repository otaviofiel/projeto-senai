<?php
header('Content-Type: application/json');
include 'conexao.php';

// Recebe os dados do carrinho enviados pelo JavaScript
$dados = json_decode(file_get_contents('php://input'), true);

if ($dados) {
    // Pega os valores enviados
    $itens = json_encode($dados['itens'], JSON_UNESCAPED_UNICODE); // Transforma a lista de itens em texto
    $total = $dados['total'];
    
    // Gera uma senha aleatória entre 1 e 99 (igual fazíamos no JS)
    $senha = rand(1, 99);
    $status = 'Pendente';

    // Salva no banco de dados
    $sql = "INSERT INTO pedidos (senha, itens, total, status) VALUES ('$senha', '$itens', '$total', '$status')";
    
    if ($conn->query($sql) === TRUE) {
        // Se der certo, devolve a senha gerada para o JavaScript mostrar na tela final
        echo json_encode(['sucesso' => true, 'senha' => $senha]);
    } else {
        echo json_encode(['sucesso' => false, 'erro' => $conn->error]);
    }
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Nenhum dado recebido']);
}

$conn->close();
?>