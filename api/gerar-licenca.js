// api/gerar-licenca.js
export default async function handler(req, res) {
  // Permitir requisições de qualquer origem (CORS)
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  
  // Responder requisições OPTIONS (pré-voo do CORS)
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }
  
  // Aceitar apenas POST
  if (req.method !== 'POST') {
    return res.status(405).json({ erro: 'Método não permitido' });
  }
  
  try {
    const { nome, email, codigo } = req.body;
    
    // Validar campos
    if (!nome || !email || !codigo) {
      return res.status(400).json({ erro: 'Preencha todos os campos' });
    }
    
    // Gerar chave aleatória
    const gerarChave = () => {
      const partes = [
        Math.random().toString(36).substring(2, 6).toUpperCase(),
        Math.random().toString(36).substring(2, 6).toUpperCase(),
        Math.random().toString(36).substring(2, 6).toUpperCase(),
        Math.random().toString(36).substring(2, 6).toUpperCase()
      ];
      return partes.join('-');
    };
    
    const chave = gerarChave();
    const expiracao = new Date();
    expiracao.setDate(expiracao.getDate() + 365);
    
    // Salvar licença (opcional - você pode conectar a um banco de dados)
    console.log(`✅ Licença gerada: ${chave} para ${nome} (${email})`);
    
    // Retornar chave
    return res.status(200).json({
      sucesso: true,
      chave: chave,
      titular: nome,
      expiracao: expiracao.toISOString()
    });
    
  } catch (error) {
    console.error('Erro:', error);
    return res.status(500).json({ erro: 'Erro interno do servidor' });
  }
}