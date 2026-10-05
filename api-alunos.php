<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'conexao.php';

$sql = "SELECT id, nome, escola, turma, email, status, data_cadastro
        FROM alunos
        ORDER BY 
            CASE
                WHEN status = 'pendente' THEN 1
                WHEN status = 'aprovado' THEN 2
                ELSE 3
            END,
            id DESC";

$resultado = $conn->query($sql);

if (!$resultado) {
    echo json_encode([
        "sucesso" => false,
        "erro" => "Erro na consulta: " . $conn->error
    ]);
    exit;
}

$alunos = [];

while ($aluno = $resultado->fetch_assoc()) {
    $alunos[] = $aluno;
}

echo json_encode([
    "sucesso" => true,
    "alunos" => $alunos
]);

$conn->close();
?>
