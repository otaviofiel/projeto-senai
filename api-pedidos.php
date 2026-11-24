<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    if ($usarSupabase) {
        $res = $supabase->select('pedidos', 'select=id,senha,itens,total,status&status=eq.Pendente&order=data_hora.asc');
        if ($res['sucesso'] && is_array($res['dados'])) {
            $pedidos = array_map(function($p) {
                if (is_string($p['itens'])) {
                    $p['itens'] = json_decode($p['itens'], true);
                }
                return $p;
            }, $res['dados']);
            echo json_encode($pedidos);
            exit;
        }
    }

    if ($conn && !$conn->connect_error) {
        $sql = "SELECT id, senha, itens, total, status FROM pedidos WHERE status = 'Pendente' ORDER BY data_hora ASC";
        $resultado = $conn->query($sql);
        $pedidos = [];
        if ($resultado && $resultado->num_rows > 0) {
            while ($linha = $resultado->fetch_assoc()) {
                $linha['itens'] = json_decode($linha['itens'], true);
                $pedidos[] = $linha;
            }
        }
        echo json_encode($pedidos);
        $conn->close();
        exit;
    }

    echo json_encode([]);

} elseif ($metodo === 'POST') {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (isset($dados['id'])) {
        $idPedido = intval($dados['id']);

        if ($usarSupabase) {
            $res = $supabase->update('pedidos', ['status' => 'Finalizado'], 'id=eq.' . $idPedido);
            echo json_encode(['sucesso' => $res['sucesso'], 'erro' => $res['erro']]);
            exit;
        }

        if ($conn && !$conn->connect_error) {
            $sql = "UPDATE pedidos SET status = 'Finalizado' WHERE id = $idPedido";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['sucesso' => true]);
            } else {
                echo json_encode(['sucesso' => false, 'erro' => $conn->error]);
            }
            $conn->close();
            exit;
        }
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'ID do pedido não enviado.']);
    }
}
?>
