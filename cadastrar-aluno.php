<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'conexao.php';

$dados = json_decode(file_get_contents("php://input"), true);

$nome = trim($dados['nome'] ?? '');
$escola = trim($dados['escola'] ?? '');
$turma = trim($dados['turma'] ?? '');
$email = trim($dados['email'] ?? '');

if ($nome === '' || $escola === '' || $turma === '' || $email === '') {
    echo json_encode([
        "sucesso" => false,
        "erro" => "Preencha todos os campos."
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "sucesso" => false,
        "erro" => "Digite um e-mail válido."
    ]);
    exit;
}

// Verifica se já existe
$sql = "SELECT id, status FROM alunos WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $aluno = $resultado->fetch_assoc();

    if ($aluno['status'] === 'pendente') {
        $mensagem = "Seu cadastro já está aguardando aprovação.";
    } elseif ($aluno['status'] === 'aprovado') {
        $mensagem = "Este e-mail já está aprovado.";
    } else {
        $mensagem = "Este e-mail já possui um cadastro recusado.";
    }

    echo json_encode([
        "sucesso" => false,
        "erro" => $mensagem
    ]);
    exit;
}

// Insere como pendente
$sql = "INSERT INTO alunos (nome, escola, turma, email, status) VALUES (?, ?, ?, ?, 'pendente')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $nome, $escola, $turma, $email);

if ($stmt->execute()) {
    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Cadastro realizado com sucesso."
    ]);
} else {
    echo json_encode([
        "sucesso" => false,
        "erro" => "Erro ao cadastrar aluno: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
