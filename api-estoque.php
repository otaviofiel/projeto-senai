<?php
header('Content-Type: application/json');
include 'conexao.php';

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    // BUSCAR TODOS OS PRODUTOS E SEU ESTOQUE
    $sql = "SELECT id, nome, estoque, imagem FROM produtos ORDER BY nome ASC";
    $resultado = $conn->query($sql);
    
    $produtos = array();
    if ($resultado->num_rows > 0) {
        while($linha = $resultado->fetch_assoc()) {
            $produtos[] = $linha;
        }
    }
    echo json_encode($produtos);

} else if ($metodo === 'POST') {
    // ATUALIZAR O ESTOQUE DE UM PRODUTO
    $dados = json_decode(file_get_contents('php://input'), true);
    
    if (isset($dados['id']) && isset($dados['estoque'])) {
        $id = $dados['id'];
        $novoEstoque = (int)$dados['estoque']; // Garante que seja um número inteiro
        
        $sql = "UPDATE produtos SET estoque = $novoEstoque WHERE id = $id";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['sucesso' => true]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => $conn->error]);
        }
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos enviados.']);
    }
}

$conn->close();
?>