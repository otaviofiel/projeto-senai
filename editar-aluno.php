<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

$dados = json_decode(file_get_contents("php://input"), true);
$id = intval($dados['id'] ?? 0);
$acao = trim($dados['acao'] ?? '');

if ($id <= 0 || empty($acao)) {
    echo json_encode(["sucesso" => false, "erro" => "Parâmetros inválidos."]);
    exit;
}

if ($usarSupabase) {
    // Busca aluno
    $alunoRes = $supabase->select('alunos', 'select=id,nome,email,escola,turma,status&id=eq.' . $id . '&limit=1');
    if (!$alunoRes['sucesso'] || empty($alunoRes['dados'])) {
        echo json_encode(["sucesso" => false, "erro" => "Aluno não encontrado no Supabase."]);
        exit;
    }
    $aluno = $alunoRes['dados'][0];

    if ($acao === 'tornar_admin') {
        $supabase->update('alunos', ['status' => 'admin'], 'id=eq.' . $id);
        
        // Verifica se já está em administradores
        $admCheck = $supabase->select('administradores', 'select=id&email=eq.' . rawurlencode($aluno['email']));
        if ($admCheck['sucesso'] && empty($admCheck['dados'])) {
            $senhaHash = password_hash('123456', PASSWORD_DEFAULT);
            $supabase->insert('administradores', [
                'nome' => $aluno['nome'],
                'email' => $aluno['email'],
                'senha' => $senhaHash,
                'escola' => !empty($aluno['escola']) ? $aluno['escola'] : 'SENAI'
            ]);
        }
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Aluno promovido a Administrador! Senha inicial padrão: 123456"
        ]);
        exit;
    } elseif ($acao === 'remover_admin') {
        $supabase->update('alunos', ['status' => 'aprovado'], 'id=eq.' . $id);
        $supabase->delete('administradores', 'email=eq.' . rawurlencode($aluno['email']));
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Cargo de administrador removido. Aluno retornou ao status Aprovado."
        ]);
        exit;
    } elseif ($acao === 'banir') {
        $supabase->update('alunos', ['status' => 'banido'], 'id=eq.' . $id);
        $supabase->delete('administradores', 'email=eq.' . rawurlencode($aluno['email']));
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Aluno banido com sucesso! O acesso ao sistema foi bloqueado."
        ]);
        exit;
    } elseif ($acao === 'desbanir') {
        $supabase->update('alunos', ['status' => 'aprovado'], 'id=eq.' . $id);
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Aluno desbanido com sucesso! Status alterado para Aprovado."
        ]);
        exit;
    } elseif ($acao === 'editar_dados') {
        $nome = trim($dados['nome'] ?? $aluno['nome']);
        $email = trim($dados['email'] ?? $aluno['email']);
        $turma = trim($dados['turma'] ?? $aluno['turma']);
        $escola = trim($dados['escola'] ?? $aluno['escola']);

        $resUpdate = $supabase->update('alunos', [
            'nome' => $nome,
            'email' => $email,
            'turma' => $turma,
            'escola' => $escola
        ], 'id=eq.' . $id);

        if ($resUpdate['sucesso']) {
            echo json_encode(["sucesso" => true, "mensagem" => "Dados do aluno atualizados com sucesso!"]);
        } else {
            echo json_encode(["sucesso" => false, "erro" => $resUpdate['erro']]);
        }
        exit;
    }
}

// Fallback MySQL local
if ($conn && !$conn->connect_error) {
    @$conn->query("ALTER TABLE alunos MODIFY COLUMN status ENUM('pendente','aprovado','recusado','banido','admin') DEFAULT 'pendente'");

    $stmt = $conn->prepare("SELECT id, nome, email, escola, turma, status FROM alunos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $aluno = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$aluno) {
        echo json_encode(["sucesso" => false, "erro" => "Aluno não encontrado."]);
        $conn->close();
        exit;
    }

    if ($acao === 'tornar_admin') {
        $conn->query("UPDATE alunos SET status = 'admin' WHERE id = $id");
        $nome = $aluno['nome'];
        $email = $aluno['email'];
        $escola = !empty($aluno['escola']) ? $aluno['escola'] : 'SENAI';
        $senhaHash = password_hash('123456', PASSWORD_DEFAULT);

        $checkAdmin = $conn->prepare("SELECT id FROM administradores WHERE email = ?");
        $checkAdmin->bind_param("s", $email);
        $checkAdmin->execute();
        $resCheck = $checkAdmin->get_result();
        $checkAdmin->close();

        if ($resCheck->num_rows === 0) {
            $insertAdmin = $conn->prepare("INSERT INTO administradores (nome, email, senha, escola) VALUES (?, ?, ?, ?)");
            $insertAdmin->bind_param("ssss", $nome, $email, $senhaHash, $escola);
            $insertAdmin->execute();
            $insertAdmin->close();
        }

        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Aluno promovido a Administrador! Senha inicial padrão: 123456"
        ]);
    } elseif ($acao === 'remover_admin') {
        $conn->query("UPDATE alunos SET status = 'aprovado' WHERE id = $id");
        $email = $aluno['email'];
        $conn->query("DELETE FROM administradores WHERE email = '$email'");
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Cargo de administrador removido. Aluno retornou ao status Aprovado."
        ]);
    } elseif ($acao === 'banir') {
        $conn->query("UPDATE alunos SET status = 'banido' WHERE id = $id");
        $email = $aluno['email'];
        $conn->query("DELETE FROM administradores WHERE email = '$email'");
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Aluno banido com sucesso! O acesso ao sistema foi bloqueado."
        ]);
    } elseif ($acao === 'desbanir') {
        $conn->query("UPDATE alunos SET status = 'aprovado' WHERE id = $id");
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Aluno desbanido com sucesso! Status alterado para Aprovado."
        ]);
    } elseif ($acao === 'editar_dados') {
        $nome = trim($dados['nome'] ?? $aluno['nome']);
        $email = trim($dados['email'] ?? $aluno['email']);
        $turma = trim($dados['turma'] ?? $aluno['turma']);
        $escola = trim($dados['escola'] ?? $aluno['escola']);

        $update = $conn->prepare("UPDATE alunos SET nome = ?, email = ?, turma = ?, escola = ? WHERE id = ?");
        $update->bind_param("ssssi", $nome, $email, $turma, $escola, $id);
        if ($update->execute()) {
            echo json_encode(["sucesso" => true, "mensagem" => "Dados do aluno atualizados com sucesso!"]);
        } else {
            echo json_encode(["sucesso" => false, "erro" => "Erro ao salvar dados: " . $conn->error]);
        }
        $update->close();
    }
    $conn->close();
    exit;
}

echo json_encode(["sucesso" => false, "erro" => "Sem conexão com o banco."]);
?>
