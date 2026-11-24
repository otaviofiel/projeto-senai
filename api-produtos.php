<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

if ($usarSupabase) {
    $res = $supabase->select('produtos', 'select=id,nome,categoria,preco,imagem,estoque&order=id.asc');
    if ($res['sucesso'] && is_array($res['dados'])) {
        echo json_encode($res['dados']);
        exit;
    }
}

// Fallback MySQL local
if ($conn && !$conn->connect_error) {
    $sql = "SELECT id, nome, categoria, preco, imagem, estoque FROM produtos ORDER BY id ASC";
    $resultado = $conn->query($sql);
    $produtos = [];
    if ($resultado && $resultado->num_rows > 0) {
        while ($linha = $resultado->fetch_assoc()) {
            $produtos[] = $linha;
        }
    }
    echo json_encode($produtos);
    $conn->close();
    exit;
}

echo json_encode([]);
?>
