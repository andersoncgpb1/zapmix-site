<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

// Use database if configured, otherwise fall back to JSON
$use_db = defined('USE_DATABASE') && USE_DATABASE;

if ($use_db) {
    require_once __DIR__ . '/../db.php';
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        try {
            $messages = Database::fetchAll(
                'SELECT id, name, text, media, approved, created_at, approved_at FROM messages ORDER BY created_at DESC'
            );
            echo json_encode($messages);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'database_error']);
        }
        exit;
    }
    
    $action = $_POST['action'] ?? 'add';
    
    try {
        if ($action === 'add') {
            $stmt = Database::query(
                'INSERT INTO messages (name, text, media, approved) VALUES (?, ?, ?, 0)',
                [
                    substr($_POST['name'] ?? 'Anônimo', 0, 200),
                    substr($_POST['text'] ?? '', 0, 2000),
                    $_POST['media'] ?? null
                ]
            );
            
            $id = Database::lastInsertId();
            $item = Database::fetchOne('SELECT id, name, text, media, approved, created_at, approved_at FROM messages WHERE id = ?', [$id]);
            
            echo json_encode(['ok' => true, 'item' => $item]);
            exit;
        }
        
        if ($action === 'approve') {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                echo json_encode(['ok' => false, 'error' => 'missing_id']);
                exit;
            }
            
            Database::query(
                'UPDATE messages SET approved = 1, approved_at = CURRENT_TIMESTAMP WHERE id = ?',
                [$id]
            );
            
            $item = Database::fetchOne('SELECT id, name, text, media, approved, created_at, approved_at FROM messages WHERE id = ?', [$id]);
            
            if (!$item) {
                echo json_encode(['ok' => false, 'error' => 'not_found']);
                exit;
            }
            
            echo json_encode(['ok' => true, 'item' => $item]);
            exit;
        }
        
        if ($action === 'delete') {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                echo json_encode(['ok' => false, 'error' => 'missing_id']);
                exit;
            }
            
            $stmt = Database::query('DELETE FROM messages WHERE id = ?', [$id]);
            
            if (Database::rowCount($stmt) === 0) {
                echo json_encode(['ok' => false, 'error' => 'not_found']);
                exit;
            }
            
            echo json_encode(['ok' => true]);
            exit;
        }
        
        echo json_encode(['ok' => false, 'error' => 'unknown_action']);
        exit;
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'database_error', 'detail' => $e->getMessage()]);
        exit;
    }
} else {
    // JSON fallback
    function data_path() { return DATA_DIR . '/messages.json'; }
    function load_messages() { $p = data_path(); if(!file_exists($p)) return []; $json = file_get_contents($p); $arr = json_decode($json, true); return is_array($arr)?$arr:[]; }
    function save_messages($arr) { $p = data_path(); file_put_contents($p, json_encode($arr, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); }
    
    $method = $_SERVER['REQUEST_METHOD'];
    $messages = load_messages();
    
    if ($method === 'GET') {
        echo json_encode(array_values($messages));
        exit;
    }
    
    $action = $_POST['action'] ?? 'add';
    
    if ($action === 'add') {
        $id = uniqid();
        $item = [
            'id' => $id,
            'name' => substr($_POST['name'] ?? 'Anônimo', 0, 200),
            'text' => substr($_POST['text'] ?? '', 0, 2000),
            'media' => $_POST['media'] ?? null,
            'approved' => false,
            'created_at' => date('c')
        ];
        $messages[$id] = $item;
        save_messages($messages);
        echo json_encode(['ok' => true, 'item' => $item]);
        exit;
    }
    
    if ($action === 'approve') {
        $id = $_POST['id'] ?? '';
        if (!$id || !isset($messages[$id])) {
            echo json_encode(['ok' => false, 'error' => 'not_found']);
            exit;
        }
        $messages[$id]['approved'] = true;
        $messages[$id]['approved_at'] = date('c');
        save_messages($messages);
        echo json_encode(['ok' => true, 'item' => $messages[$id]]);
        exit;
    }
    
    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if (!$id || !isset($messages[$id])) {
            echo json_encode(['ok' => false, 'error' => 'not_found']);
            exit;
        }
        unset($messages[$id]);
        save_messages($messages);
        echo json_encode(['ok' => true]);
        exit;
    }
    
    echo json_encode(['ok' => false, 'error' => 'unknown_action']);
    exit;
}
?>