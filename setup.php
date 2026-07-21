<?php
// setup.php - Database setup wizard

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Check if database is already set up
try {
    $check = Database::fetchOne("SHOW TABLES LIKE 'messages'");
    if ($check) {
        $setup_complete = true;
    } else {
        $setup_complete = false;
    }
} catch (Exception $e) {
    $setup_complete = false;
    $error = 'Erro ao conectar: ' . $e->getMessage();
}

// If form submitted, create tables
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_tables'])) {
    try {
        $schema = file_get_contents(__DIR__ . '/schema.sql');
        $queries = array_filter(array_map('trim', explode(';', $schema)));
        
        foreach ($queries as $query) {
            if (!empty($query)) {
                Database::query($query);
            }
        }
        
        $success = true;
        $setup_complete = true;
    } catch (Exception $e) {
        $success = false;
        $error = 'Erro ao criar tabelas: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZapMix - Setup do Banco de Dados</title>
    <link rel="stylesheet" href="/assets/tailwind.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">ZapMix Setup</h1>
            
            <?php if (isset($success) && $success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                    ✓ Banco de dados configurado com sucesso!
                </div>
                <div class="space-y-3">
                    <a href="/admin.php" class="block text-center bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                        Ir para Admin
                    </a>
                    <a href="/index.php" class="block text-center bg-gray-200 text-gray-800 py-2 rounded hover:bg-gray-300">
                        Ir para Homepage
                    </a>
                </div>
            <?php elseif ($setup_complete): ?>
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-6">
                    ✓ Banco de dados já está configurado!
                </div>
                <div class="space-y-3">
                    <a href="/admin.php" class="block text-center bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                        Ir para Admin
                    </a>
                    <a href="/index.php" class="block text-center bg-gray-200 text-gray-800 py-2 rounded hover:bg-gray-300">
                        Ir para Homepage
                    </a>
                </div>
            <?php else: ?>
                <div class="bg-gray-50 p-4 rounded mb-6">
                    <h2 class="font-semibold text-gray-900 mb-3">Configuração Inicial</h2>
                    <p class="text-gray-700 text-sm mb-4">
                        Clique no botão abaixo para criar as tabelas do banco de dados MySQL.
                    </p>
                    
                    <?php if (isset($error)): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                            ✗ <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p><strong>Host:</strong> <?php echo htmlspecialchars(DB_HOST); ?></p>
                        <p><strong>Database:</strong> <?php echo htmlspecialchars(DB_NAME); ?></p>
                        <p><strong>User:</strong> <?php echo htmlspecialchars(DB_USER); ?></p>
                    </div>
                </div>
                
                <form method="POST" class="space-y-3">
                    <button type="submit" name="create_tables" value="1" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-semibold">
                        Criar Tabelas do Banco
                    </button>
                    <a href="/index.php" class="block text-center bg-gray-200 text-gray-800 py-2 rounded hover:bg-gray-300">
                        Voltar para Homepage
                    </a>
                </form>
            <?php endif; ?>
            
            <hr class="my-6">
            
            <div class="text-xs text-gray-500 space-y-1">
                <p><strong>Status da Configuração:</strong></p>
                <p>✓ Config: OK</p>
                <p><?php echo $setup_complete ? '✓' : '✗'; ?> Database: <?php echo $setup_complete ? 'OK' : 'Não configurado'; ?></p>
            </div>
        </div>
    </div>
</body>
</html>
