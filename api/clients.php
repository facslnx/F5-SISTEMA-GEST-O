<?php
require_once '../utils/functions.php';
require_once '../database/connection.php';

session_start();
checkLogin();

// Validar campos obrigatórios
function validateRequiredFields($fields) {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $missing[] = $field;
        }
    }
    return $missing;
}

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create':
                // Validar campos obrigatórios
                $requiredFields = ['empresa', 'documento', 'responsavel', 'telefone', 'email'];
                $missingFields = validateRequiredFields($requiredFields);
                
                if (!empty($missingFields)) {
                    jsonResponse(false, null, 'Campos obrigatórios faltando: ' . implode(', ', $missingFields));
                    exit;
                }
                
                // Validar email
                if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                    jsonResponse(false, null, 'Email inválido');
                    exit;
                }
                
                // Iniciar transação
                $pdo->beginTransaction();
                
                try {
                    // Verificar se já existe cliente com mesmo documento
                    $stmt = $pdo->prepare("SELECT id FROM clients WHERE documento = ?");
                    $stmt->execute([$_POST['documento']]);
                    if ($stmt->fetch()) {
                        throw new Exception('Já existe um cliente cadastrado com este documento');
                    }
                    
                    // Preparar dados do cliente
                    $stmt = $pdo->prepare("
                        INSERT INTO clients (
                            empresa,
                            documento,
                            endereco,
                            cidade,
                            estado,
                            cep,
                            responsavel,
                            telefone,
                            email,
                            created_at,
                            updated_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    
                    $stmt->execute([
                        trim($_POST['empresa']),
                        trim($_POST['documento']),
                        trim($_POST['endereco'] ?? ''),
                        trim($_POST['cidade'] ?? ''),
                        trim($_POST['estado'] ?? ''),
                        trim($_POST['cep'] ?? ''),
                        trim($_POST['responsavel']),
                        trim($_POST['telefone']),
                        trim($_POST['email'])
                    ]);
                    
                    $client_id = $pdo->lastInsertId();
                    
                    // Upload do contrato se fornecido
                    if (isset($_FILES['contrato']) && $_FILES['contrato']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = '../uploads/contratos/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        // Validar tipo de arquivo
                        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
                        $file_info = finfo_open(FILEINFO_MIME_TYPE);
                        $file_type = finfo_file($file_info, $_FILES['contrato']['tmp_name']);
                        finfo_close($file_info);
                        
                        if (!in_array($file_type, $allowed_types)) {
                            throw new Exception('Tipo de arquivo não permitido. Apenas PDF e imagens (JPG, PNG) são aceitos.');
                        }
                        
                        $file_ext = strtolower(pathinfo($_FILES['contrato']['name'], PATHINFO_EXTENSION));
                        $new_filename = $client_id . '_' . time() . '.' . $file_ext;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (!move_uploaded_file($_FILES['contrato']['tmp_name'], $upload_path)) {
                            throw new Exception('Erro ao fazer upload do contrato');
                        }
                        
                        $stmt = $pdo->prepare("UPDATE clients SET contrato_url = ? WHERE id = ?");
                        $stmt->execute([$new_filename, $client_id]);
                    }
                    
                    $pdo->commit();
                    jsonResponse(true, ['client_id' => $client_id], 'Cliente criado com sucesso');
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log('Erro ao criar cliente: ' . $e->getMessage());
                    error_log('POST data: ' . print_r($_POST, true));
                    jsonResponse(false, null, 'Erro ao criar cliente: ' . $e->getMessage());
                }
                break;
                
            case 'update':
                $id = $_POST['id'] ?? null;
                if (!$id) {
                    jsonResponse(false, null, 'ID do cliente não fornecido');
                    exit;
                }
                
                // Validar campos obrigatórios
                $requiredFields = ['empresa', 'documento', 'responsavel', 'telefone', 'email'];
                $missingFields = validateRequiredFields($requiredFields);
                
                if (!empty($missingFields)) {
                    jsonResponse(false, null, 'Campos obrigatórios faltando: ' . implode(', ', $missingFields));
                    exit;
                }
                
                // Validar email
                if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                    jsonResponse(false, null, 'Email inválido');
                    exit;
                }
                
                // Iniciar transação
                $pdo->beginTransaction();
                
                try {
                    // Verificar se o cliente existe
                    $stmt = $pdo->prepare("SELECT id FROM clients WHERE id = ?");
                    $stmt->execute([$id]);
                    if (!$stmt->fetch()) {
                        throw new Exception('Cliente não encontrado');
                    }
                    
                    // Verificar documento duplicado
                    $stmt = $pdo->prepare("SELECT id FROM clients WHERE documento = ? AND id != ?");
                    $stmt->execute([$_POST['documento'], $id]);
                    if ($stmt->fetch()) {
                        throw new Exception('Já existe outro cliente com este documento');
                    }
                    
                    $stmt = $pdo->prepare("
                        UPDATE clients SET
                            empresa = ?,
                            documento = ?,
                            endereco = ?,
                            cidade = ?,
                            estado = ?,
                            cep = ?,
                            responsavel = ?,
                            telefone = ?,
                            email = ?,
                            updated_at = NOW()
                        WHERE id = ?
                    ");
                    
                    $stmt->execute([
                        trim($_POST['empresa']),
                        trim($_POST['documento']),
                        trim($_POST['endereco'] ?? ''),
                        trim($_POST['cidade'] ?? ''),
                        trim($_POST['estado'] ?? ''),
                        trim($_POST['cep'] ?? ''),
                        trim($_POST['responsavel']),
                        trim($_POST['telefone']),
                        trim($_POST['email']),
                        $id
                    ]);
                    
                    // Upload do novo contrato se fornecido
                    if (isset($_FILES['contrato']) && $_FILES['contrato']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = '../uploads/contratos/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        // Validar tipo de arquivo
                        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
                        $file_info = finfo_open(FILEINFO_MIME_TYPE);
                        $file_type = finfo_file($file_info, $_FILES['contrato']['tmp_name']);
                        finfo_close($file_info);
                        
                        if (!in_array($file_type, $allowed_types)) {
                            throw new Exception('Tipo de arquivo não permitido. Apenas PDF e imagens (JPG, PNG) são aceitos.');
                        }
                        
                        // Remover contrato antigo
                        $stmt = $pdo->prepare("SELECT contrato_url FROM clients WHERE id = ?");
                        $stmt->execute([$id]);
                        $old_contract = $stmt->fetchColumn();
                        if ($old_contract && file_exists($upload_dir . $old_contract)) {
                            unlink($upload_dir . $old_contract);
                        }
                        
                        $file_ext = strtolower(pathinfo($_FILES['contrato']['name'], PATHINFO_EXTENSION));
                        $new_filename = $id . '_' . time() . '.' . $file_ext;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (!move_uploaded_file($_FILES['contrato']['tmp_name'], $upload_path)) {
                            throw new Exception('Erro ao fazer upload do contrato');
                        }
                        
                        $stmt = $pdo->prepare("UPDATE clients SET contrato_url = ? WHERE id = ?");
                        $stmt->execute([$new_filename, $id]);
                    }
                    
                    $pdo->commit();
                    jsonResponse(true, ['client_id' => $id], 'Cliente atualizado com sucesso');
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log('Erro ao atualizar cliente: ' . $e->getMessage());
                    error_log('POST data: ' . print_r($_POST, true));
                    jsonResponse(false, null, 'Erro ao atualizar cliente: ' . $e->getMessage());
                }
                break;
                
            case 'delete':
                $id = $_POST['id'] ?? null;
                if (!$id) {
                    jsonResponse(false, null, 'ID do cliente não fornecido');
                    exit;
                }
                
                try {
                    // Verificar se o cliente existe
                    $stmt = $pdo->prepare("SELECT id FROM clients WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    if (!$stmt->fetch()) {
                        throw new Exception('Cliente não encontrado');
                    }
                    
                    // Atualizar o status do cliente para inativo
                    $stmt = $pdo->prepare("UPDATE clients SET status = 0 WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    jsonResponse(true, null, 'Cliente removido com sucesso');
                    
                } catch (Exception $e) {
                    error_log('Erro ao remover cliente: ' . $e->getMessage());
                    jsonResponse(false, null, 'Erro ao remover cliente: ' . $e->getMessage());
                }
                break;
                
            default:
                jsonResponse(false, null, 'Ação inválida');
                break;
        }
    } else {
        jsonResponse(false, null, 'Método não permitido');
    }
    
} catch (Exception $e) {
    error_log('Erro ao processar requisição: ' . $e->getMessage());
    jsonResponse(false, null, 'Erro ao processar requisição: ' . $e->getMessage());
}
