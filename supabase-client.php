<?php
/**
 * Cliente Supabase REST API em PHP nativo (via cURL)
 * Não requer Composer ou extensões externas (usa cURL nativa do PHP/XAMPP)
 */
require_once 'config.php';

class SupabaseClient {
    private string $url;
    private string $key;

    public function __construct() {
        $this->url = rtrim(SUPABASE_URL, '/');
        $this->key = SUPABASE_KEY;
    }

    public function isConfigured(): bool {
        return !empty($this->url) 
            && !empty($this->key) 
            && !str_contains($this->url, 'SEU-PROJETO') 
            && !str_contains($this->key, 'SUA-ANON');
    }

    private function request(string $endpoint, string $method = 'GET', ?array $data = null, array $headers = []): array {
        if (!$this->isConfigured()) {
            return [
                'sucesso' => false,
                'status_code' => 500,
                'erro' => 'Credenciais do Supabase não configuradas em config.php'
            ];
        }

        $fullUrl = $this->url . '/rest/v1/' . ltrim($endpoint, '/');
        $ch = curl_init($fullUrl);

        $defaultHeaders = [
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $this->key,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];

        $allHeaders = array_merge($defaultHeaders, $headers);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($data !== null && in_array($method, ['POST', 'PATCH', 'PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'sucesso' => false,
                'status_code' => 0,
                'erro' => 'Erro de conexão cURL com Supabase: ' . $curlError
            ];
        }

        $decoded = json_decode($response, true);
        $isSuccess = ($httpCode >= 200 && $httpCode < 300);

        return [
            'sucesso' => $isSuccess,
            'status_code' => $httpCode,
            'dados' => $decoded,
            'erro' => !$isSuccess ? ($decoded['message'] ?? $decoded['error'] ?? $response) : null
        ];
    }

    // SELECT
    public function select(string $table, string $query = 'select=*'): array {
        $endpoint = $table . '?' . $query;
        return $this->request($endpoint, 'GET');
    }

    // INSERT
    public function insert(string $table, array $record): array {
        return $this->request($table, 'POST', $record);
    }

    // UPDATE
    public function update(string $table, array $data, string $filterQuery): array {
        $endpoint = $table . '?' . $filterQuery;
        return $this->request($endpoint, 'PATCH', $data);
    }

    // DELETE
    public function delete(string $table, string $filterQuery): array {
        $endpoint = $table . '?' . $filterQuery;
        return $this->request($endpoint, 'DELETE');
    }
}
?>
