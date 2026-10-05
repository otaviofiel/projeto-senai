<?php
// Avisa o navegador que vamos devolver dados no formato JSON
header('Content-Type: application/json');

// Puxa a chave do banco de dados
include 'conexao.php';

// Cria o comando para buscar todos os produtos
$sql = "SELECT id, nome, categoria, preco, imagem FROM produtos";
$resultado = $conn->query($sql);

$produtos = array();

// Se encontrar produtos, guarda tudo numa lista
if ($resultado->num_rows > 0) {
    while($linha = $resultado->fetch_assoc()) {
        $produtos[] = $linha;
    }
}

// Imprime a lista na tela no formato JSON
echo json_encode($produtos);

// Fecha a conexão
$conn->close();
?>