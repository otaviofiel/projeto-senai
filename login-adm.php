<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'conexao.php';

$dados = json_decode(file_get_contents("php://input"), true);

$usuario = trim($dados['usuario'] ?? '');
$senha = $dados['senha'] ?? '';

if ($usuario === '' || $senha === '') {
    echo json_encode([
        "sucesso" => false,
        "erro" => "Informe usuário/e-mail e senha."
    ]);
    exit;
}

// Aceita tanto o e-mail quanto o nome cadastrado
$sql = "SELECT id, nome, email, senha, escola
        FROM administradores
        WHERE email = ? OR nome = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "sucesso" => false,
        "erro" => "Erro na consulta: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("ss", $usuario, $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode([
        "sucesso" => false,
        "erro" => "Usuário ou senha incorretos."
    ]);
    exit;
}

$admin = $resultado->fetch_assoc();

// Verifica a senha: tanto hash seguro quanto texto simples (compatibilidade)
$senhaValida = false;
if (password_verify($senha, $admin['senha'])) {
    $senhaValida = true;
} elseif ($senha === $admin['senha']) {
    $senhaValida = true;
}

if (!$senhaValida) {
    echo json_encode([
        "sucesso" => false,
        "erro" => "Usuário ou senha incorretos."
    ]);
    exit;
}

echo json_encode([
    "sucesso" => true,
    "admin" => [
        "id" => $admin['id'],
        "nome" => $admin['nome'],
        "email" => $admin['email'],
        "escola" => $admin['escola']
    ]
]);

$stmt->close();
$conn->close();
?>
