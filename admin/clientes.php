<?php
// admin/clientes.php - Gerenciar clientes

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['zapmix_admin'])) {
    header('Location: /admin.php');
    exit;
}

// Ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'novo') {
        try {
            Database::query(
                'INSERT INTO clientes (nome, email, telefone, empresa) VALUES (?, ?, ?, ?)',
                [
                    $_POST['nome'] ?? '',
                    $_POST['email'] ?? '',
                    $_POST['telefone'] ?? '',
                    $_POST['empresa'] ?? ''
                ]
            );
            $success = 'Cliente criado com sucesso!';
        } catch (Exception $e) {
            $error = 'Erro ao criar cliente: ' . $e->getMessage();
        }
    }
    
    if ($action === 'deletar') {
        try {
            Database::query('DELETE FROM clientes WHERE id = ?', [$_POST['id']]);
            $success = 'Cliente deletado!';
        } catch (Exception $e) {
            $error = 'Erro ao deletar: ' . $e->getMessage();
        }
    }
}

// Get all clientes
$clientes = Database::fetchAll('SELECT * FROM clientes ORDER BY data_cadastro DESC');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Admin ZapMix</title>
    <link rel="stylesheet" href="/assets/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-900 text-white p-4">
            <div class="mb-8">
                <h1 class="text-2xl font-bold">ZapMix</h1>
                <p class="text-gray-400 text-sm">Admin</p>
            </div>
            <nav class="space-y-2">
                <a href="/admin/dashboard.php" class="block px-4 py-2 rounded hover:bg-gray-800">
                    <i class="fas fa-chart-line mr-2"></i> Dashboard
                </a>
                <a href="/admin/clientes.php" class="block px-4 py-2 rounded bg-blue-600">
                    <i class="fas fa-users mr-2"></i> Clientes
                </a>
                <a href="/admin/licencas.php" class="block px-4 py-2 rounded hover:bg-gray-800">
                    <i class="fas fa-key mr-2"></i> Licenças
                </a>
                <a href="/admin/logout.php" class="block px-4 py-2 rounded hover:bg-red-600">
                    <i class="fas fa-sign-out-alt mr-2"></i> Sair
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Clientes</h2>
                    <button onclick="openNewClientModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Novo Cliente
                    </button>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Tabela -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Empresa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($clientes as $c): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($c['nome']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($c['email']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($c['empresa'] ?? '-'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $c['ativa'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $c['ativa'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="/admin/cliente-detalhes.php?id=<?php echo $c['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Editar</a>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="deletar">
                                            <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza?')">Deletar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Novo Cliente -->
    <div id="newClientModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-lg font-bold mb-4">Novo Cliente</h3>
            <form method="POST">
                <input type="hidden" name="action" value="novo">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                    <input type="text" name="nome" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <input type="text" name="telefone" class="w-full px-3 py-2 border rounded-lg">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
                    <input type="text" name="empresa" class="w-full px-3 py-2 border rounded-lg">
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Criar</button>
                    <button type="button" onclick="closeNewClientModal()" class="flex-1 bg-gray-300 text-gray-800 py-2 rounded hover:bg-gray-400">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openNewClientModal() {
            document.getElementById('newClientModal').classList.remove('hidden');
        }
        function closeNewClientModal() {
            document.getElementById('newClientModal').classList.add('hidden');
        }
    </script>
</body>
</html>
