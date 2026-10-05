<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'conexao.php';

$dados = json_decode(file_get_contents("php://input"), true);

$email = trim($dados['email'] ?? '');

if ($email === '') {
    echo json_encode([
        "sucesso" => false,
        "erro" => "Digite seu e-mail."
    ]);
    exit;
}

$sql = "SELECT id, nome, escola, turma, email, status
        FROM alunos
        WHERE email = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "sucesso" => false,
        "erro" => "Erro na consulta: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode([
        "sucesso" => false,
        "erro" => "Aluno não encontrado. Faça seu cadastro primeiro."
    ]);
    exit;
}

$aluno = $resultado->fetch_assoc();

// ALUNO PENDENTE
if ($aluno['status'] === 'pendente') {
    echo json_encode([
        "sucesso" => false,
        "status" => "pendente",
        "erro" => "Seu cadastro está aguardando aprovação da cantina."
    ]);
    exit;
}

// ALUNO RECUSADO
if ($aluno['status'] === 'recusado') {
    echo json_encode([
        "sucesso" => false,
        "status" => "recusado",
        "erro" => "Seu cadastro foi recusado pela cantina."
    ]);
    exit;
}

// ALUNO APROVADO
echo json_encode([
    "sucesso" => true,
    "status" => "aprovado",
    "aluno" => [
        "id" => $aluno['id'],
        "nome" => $aluno['nome'],
        "escola" => $aluno['escola'],
        "turma" => $aluno['turma'],
        "email" => $aluno['email']
    ]
]);

$stmt->close();
$conn->close();
?>
