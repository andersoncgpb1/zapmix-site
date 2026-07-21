<?php
// Broadcast display for approved messages
require_once __DIR__ . '/config.php';

// Try database first, fall back to JSON
$show = null;
$use_db = defined('USE_DATABASE') && USE_DATABASE;

if ($use_db) {
    try {
        require_once __DIR__ . '/db.php';
        $show = Database::fetchOne(
            'SELECT id, name, text, media, approved, approved_at FROM messages WHERE approved = 1 ORDER BY approved_at DESC, created_at DESC LIMIT 1'
        );
    } catch (Exception $e) {
        // Fall back to JSON if DB fails
        $use_db = false;
    }
}

if (!$use_db) {
    function load_messages() { $p = DATA_DIR . '/messages.json'; if(!file_exists($p)) return []; $arr = json_decode(file_get_contents($p), true); return is_array($arr)?$arr:[]; }
    $messages = load_messages();
    $approved = array_values(array_filter($messages, function($m){ return !empty($m['approved']); }));
    usort($approved, function($a,$b){ return strtotime($b['approved_at'] ?? $b['created_at']) - strtotime($a['approved_at'] ?? $a['created_at']); });
    $show = $approved[0] ?? null;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Exibidor - ZapMix</title>
<style>
body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;color:#fff;background:#0b1220;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
.card{max-width:1200px;width:90%;padding:40px;border-radius:12px;background:rgba(15,23,42,0.75);box-shadow:0 20px 60px rgba(0,0,0,0.6)}
.name{font-weight:800;font-size:1.8rem;color:#90d105}
.text{margin-top:12px;font-size:2.6rem}
</style>
</head>
<body>
<div class="card">
<?php if ($show): ?>
  <div class="name"><?php echo htmlspecialchars($show['name']); ?></div>
  <div class="text"><?php echo nl2br(htmlspecialchars($show['text'])); ?></div>
<?php else: ?>
  <div class="text">Nenhuma mensagem aprovada</div>
<?php endif; ?>
</div>
</body>
</html>