<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

if ($usarSupabase) {
    $res = $supabase->select('alunos', 'select=id,nome,escola,turma,email,status,data_cadastro&order=id.desc');
    if ($res['sucesso'] && is_array($res['dados'])) {
        $alunos = $res['dados'];
        // Ordena pendente primeiro, aprovado depois, outros por último
        usort($alunos, function($a, $b) {
            $peso = ['pendente' => 1, 'aprovado' => 2, 'admin' => 3, 'recusado' => 4, 'banido' => 5];
            $pA = $peso[$a['status']] ?? 99;
            $pB = $peso[$b['status']] ?? 99;
            return $pA <=> $pB;
        });

        echo json_encode(["sucesso" => true, "alunos" => $alunos]);
        exit;
    }
}

// Fallback MySQL local
if ($conn && !$conn->connect_error) {
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
    $alunos = [];
    if ($resultado) {
        while ($aluno = $resultado->fetch_assoc()) {
            $alunos[] = $aluno;
        }
    }
    echo json_encode(["sucesso" => true, "alunos" => $alunos]);
    $conn->close();
    exit;
}

echo json_encode(["sucesso" => false, "erro" => "Sem conexão com o banco de dados."]);
?>
