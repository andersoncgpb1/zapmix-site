<?php
// api/validar-licenca.php - Validate license from app

header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

$chave = $_GET['chave'] ?? $_POST['chave'] ?? '';

if (empty($chave)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'missing_key']);
    exit;
}

try {
    $licenca = Database::fetchOne(
        'SELECT l.*, c.nome as cliente_nome FROM licencas l
         LEFT JOIN clientes c ON l.cliente_id = c.id
         WHERE l.chave_licenca = ?',
        [$chave]
    );
    
    if (!$licenca) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'invalid_key']);
        exit;
    }
    
    // Check if active and not expired
    $expirou = strtotime($licenca['data_expiracao']) < time();
    
    if ($expirou || !$licenca['ativa']) {
        http_response_code(403);
        echo json_encode([
            'ok' => false, 
            'error' => 'license_expired',
            'data_expiracao' => $licenca['data_expiracao']
        ]);
        exit;
    }
    
    // License is valid
    echo json_encode([
        'ok' => true,
        'cliente_id' => $licenca['cliente_id'],
        'cliente_nome' => $licenca['cliente_nome'],
        'tipo' => $licenca['tipo'],
        'data_expiracao' => $licenca['data_expiracao'],
        'funcoes' => json_decode($licenca['funcoes'] ?? '[]')
    ]);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server_error']);
    exit;
}
?>
