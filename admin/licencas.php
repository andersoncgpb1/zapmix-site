<?php
// admin/licencas.php - Gerenciar licenças

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
    
    if ($action === 'nova') {
        try {
            $chave = strtoupper(bin2hex(random_bytes(8)));
            $tipo = $_POST['tipo'] ?? 'mensal';
            $data_inicio = date('Y-m-d');
            $data_expiracao = match($tipo) {
                'trial' => date('Y-m-d', strtotime('+7 days')),
                'mensal' => date('Y-m-d', strtotime('+1 month')),
                'anual' => date('Y-m-d', strtotime('+1 year')),
                default => date('Y-m-d', strtotime('+1 month'))
            };
            
            Database::query(
                'INSERT INTO licencas (cliente_id, chave_licenca, tipo, data_inicio, data_expiracao) VALUES (?, ?, ?, ?, ?)',
                [
                    $_POST['cliente_id'],
                    $chave,
                    $tipo,
                    $data_inicio,
                    $data_expiracao
                ]
            );
            $success = "Licença criada! Chave: <strong>$chave</strong>";
        } catch (Exception $e) {
            $error = 'Erro ao criar licença: ' . $e->getMessage();
        }
    }
}

// Get licencas with cliente info
$licencas = Database::fetchAll(`
    SELECT l.*, c.nome as cliente_nome FROM licencas l
    LEFT JOIN clientes c ON l.cliente_id = c.id
    ORDER BY l.data_criacao DESC
`);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Licenças - Admin ZapMix</title>
    <link rel="stylesheet" href="/assets/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen bg-gray-100">
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
                <a href="/admin/licencas.php" class="block px-4 py-2 rounded bg-blue-600">
                    <i class="fas fa-key mr-2"></i> Licenças
                </a>
                <a href="/admin/logout.php" class="block px-4 py-2 rounded hover:bg-red-600">
                    <i class="fas fa-sign-out-alt mr-2"></i> Sair
                </a>
            </nav>
        </div>
        
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Licenças</h2>
                    <button onclick="openNewLicenseModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Gerar Licença
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
                
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Chave</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expira em</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($licencas as $l): ?>
                                <tr>
                                    <td class="px-6 py-4 font-mono text-sm"><?php echo htmlspecialchars($l['chave_licenca']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($l['cliente_nome'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($l['tipo']); ?></td>
                                    <td class="px-6 py-4 text-sm"><?php echo date('d/m/Y', strtotime($l['data_expiracao'])); ?></td>
                                    <td class="px-6 py-4">
                                        <?php $expirou = strtotime($l['data_expiracao']) < time(); ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo !$expirou && $l['ativa'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo !$expirou && $l['ativa'] ? 'Ativa' : 'Expirada'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal -->
    <div id="newLicenseModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-lg font-bold mb-4">Gerar Licença</h3>
            <form method="POST">
                <input type="hidden" name="action" value="nova">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                    <select name="cliente_id" required class="w-full px-3 py-2 border rounded-lg">
                        <option>Selecione um cliente...</option>
                        <?php
                        $clientes = Database::fetchAll('SELECT id, nome FROM clientes WHERE ativa = 1');
                        foreach ($clientes as $c):
                        ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="tipo" required class="w-full px-3 py-2 border rounded-lg">
                        <option value="trial">Trial (7 dias)</option>
                        <option value="mensal">Mensal</option>
                        <option value="anual">Anual</option>
                    </select>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Gerar</button>
                    <button type="button" onclick="closeNewLicenseModal()" class="flex-1 bg-gray-300 text-gray-800 py-2 rounded">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openNewLicenseModal() { document.getElementById('newLicenseModal').classList.remove('hidden'); }
        function closeNewLicenseModal() { document.getElementById('newLicenseModal').classList.add('hidden'); }
    </script>
</body>
</html>
