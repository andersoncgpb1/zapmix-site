<?php
// Simple enquete exibidor that reads a poll payload from data/poll.json
require_once __DIR__ . '/config.php';
$pollPath = DATA_DIR . '/poll.json';
$poll = file_exists($pollPath) ? json_decode(file_get_contents($pollPath), true) : null;
$results = $poll['results'] ?? [];
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Exibidor Enquete - ZapMix</title>
<style>
body{font-family:Inter,system-ui;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#0b1220;color:#fff}
.container{width:90%;max-width:1100px}
.option{display:flex;align-items:center;gap:12px;padding:14px;background:rgba(255,255,255,0.04);border-radius:10px;margin-bottom:10px}
.bar{height:28px;background:#90d105;border-radius:8px}
.label{flex:1}
</style>
</head>
<body>
<div class="container">
<?php if ($poll): ?>
  <h1 style="color:#90d105"><?php echo htmlspecialchars($poll['question'] ?? 'Enquete'); ?></h1>
  <?php foreach($poll['options'] as $i=>$opt): $val = $results[$i] ?? 0; $total = array_sum($results)?:1; $pct=round($val*100/$total); ?>
    <div class="option"><div class="label"><?php echo htmlspecialchars($opt); ?> — <?php echo $pct; ?>%</div><div style="width:40%;"><div class="bar" style="width:<?php echo $pct; ?>%"></div></div></div>
  <?php endforeach; ?>
<?php else: ?>
  <div>Nenhuma enquete configurada</div>
<?php endif; ?>
</div>
</body>
</html>