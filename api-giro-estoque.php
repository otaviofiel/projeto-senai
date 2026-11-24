<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    if ($usarSupabase) {
        $resProd = $supabase->select('produtos', 'select=id,nome,categoria,preco,estoque&order=nome.asc');
        $produtos = $resProd['sucesso'] && is_array($resProd['dados']) ? $resProd['dados'] : [];

        $totalItensEstoque = 0;
        $valorTotalEstoque = 0;
        foreach ($produtos as $p) {
            $qtd = intval($p['estoque']);
            $preco = floatval($p['preco']);
            $totalItensEstoque += $qtd;
            $valorTotalEstoque += ($qtd * $preco);
        }

        $resMov = $supabase->select('movimentacoes_estoque', 'select=*&order=id.desc&limit=100');
        $movimentacoes = $resMov['sucesso'] && is_array($resMov['dados']) ? $resMov['dados'] : [];

        $totalEntradas = 0;
        $totalSaidas = 0;
        foreach ($movimentacoes as $m) {
            if ($m['tipo'] === 'entrada') {
                $totalEntradas += intval($m['quantidade']);
            } else {
                $totalSaidas += intval($m['quantidade']);
            }
        }

        echo json_encode([
            "sucesso" => true,
            "totais" => [
                "total_itens" => $totalItensEstoque,
                "valor_total" => $valorTotalEstoque,
                "total_entradas" => $totalEntradas,
                "total_saidas" => $totalSaidas
            ],
            "produtos" => $produtos,
            "movimentacoes" => $movimentacoes
        ]);
        exit;
    }

    // Fallback MySQL local
    if ($conn && !$conn->connect_error) {
        $sqlProdutos = "SELECT id, nome, categoria, preco, estoque FROM produtos ORDER BY nome ASC";
        $resProd = $conn->query($sqlProdutos);
        $produtos = [];
        $totalItensEstoque = 0;
        $valorTotalEstoque = 0;

        if ($resProd && $resProd->num_rows > 0) {
            while ($p = $resProd->fetch_assoc()) {
                $qtd = intval($p['estoque']);
                $preco = floatval($p['preco']);
                $totalItensEstoque += $qtd;
                $valorTotalEstoque += ($qtd * $preco);
                $produtos[] = $p;
            }
        }

        $sqlMov = "SELECT id, produto_id, produto_nome, tipo, quantidade, motivo, saldo_apos, data_hora 
                   FROM movimentacoes_estoque 
                   ORDER BY id DESC LIMIT 100";
        $resMov = $conn->query($sqlMov);
        $movimentacoes = [];
        $totalEntradas = 0;
        $totalSaidas = 0;

        if ($resMov && $resMov->num_rows > 0) {
            while ($m = $resMov->fetch_assoc()) {
                if ($m['tipo'] === 'entrada') {
                    $totalEntradas += intval($m['quantidade']);
                } else {
                    $totalSaidas += intval($m['quantidade']);
                }
                $movimentacoes[] = $m;
            }
        }

        echo json_encode([
            "sucesso" => true,
            "totais" => [
                "total_itens" => $totalItensEstoque,
                "valor_total" => $valorTotalEstoque,
                "total_entradas" => $totalEntradas,
                "total_saidas" => $totalSaidas
            ],
            "produtos" => $produtos,
            "movimentacoes" => $movimentacoes
        ]);
        $conn->close();
        exit;
    }

    echo json_encode(["sucesso" => false, "erro" => "Sem conexão com o banco."]);

} elseif ($metodo === 'POST') {
    $dados = json_decode(file_get_contents("php://input"), true);
    $produtoId = intval($dados['produto_id'] ?? 0);
    $tipo = strtolower(trim($dados['tipo'] ?? ''));
    $quantidade = intval($dados['quantidade'] ?? 0);
    $motivo = trim($dados['motivo'] ?? 'Ajuste manual');

    if ($produtoId <= 0 || ($tipo !== 'entrada' && $tipo !== 'saida') || $quantidade <= 0) {
        echo json_encode(["sucesso" => false, "erro" => "Dados inválidos para registrar movimentação."]);
        exit;
    }

    if ($usarSupabase) {
        $prodRes = $supabase->select('produtos', 'select=id,nome,estoque&id=eq.' . $produtoId . '&limit=1');
        if (!$prodRes['sucesso'] || empty($prodRes['dados'])) {
            echo json_encode(["sucesso" => false, "erro" => "Produto não encontrado no Supabase."]);
            exit;
        }
        $prod = $prodRes['dados'][0];
        $estoqueAtual = intval($prod['estoque']);

        if ($tipo === 'entrada') {
            $novoSaldo = $estoqueAtual + $quantidade;
        } else {
            if ($estoqueAtual < $quantidade) {
                echo json_encode(["sucesso" => false, "erro" => "Saldo insuficiente em estoque! Saldo atual: $estoqueAtual"]);
                exit;
            }
            $novoSaldo = $estoqueAtual - $quantidade;
        }

        $supabase->update('produtos', ['estoque' => $novoSaldo], 'id=eq.' . $produtoId);
        $supabase->insert('movimentacoes_estoque', [
            'produto_id' => $produtoId,
            'produto_nome' => $prod['nome'],
            'tipo' => $tipo,
            'quantidade' => $quantidade,
            'motivo' => $motivo,
            'saldo_apos' => $novoSaldo
        ]);

        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Giro de " . ($tipo === 'entrada' ? 'Entrada' : 'Saída') . " registrado no Supabase! Novo saldo: $novoSaldo",
            "novo_saldo" => $novoSaldo
        ]);
        exit;
    }

    // Fallback MySQL local
    if ($conn && !$conn->connect_error) {
        $stmt = $conn->prepare("SELECT id, nome, estoque FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $produtoId);
        $stmt->execute();
        $prod = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$prod) {
            echo json_encode(["sucesso" => false, "erro" => "Produto não encontrado."]);
            $conn->close();
            exit;
        }

        $estoqueAtual = intval($prod['estoque']);
        if ($tipo === 'entrada') {
            $novoSaldo = $estoqueAtual + $quantidade;
        } else {
            if ($estoqueAtual < $quantidade) {
                echo json_encode(["sucesso" => false, "erro" => "Saldo insuficiente em estoque! Saldo atual: $estoqueAtual"]);
                $conn->close();
                exit;
            }
            $novoSaldo = $estoqueAtual - $quantidade;
        }

        $conn->query("UPDATE produtos SET estoque = $novoSaldo WHERE id = $produtoId");

        $stmtIns = $conn->prepare("INSERT INTO movimentacoes_estoque (produto_id, produto_nome, tipo, quantidade, motivo, saldo_apos) VALUES (?, ?, ?, ?, ?, ?)");
        $prodNome = $prod['nome'];
        $stmtIns->bind_param("issisi", $produtoId, $prodNome, $tipo, $quantidade, $motivo, $novoSaldo);
        $stmtIns->execute();
        $stmtIns->close();

        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Giro de " . ($tipo === 'entrada' ? 'Entrada' : 'Saída') . " registrado com sucesso! Novo saldo: $novoSaldo",
            "novo_saldo" => $novoSaldo
        ]);
        $conn->close();
        exit;
    }

    echo json_encode(["sucesso" => false, "erro" => "Sem conexão com o banco."]);
}
?>
