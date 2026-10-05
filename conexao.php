<?php
// Configurações padrão do XAMPP
$servidor = "localhost";
$usuario = "root"; 
$senha = ""; // No XAMPP, a senha do root geralmente é vazia
$banco = "cantina_senai";

// Tenta criar a conexão
$conn = new mysqli($servidor, $usuario, $senha, $banco);

// Verifica se deu algum erro
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}
?>