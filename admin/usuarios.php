<?php
// admin/usuarios.php - Manage admin users

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
    
    if ($action === 'novo_admin') {
        try {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($email) || empty($password)) {
                $error = 'Preencha todos os campos';
            } else {
                $senha_hash = password_hash($password, PASSWORD_DEFAULT);
                Database::query(
                    'INSERT INTO usuarios_admin (username, email, senha_hash) VALUES (?, ?, ?)',
                    [$username, $email, $senha_hash]
                );
                $success = 'Admin criado com sucesso!';
            }
        } catch (Exception $e) {
            $error = 'Erro: ' . $e->getMessage();
        }
    }
    
    if ($action === 'resetar_senha') {
        try {
            $id = $_POST['id'];
            $nova_senha = $_POST['nova_senha'] ?? '';
            
            if (empty($nova_senha)) {
                $error = 'Digite a nova senha';
            } else {
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                Database::query('UPDATE usuarios_admin SET senha_hash = ? WHERE id = ?', [$senha_hash, $id]);
                $success = 'Senha alterada!';
            }
        } catch (Exception $e) {
            $error = 'Erro: ' . $e->getMessage();
        }
    }
    
    if ($action === 'deletar') {
        try {
            Database::query('DELETE FROM usuarios_admin WHERE id = ? AND id != ?', [$_POST['id'], $_SESSION['admin_id']]);
            $success = 'Admin deletado!';
        } catch (Exception $e) {
            $error = 'Erro ao deletar';
        }
    }
}

$admins = Database::fetchAll('SELECT id, username, email, ativo, ultimo_acesso FROM usuarios_admin ORDER BY id DESC');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários Admin - ZapMix</title>
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
                <a href="/admin/clientes.php" class="block px-4 py-2 rounded hover:bg-gray-800">
                    <i class="fas fa-users mr-2"></i> Clientes
                </a>
                <a href="/admin/licencas.php" class="block px-4 py-2 rounded hover:bg-gray-800">
                    <i class="fas fa-key mr-2"></i> Licenças
                </a>
                <a href="/admin/usuarios.php" class="block px-4 py-2 rounded bg-blue-600">
                    <i class="fas fa-user-shield mr-2"></i> Admins
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
                    <h2 class="text-3xl font-bold text-gray-900">Usuários Admin</h2>
                    <button onclick="openNewAdminModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Novo Admin
                    </button>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                        ✓ <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                        ✗ <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Tabela -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuário</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Último Acesso</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($admin['username']); ?></td>
                                    <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($admin['email']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $admin['ativo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $admin['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?php echo $admin['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($admin['ultimo_acesso'])) : 'Nunca'; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm space-x-2">
                                        <button onclick="openResetPasswordModal(<?php echo $admin['id']; ?>)" class="text-blue-600 hover:text-blue-900">
                                            Resetar Senha
                                        </button>
                                        <?php if ($admin['id'] !== $_SESSION['admin_id']): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="deletar">
                                                <input type="hidden" name="id" value="<?php echo $admin['id']; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza?')">
                                                    Deletar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Novo Admin -->
    <div id="newAdminModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-lg font-bold mb-4">Novo Administrador</h3>
            <form method="POST">
                <input type="hidden" name="action" value="novo_admin">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Usuário</label>
                    <input type="text" name="username" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                    <input type="password" name="password" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Criar</button>
                    <button type="button" onclick="closeNewAdminModal()" class="flex-1 bg-gray-300 text-gray-800 py-2 rounded">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Resetar Senha -->
    <div id="resetPasswordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-lg font-bold mb-4">Resetar Senha</h3>
            <form method="POST">
                <input type="hidden" name="action" value="resetar_senha">
                <input type="hidden" id="resetAdminId" name="id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha</label>
                    <input type="password" name="nova_senha" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Resetar</button>
                    <button type="button" onclick="closeResetPasswordModal()" class="flex-1 bg-gray-300 text-gray-800 py-2 rounded">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openNewAdminModal() { document.getElementById('newAdminModal').classList.remove('hidden'); }
        function closeNewAdminModal() { document.getElementById('newAdminModal').classList.add('hidden'); }
        function openResetPasswordModal(id) { 
            document.getElementById('resetAdminId').value = id;
            document.getElementById('resetPasswordModal').classList.remove('hidden'); 
        }
        function closeResetPasswordModal() { document.getElementById('resetPasswordModal').classList.add('hidden'); }
    </script>
</body>
</html>
