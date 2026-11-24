<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

$dados = json_decode(file_get_contents("php://input"), true);

if (!$dados) {
    echo json_encode(["sucesso" => false, "erro" => "Nenhum dado enviado."]);
    exit;
}

$acao = trim($dados['acao'] ?? 'criar_pedido');

if ($usarSupabase) {
    if ($acao === 'criar_pedido') {
        $itens = $dados['itens'] ?? [];
        $total = floatval($dados['total'] ?? 0);
        $status = trim($dados['status'] ?? 'Pendente');
        $senha = rand(1, 99);

        if (empty($itens) || $total <= 0) {
            echo json_encode(["sucesso" => false, "erro" => "Selecione ao menos um produto."]);
            exit;
        }

        $resPed = $supabase->insert('pedidos', [
            'senha' => $senha,
            'itens' => $itens,
            'total' => $total,
            'status' => $status
        ]);

        if ($resPed['sucesso']) {
            // Abate estoque no Supabase
            foreach ($itens as $item) {
                if (!empty($item['id']) && !empty($item['quantidade'])) {
                    $idProd = intval($item['id']);
                    $qtd = intval($item['quantidade']);
                    $getProd = $supabase->select('produtos', 'select=estoque&id=eq.' . $idProd);
                    if ($getProd['sucesso'] && !empty($getProd['dados'])) {
                        $estAtual = intval($getProd['dados'][0]['estoque']);
                        $novoEst = max(0, $estAtual - $qtd);
                        $supabase->update('produtos', ['estoque' => $novoEst], 'id=eq.' . $idProd);
                    }
                }
            }

            echo json_encode([
                "sucesso" => true,
                "mensagem" => "Pedido lançado com sucesso!",
                "senha" => $senha
            ]);
        } else {
            echo json_encode(["sucesso" => false, "erro" => $resPed['erro']]);
        }
        exit;
    } elseif ($acao === 'adicionar_produto') {
        $nome = trim($dados['nome'] ?? '');
        $categoria = trim($dados['categoria'] ?? 'Lanches');
        $preco = floatval($dados['preco'] ?? 0);
        $estoque = intval($dados['estoque'] ?? 0);
        $imagem = trim($dados['imagem'] ?? 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&q=80');

        $resProd = $supabase->insert('produtos', [
            'nome' => $nome,
            'categoria' => $categoria,
            'preco' => $preco,
            'imagem' => $imagem,
            'estoque' => $estoque
        ]);

        if ($resProd['sucesso']) {
            echo json_encode([
                "sucesso" => true,
                "mensagem" => "Produto adicionado ao cardápio e estoque!"
            ]);
        } else {
            echo json_encode(["sucesso" => false, "erro" => $resProd['erro']]);
        }
        exit;
    }
}

// Fallback MySQL local
if ($conn && !$conn->connect_error) {
    if ($acao === 'criar_pedido') {
        $itens = $dados['itens'] ?? [];
        $total = floatval($dados['total'] ?? 0);
        $status = trim($dados['status'] ?? 'Pendente');

        if (empty($itens) || $total <= 0) {
            echo json_encode(["sucesso" => false, "erro" => "Selecione ao menos um produto válido com quantidade."]);
            $conn->close();
            exit;
        }

        $itensJson = json_encode($itens, JSON_UNESCAPED_UNICODE);
        $senha = rand(1, 99);

        $stmt = $conn->prepare("INSERT INTO pedidos (senha, itens, total, status) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isds", $senha, $itensJson, $total, $status);
            if ($stmt->execute()) {
                foreach ($itens as $item) {
                    if (!empty($item['id']) && !empty($item['quantidade'])) {
                        $idProd = intval($item['id']);
                        $qtd = intval($item['quantidade']);
                        $conn->query("UPDATE produtos SET estoque = GREATEST(0, estoque - $qtd) WHERE id = $idProd");
                    }
                }
                echo json_encode([
                    "sucesso" => true,
                    "mensagem" => "Pedido lançado com sucesso!",
                    "senha" => $senha,
                    "id" => $stmt->insert_id
                ]);
            } else {
                echo json_encode(["sucesso" => false, "erro" => "Erro ao inserir pedido: " . $stmt->error]);
            }
            $stmt->close();
        }
    } elseif ($acao === 'adicionar_produto') {
        $nome = trim($dados['nome'] ?? '');
        $categoria = trim($dados['categoria'] ?? 'Lanches');
        $preco = floatval($dados['preco'] ?? 0);
        $estoque = intval($dados['estoque'] ?? 0);
        $imagem = trim($dados['imagem'] ?? 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&q=80');

        $stmt = $conn->prepare("INSERT INTO produtos (nome, categoria, preco, imagem, estoque) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssdsi", $nome, $categoria, $preco, $imagem, $estoque);
            if ($stmt->execute()) {
                echo json_encode([
                    "sucesso" => true,
                    "mensagem" => "Produto adicionado ao cardápio e estoque!",
                    "id" => $stmt->insert_id
                ]);
            } else {
                echo json_encode(["sucesso" => false, "erro" => "Erro ao salvar produto: " . $stmt->error]);
            }
            $stmt->close();
        }
    }
    $conn->close();
    exit;
}

echo json_encode(["sucesso" => false, "erro" => "Sem conexão com o banco."]);
?>
