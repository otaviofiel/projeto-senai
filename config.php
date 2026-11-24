<?php
// ============================================================
// CONFIGURAÇÕES GERAIS E CREDENCIAIS DO SUPABASE
// ============================================================

// Credenciais do seu projeto no Supabase:
define('SUPABASE_URL', 'https://hkjcbuzjfwegfuaausna.supabase.co');
define('SUPABASE_KEY', 'sb_publishable_LNU5nJH0I3xKdl_DLD7fMA_hWHsbZCB');

// Define qual banco de dados usar:
// 'supabase' = Utiliza a API REST do Supabase (Cloud PostgreSQL)
// 'mysql'    = Utiliza o banco local do XAMPP (cantina_senai)
define('DATABASE_DRIVER', 'supabase');

// Configurações do Mercado Pago
define('MP_ACCESS_TOKEN', 'SEU_ACCESS_TOKEN_AQUI');

// URL base do projeto no XAMPP
define('BASE_URL', 'http://localhost/projeto-senai');
?>
