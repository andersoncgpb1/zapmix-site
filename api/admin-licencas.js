// api/admin-licencas.js
const SENHA_ADMIN = process.env.ADMIN_SENHA || 'admin123';

export default async function handler(req, res) {
  // CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  
  if (req.method === 'OPTIONS') return res.status(200).end();
  
  // Verificar senha (simples, para demonstração)
  const auth = req.headers.authorization;
  const senha = auth ? auth.replace('Bearer ', '') : req.query.senha;
  
  if (senha !== SENHA_ADMIN) {
    return res.status(401).json({ erro: 'Não autorizado' });
  }
  
  if (req.method === 'GET') {
    // Buscar licenças do servidor remoto
    const serverUrl = process.env.ZAPMIX_SERVER || 'http://localhost:3000';
    
    try {
      const response = await fetch(`${serverUrl}/api/admin/licencas`);
      const data = await response.json();
      res.json(data);
    } catch (error) {
      res.status(500).json({ erro: 'Erro ao buscar licenças', licencas: [] });
    }
  }
  
  if (req.method === 'POST') {
    const { acao, chave } = req.body;
    const serverUrl = process.env.ZAPMIX_SERVER || 'http://localhost:3000';
    
    try {
      const response = await fetch(`${serverUrl}/api/admin/licencas/acao`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ acao, chave })
      });
      const data = await response.json();
      res.json(data);
    } catch (error) {
      res.status(500).json({ erro: 'Erro ao executar ação' });
    }
  }
}