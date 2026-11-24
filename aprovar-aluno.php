<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

$dados = json_decode(file_get_contents("php://input"), true);
$id = intval($dados['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(["sucesso" => false, "erro" => "Aluno inválido."]);
    exit;
}

if ($usarSupabase) {
    $res = $supabase->update('alunos', ['status' => 'aprovado'], 'id=eq.' . $id);
    if ($res['sucesso'] && !empty($res['dados'])) {
        echo json_encode(["sucesso" => true, "mensagem" => "Aluno aprovado com sucesso."]);
    } else {
        $msgErro = !empty($res['dados']) ? ($res['erro'] ?? 'Erro desconhecido') : 'A política de segurança (RLS) do Supabase impediu a atualização. Verifique a chave service_role ou as políticas da tabela alunos.';
        echo json_encode(["sucesso" => false, "erro" => $msgErro]);
    }
    exit;
}

if ($conn && !$conn->connect_error) {
    $sql = "UPDATE alunos SET status = 'aprovado' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true, "mensagem" => "Aluno aprovado com sucesso."]);
    } else {
        echo json_encode(["sucesso" => false, "erro" => "Não foi possível aprovar o aluno: " . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

echo json_encode(["sucesso" => false, "erro" => "Sem conexão com o banco."]);
?>
