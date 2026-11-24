<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

$dados = json_decode(file_get_contents("php://input"), true);
$email = trim($dados['email'] ?? '');

if ($email === '') {
    echo json_encode(["sucesso" => false, "erro" => "Digite seu e-mail."]);
    exit;
}

if ($usarSupabase) {
    $res = $supabase->select('alunos', 'select=id,nome,escola,turma,email,status&email=eq.' . rawurlencode($email) . '&limit=1');
    if ($res['sucesso'] && !empty($res['dados'])) {
        $aluno = $res['dados'][0];

        if ($aluno['status'] === 'pendente') {
            echo json_encode([
                "sucesso" => false,
                "status" => "pendente",
                "erro" => "Seu cadastro está aguardando aprovação da cantina."
            ]);
            exit;
        }

        if ($aluno['status'] === 'banido') {
            echo json_encode([
                "sucesso" => false,
                "status" => "banido",
                "erro" => "Sua conta foi banida pela administração da cantina."
            ]);
            exit;
        }

        if ($aluno['status'] === 'recusado') {
            echo json_encode([
                "sucesso" => false,
                "status" => "recusado",
                "erro" => "Seu cadastro foi recusado pela cantina."
            ]);
            exit;
        }

        echo json_encode([
            "sucesso" => true,
            "status" => $aluno['status'],
            "aluno" => [
                "id" => $aluno['id'],
                "nome" => $aluno['nome'],
                "escola" => $aluno['escola'],
                "turma" => $aluno['turma'],
                "email" => $aluno['email'],
                "is_admin" => ($aluno['status'] === 'admin')
            ]
        ]);
        exit;
    } else {
        echo json_encode([
            "sucesso" => false,
            "erro" => "Aluno não encontrado. Faça seu cadastro primeiro."
        ]);
        exit;
    }
}

// Fallback MySQL local
if ($conn && !$conn->connect_error) {
    $sql = "SELECT id, nome, escola, turma, email, status FROM alunos WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo json_encode(["sucesso" => false, "erro" => "Aluno não encontrado. Faça seu cadastro primeiro."]);
        $stmt->close();
        $conn->close();
        exit;
    }

    $aluno = $resultado->fetch_assoc();

    if ($aluno['status'] === 'pendente') {
        echo json_encode([
            "sucesso" => false,
            "status" => "pendente",
            "erro" => "Seu cadastro está aguardando aprovação da cantina."
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }

    if ($aluno['status'] === 'banido') {
        echo json_encode([
            "sucesso" => false,
            "status" => "banido",
            "erro" => "Sua conta foi banida pela administração da cantina."
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }

    if ($aluno['status'] === 'recusado') {
        echo json_encode([
            "sucesso" => false,
            "status" => "recusado",
            "erro" => "Seu cadastro foi recusado pela cantina."
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }

    echo json_encode([
        "sucesso" => true,
        "status" => $aluno['status'],
        "aluno" => [
            "id" => $aluno['id'],
            "nome" => $aluno['nome'],
            "escola" => $aluno['escola'],
            "turma" => $aluno['turma'],
            "email" => $aluno['email'],
            "is_admin" => ($aluno['status'] === 'admin')
        ]
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

echo json_encode(["sucesso" => false, "erro" => "Sem conexão com o banco de dados."]);
?>
