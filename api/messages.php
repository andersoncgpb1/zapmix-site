<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

function data_path(){ return DATA_DIR . '/messages.json'; }
function load_messages(){ $p = data_path(); if(!file_exists($p)) return []; $json = file_get_contents($p); $arr = json_decode($json, true); return is_array($arr)?$arr:[]; }
function save_messages($arr){ $p = data_path(); file_put_contents($p, json_encode($arr, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); }

$method = $_SERVER['REQUEST_METHOD'];
$messages = load_messages();

if ($method === 'GET'){
  echo json_encode(array_values($messages));
  exit;
}

// For simplicity use POST for add/approve/delete
$action = $_POST['action'] ?? 'add';

if ($action === 'add'){
  $id = uniqid();
  $item = [
    'id'=>$id,
    'name'=>substr($_POST['name'] ?? 'Anônimo',0,200),
    'text'=>substr($_POST['text'] ?? '',0,2000),
    'media'=>$_POST['media'] ?? null,
    'approved'=>false,
    'created_at'=>date('c')
  ];
  $messages[$id] = $item;
  save_messages($messages);
  echo json_encode(['ok'=>true,'item'=>$item]);
  exit;
}

if ($action === 'approve'){
  $id = $_POST['id'] ?? '';
  if (!$id || !isset($messages[$id])){ echo json_encode(['ok'=>false,'error'=>'not_found']); exit; }
  $messages[$id]['approved'] = true;
  $messages[$id]['approved_at'] = date('c');
  save_messages($messages);
  echo json_encode(['ok'=>true,'item'=>$messages[$id]]);
  exit;
}

if ($action === 'delete'){
  $id = $_POST['id'] ?? '';
  if (!$id || !isset($messages[$id])){ echo json_encode(['ok'=>false,'error'=>'not_found']); exit; }
  unset($messages[$id]);
  save_messages($messages);
  echo json_encode(['ok'=>true]);
  exit;
}

// unknown action
echo json_encode(['ok'=>false,'error'=>'unknown_action']);
exit;
?>