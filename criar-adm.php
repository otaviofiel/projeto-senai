<?php

include 'conexao.php';

$nome = "Administrador";
$email = "admin@filazero.com";
$senha = password_hash("1234", PASSWORD_DEFAULT);
$escola = "SENAI";

$sql = "INSERT INTO administradores (nome, email, senha, escola)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $nome, $email, $senha, $escola);

if ($stmt->execute()) {
    echo "Administrador criado com sucesso!";
} else {
    echo "Erro: " . $stmt->error;
}

$stmt->close();
$conn->close();

?>