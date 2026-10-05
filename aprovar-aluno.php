<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'conexao.php';

$dados = json_decode(file_get_contents("php://input"), true);

$id = intval($dados['id'] ?? 0);

if ($id <= 0) {
    echo json_encode([
        "sucesso" => false,
        "erro" => "Aluno inválido."
    ]);
    exit;
}

$sql = "UPDATE alunos SET status = 'aprovado' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Aluno aprovado com sucesso."
    ]);
} else {
    echo json_encode([
        "sucesso" => false,
        "erro" => "Não foi possível aprovar o aluno: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
