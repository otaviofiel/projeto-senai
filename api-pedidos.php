<?php
header('Content-Type: application/json');
include 'conexao.php';

// Descobre o tipo de requisição (GET para buscar, POST para atualizar)
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    // BUSCAR PEDIDOS PENDENTES
    $sql = "SELECT id, senha, itens, total, status FROM pedidos WHERE status = 'Pendente' ORDER BY data_hora ASC";
    $resultado = $conn->query($sql);
    
    $pedidos = array();
    if ($resultado->num_rows > 0) {
        while($linha = $resultado->fetch_assoc()) {
            // Converte o texto JSON dos itens de volta para uma lista do PHP
            $linha['itens'] = json_decode($linha['itens'], true);
            $pedidos[] = $linha;
        }
    }
    echo json_encode($pedidos);

} else if ($metodo === 'POST') {
    // FINALIZAR PEDIDO
    $dados = json_decode(file_get_contents('php://input'), true);
    
    if (isset($dados['id'])) {
        $idPedido = $dados['id'];
        
        // Atualiza o status no banco de dados
        $sql = "UPDATE pedidos SET status = 'Finalizado' WHERE id = $idPedido";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['sucesso' => true]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => $conn->error]);
        }
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'ID do pedido não enviado.']);
    }
}

$conn->close();
?>