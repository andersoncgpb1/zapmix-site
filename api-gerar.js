// api/gerar-licenca.js (para Vercel ou servidor independente)
const crypto = require('crypto');

// Função para gerar chave
function gerarChave() {
  const random = crypto.randomBytes(16).toString('hex').toUpperCase();
  return `${random.substring(0,4)}-${random.substring(4,8)}-${random.substring(8,12)}-${random.substring(12,16)}`;
}

export default async function handler(req, res) {
  // Configurar CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }
  
  if (req.method !== 'POST') {
    return res.status(405).json({ erro: 'Método não permitido' });
  }
  
  const { nome, email, codigo } = req.body;
  
  if (!nome || !email) {
    return res.status(400).json({ erro: 'Nome e e-mail são obrigatórios' });
  }
  
  // VALIDAÇÃO DO CÓDIGO DE COMPRA (Hotmart/Kiwify)
  // Aqui você pode integrar com a API da Hotmart para validar o código
  // Por enquanto, aceitamos qualquer código que não esteja vazio
  
  if (!codigo) {
    return res.status(400).json({ erro: 'Código de compra é obrigatório' });
  }
  
  // Gerar chave
  const chave = gerarChave();
  
  // Salvar no banco de dados ou arquivo
  // Como estamos no Vercel, você precisa de um banco de dados externo
  // Ex: MongoDB, Firebase, Supabase, ou arquivo no S3
  
  // Por enquanto, vamos retornar a chave (mas ela não será salva persistentemente)
  // Em produção, salve em um banco de dados!
  
  console.log(`✅ Licença gerada: ${chave} para ${nome} (${email})`);
  
  res.json({ 
    sucesso: true, 
    chave: chave,
    titular: nome,
    expiracao: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toISOString()
  });
}