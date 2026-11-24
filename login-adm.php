<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

$dados = json_decode(file_get_contents("php://input"), true);
$usuario = trim($dados['usuario'] ?? '');
$senha = $dados['senha'] ?? '';

if ($usuario === '' || $senha === '') {
    echo json_encode(["sucesso" => false, "erro" => "Informe usuário/e-mail e senha."]);
    exit;
}

if ($usarSupabase) {
    // Busca por email ou nome
    $filtro = 'or=(email.eq.' . rawurlencode($usuario) . ',nome.eq.' . rawurlencode($usuario) . ')&limit=1';
    $res = $supabase->select('administradores', 'select=id,nome,email,senha,escola&' . $filtro);

    if ($res['sucesso'] && !empty($res['dados'])) {
        $admin = $res['dados'][0];
        $senhaValida = false;

        if (password_verify($senha, $admin['senha'])) {
            $senhaValida = true;
        } elseif ($senha === $admin['senha']) {
            $senhaValida = true;
        }

        if (!$senhaValida) {
            echo json_encode(["sucesso" => false, "erro" => "Usuário ou senha incorretos."]);
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
        exit;
    } else {
        echo json_encode(["sucesso" => false, "erro" => "Usuário ou senha incorretos."]);
        exit;
    }
}

// Fallback MySQL local
if ($conn && !$conn->connect_error) {
    $sql = "SELECT id, nome, email, senha, escola FROM administradores WHERE email = ? OR nome = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $usuario, $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo json_encode(["sucesso" => false, "erro" => "Usuário ou senha incorretos."]);
        $stmt->close();
        $conn->close();
        exit;
    }

    $admin = $resultado->fetch_assoc();
    $senhaValida = false;

    if (password_verify($senha, $admin['senha'])) {
        $senhaValida = true;
    } elseif ($senha === $admin['senha']) {
        $senhaValida = true;
    }

    if (!$senhaValida) {
        echo json_encode(["sucesso" => false, "erro" => "Usuário ou senha incorretos."]);
        $stmt->close();
        $conn->close();
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
    exit;
}

echo json_encode(["sucesso" => false, "erro" => "Sem conexão com o banco de dados."]);
?>
