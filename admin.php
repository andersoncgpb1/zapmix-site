<?php
session_start();
require_once __DIR__ . '/config.php';

function is_logged_in(){
  return !empty($_SESSION['zapmix_admin']);
}

function attempt_login($user, $pass){
  if ($user !== ADMIN_USER) return false;
  return password_verify($pass, ADMIN_PASS_HASH);
}

if (isset($_GET['logout'])){
  session_destroy();
  header('Location: /admin.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user'])){
  $user = $_POST['user'];
  $pass = $_POST['pass'] ?? '';
  if (attempt_login($user, $pass)){
    $_SESSION['zapmix_admin'] = $user;
    header('Location: /admin.php');
    exit;
  } else {
    $error = 'Usuário ou senha inválidos';
  }
}

if (!is_logged_in()):
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - ZapMix Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">
  <form method="post" class="bg-white p-8 rounded shadow-md w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6">Painel ZapMix</h2>
    <?php if (!empty(
      $error)): ?><div class="mb-4 text-red-600"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <label class="block mb-2">Usuário<input name="user" class="w-full p-2 border rounded" required></label>
    <label class="block mb-4">Senha<input name="pass" type="password" class="w-full p-2 border rounded" required></label>
    <div class="flex gap-2">
      <button class="bg-green-500 text-white px-4 py-2 rounded font-bold">Entrar</button>
      <a class="px-4 py-2 rounded border" href="/">Voltar</a>
    </div>
  </form>
</body>
</html>
<?php
else:
  // Logged in - show admin UI
  require_once __DIR__ . '/admin_ui.php';
endif;
?>