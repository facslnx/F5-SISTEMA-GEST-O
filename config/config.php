<?php
// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Alterado para 0 em desenvolvimento local
ini_set('session.cookie_lifetime', 0);
session_set_cookie_params(0, '/', '', false, true);

// Configurações de erro
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Criar diretório de logs se não existir
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}

// Configurações do Supabase
define('SUPABASE_URL', 'https://dqmzcrkbpxypcrpfgzwi.supabase.co');
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImRxbXpjcmticHh5cGNycGZnendpIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MzQxNDM4ODUsImV4cCI6MjA0OTcxOTg4NX0.RGbl6YWrNE_5x152dxA2yxScfgOje8pclw30Ow9yU2k');

// Função para criar cliente Supabase
function createSupabaseClient() {
    return new class {
        private $ch;
        
        public function __construct() {
            $this->ch = curl_init();
            curl_setopt_array($this->ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'apikey: ' . SUPABASE_ANON_KEY,
                    'Authorization: Bearer ' . $_SESSION['user']['access_token'],
                    'Content-Type: application/json',
                    'Prefer: return=representation'
                ]
            ]);
        }
        
        public function __destruct() {
            if ($this->ch) {
                curl_close($this->ch);
            }
        }
        
        public function from($table) {
            return new class($table, $this->ch) {
                private $table;
                private $ch;
                private $selects = '*';
                private $conditions = [];
                private $orders = [];
                private $single = false;
                
                public function __construct($table, $ch) {
                    $this->table = $table;
                    $this->ch = $ch;
                }
                
                public function select($fields) {
                    $this->selects = $fields;
                    return $this;
                }
                
                public function eq($column, $value) {
                    $this->conditions[] = [$column, 'eq', $value];
                    return $this;
                }
                
                public function order($column, $options = []) {
                    $ascending = !isset($options['ascending']) || $options['ascending'];
                    $this->orders[] = [$column, $ascending ? 'asc' : 'desc'];
                    return $this;
                }
                
                public function single() {
                    $this->single = true;
                    return $this;
                }
                
                private function buildUrl() {
                    $url = SUPABASE_URL . '/rest/v1/' . $this->table;
                    $params = [];
                    
                    // Add select
                    $params['select'] = $this->selects;
                    
                    // Add conditions
                    foreach ($this->conditions as $condition) {
                        $params[$condition[0]] = $condition[1] . '.' . urlencode($condition[2]);
                    }
                    
                    // Add order
                    if (!empty($this->orders)) {
                        $orderParts = [];
                        foreach ($this->orders as $order) {
                            $orderParts[] = $order[0] . '.' . $order[1];
                        }
                        $params['order'] = implode(',', $orderParts);
                    }
                    
                    return $url . '?' . http_build_query($params);
                }
                
                public function execute() {
                    curl_setopt($this->ch, CURLOPT_URL, $this->buildUrl());
                    curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');
                    
                    $response = curl_exec($this->ch);
                    $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
                    
                    if ($httpCode >= 400) {
                        return (object) [
                            'error' => (object) ['message' => 'HTTP Error: ' . $httpCode],
                            'data' => null
                        ];
                    }
                    
                    if ($response === false) {
                        return (object) [
                            'error' => (object) ['message' => 'Curl error: ' . curl_error($this->ch)],
                            'data' => null
                        ];
                    }
                    
                    $data = json_decode($response);
                    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return (object) [
                            'error' => (object) ['message' => 'JSON Error: ' . json_last_error_msg()],
                            'data' => null
                        ];
                    }
                    
                    if ($this->single && is_array($data) && !empty($data)) {
                        $data = $data[0];
                    }
                    
                    return (object) [
                        'error' => null,
                        'data' => $data
                    ];
                }
                
                public function insert($data) {
                    curl_setopt($this->ch, CURLOPT_URL, SUPABASE_URL . '/rest/v1/' . $this->table);
                    curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
                    curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($data));
                    
                    $response = curl_exec($this->ch);
                    $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
                    
                    if ($httpCode >= 400) {
                        return (object) [
                            'error' => (object) ['message' => 'HTTP Error: ' . $httpCode],
                            'data' => null
                        ];
                    }
                    
                    if ($response === false) {
                        return (object) [
                            'error' => (object) ['message' => 'Curl error: ' . curl_error($this->ch)],
                            'data' => null
                        ];
                    }
                    
                    $data = json_decode($response);
                    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return (object) [
                            'error' => (object) ['message' => 'JSON Error: ' . json_last_error_msg()],
                            'data' => null
                        ];
                    }
                    
                    return (object) [
                        'error' => null,
                        'data' => $data
                    ];
                }
                
                public function delete() {
                    $url = $this->buildUrl();
                    
                    curl_setopt($this->ch, CURLOPT_URL, $url);
                    curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    
                    $response = curl_exec($this->ch);
                    $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
                    
                    if ($httpCode >= 400) {
                        return (object) [
                            'error' => (object) ['message' => 'HTTP Error: ' . $httpCode],
                            'data' => null
                        ];
                    }
                    
                    return (object) [
                        'error' => null,
                        'data' => true
                    ];
                }
            };
        }
    };
}

// Time Zone
date_default_timezone_set('America/Sao_Paulo');
