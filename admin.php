<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Check if logged in
if (isset($_GET['logout'])){
    session_destroy();
    header('Location: /admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])){
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $admin = Database::fetchOne(
            'SELECT id, username, senha_hash, ativo FROM usuarios_admin WHERE username = ? AND ativo = 1',
            [$username]
        );
        
        if ($admin && password_verify($password, $admin['senha_hash'])) {
            $_SESSION['zapmix_admin'] = $admin['username'];
            $_SESSION['admin_id'] = $admin['id'];
            
            // Update last access
            Database::query(
                'UPDATE usuarios_admin SET ultimo_acesso = CURRENT_TIMESTAMP WHERE id = ?',
                [$admin['id']]
            );
            
            header('Location: /admin/dashboard.php');
            exit;
        } else {
            $error = 'Usuário ou senha inválidos';
        }
    } catch (Exception $e) {
        $error = 'Erro ao conectar ao banco de dados';
    }
}

// Check if already logged in
if (isset($_SESSION['zapmix_admin'])) {
    header('Location: /admin/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ZapMix Admin</title>
    <link rel="stylesheet" href="/assets/tailwind.css">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-12 text-center">
                <h1 class="text-4xl font-bold text-white mb-2">ZapMix</h1>
                <p class="text-blue-100">Painel Administrativo</p>
            </div>
            
            <!-- Form -->
            <div class="px-8 py-8">
                <?php if (isset($error)): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Usuário</label>
                        <input 
                            type="text" 
                            name="username" 
                            required 
                            autofocus
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                            placeholder="admin"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                        <input 
                            type="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                            placeholder="••••••••"
                        >
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-200 transform hover:scale-105"
                    >
                        Entrar
                    </button>
                </form>
                
                <div class="mt-6 text-center border-t pt-6">
                    <a href="/" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                        ← Voltar para Homepage
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Info Footer -->
        <div class="mt-8 text-center text-gray-400 text-sm">
            <p>🔒 Acesso restrito ao painel administrativo</p>
            <p class="mt-2 text-gray-500">Dados: admin / changeme (padrão)</p>
        </div>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</body>
</html>
