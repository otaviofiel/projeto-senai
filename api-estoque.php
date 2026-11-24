<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    if ($usarSupabase) {
        $res = $supabase->select('produtos', 'select=id,nome,estoque,imagem&order=nome.asc');
        if ($res['sucesso'] && is_array($res['dados'])) {
            echo json_encode($res['dados']);
            exit;
        }
    }

    if ($conn && !$conn->connect_error) {
        $sql = "SELECT id, nome, estoque, imagem FROM produtos ORDER BY nome ASC";
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

} elseif ($metodo === 'POST') {
    $dados = json_decode(file_get_contents('php://input'), true);
    
    if (isset($dados['id']) && isset($dados['estoque'])) {
        $id = intval($dados['id']);
        $novoEstoque = intval($dados['estoque']);

        if ($usarSupabase) {
            $res = $supabase->update('produtos', ['estoque' => $novoEstoque], 'id=eq.' . $id);
            if ($res['sucesso']) {
                echo json_encode(['sucesso' => true]);
                exit;
            } else {
                echo json_encode(['sucesso' => false, 'erro' => $res['erro']]);
                exit;
            }
        }

        if ($conn && !$conn->connect_error) {
            $sql = "UPDATE produtos SET estoque = $novoEstoque WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['sucesso' => true]);
            } else {
                echo json_encode(['sucesso' => false, 'erro' => $conn->error]);
            }
            $conn->close();
            exit;
        }
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos enviados.']);
    }
}
?>
