<?php
/**
 * Conexão Híbrida: Supabase (REST API) ou MySQL local (XAMPP)
 * Se DATABASE_DRIVER for 'supabase' e as credenciais estiverem preenchidas,
 * utiliza o Supabase com fallback inteligente para MySQL local se necessário.
 */
require_once 'config.php';
require_once 'supabase-client.php';

$supabase = new SupabaseClient();

// Mantém objeto $conn para scripts legados ou modo MySQL
$servidor = "localhost";
$usuario = "root"; 
$senha = "";
$banco = "cantina_senai";

// Supressão de erro caso MySQL do XAMPP não esteja ligado quando estiver no modo Supabase
$conn = @new mysqli($servidor, $usuario, $senha, $banco);

$usarSupabase = (defined('DATABASE_DRIVER') && DATABASE_DRIVER === 'supabase' && $supabase->isConfigured());
?>
