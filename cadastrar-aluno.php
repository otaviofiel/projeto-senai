<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

$dados = json_decode(file_get_contents("php://input"), true);
$nome   = trim($dados['nome'] ?? '');
$escola = trim($dados['escola'] ?? '');
$turma  = trim($dados['turma'] ?? '');
$email  = trim($dados['email'] ?? '');

if ($nome === '' || $escola === '' || $turma === '' || $email === '') {
    echo json_encode(["sucesso" => false, "erro" => "Preencha todos os campos."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["sucesso" => false, "erro" => "Digite um e-mail válido."]);
    exit;
}

if ($usarSupabase) {
    // Verifica se já existe
    $check = $supabase->select('alunos', 'select=id&email=eq.' . rawurlencode($email));
    if ($check['sucesso'] && !empty($check['dados'])) {
        echo json_encode(["sucesso" => false, "erro" => "Este e-mail já está cadastrado."]);
        exit;
    }

    $novoAluno = [
        'nome' => $nome,
        'escola' => $escola,
        'turma' => $turma,
        'email' => $email,
        'status' => 'pendente'
    ];

    $res = $supabase->insert('alunos', $novoAluno);
    if ($res['sucesso']) {
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Cadastro realizado com sucesso! Aguarde aprovação da cantina."
        ]);
    } else {
        echo json_encode(["sucesso" => false, "erro" => $res['erro']]);
    }
    exit;
}

// Fallback MySQL local
if ($conn && !$conn->connect_error) {
    $sqlVerifica = "SELECT id FROM alunos WHERE email = ? LIMIT 1";
    $stmtVerifica = $conn->prepare($sqlVerifica);
    $stmtVerifica->bind_param("s", $email);
    $stmtVerifica->execute();
    $resVerifica = $stmtVerifica->get_result();

    if ($resVerifica->num_rows > 0) {
        echo json_encode(["sucesso" => false, "erro" => "Este e-mail já está cadastrado."]);
        $stmtVerifica->close();
        $conn->close();
        exit;
    }
    $stmtVerifica->close();

    $sql = "INSERT INTO alunos (nome, escola, turma, email, status) VALUES (?, ?, ?, ?, 'pendente')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nome, $escola, $turma, $email);

    if ($stmt->execute()) {
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Cadastro realizado com sucesso! Aguarde a aprovação da cantina."
        ]);
    } else {
        echo json_encode(["sucesso" => false, "erro" => "Não foi possível cadastrar: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit;
}

echo json_encode(["sucesso" => false, "erro" => "Sem conexão com o banco de dados."]);
?>
