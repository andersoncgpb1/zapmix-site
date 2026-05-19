import { createClient } from "@supabase/supabase-js";

const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_SERVICE_ROLE_KEY
);

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  const auth = req.headers.authorization || "";
  const token = auth.replace("Bearer ", "");
  
  if (!token) {
    return res.status(401).json({ ok: false, erro: "Não autorizado" });
  }

  // GET - Listar clientes
  if (req.method === 'GET') {
    const { data, error } = await supabase
      .from('clientes')
      .select('*')
      .order('id');

    if (error) {
      return res.status(500).json({ ok: false, erro: error.message });
    }

    return res.json({ ok: true, clientes: data || [] });
  }

  // POST - Criar cliente
  if (req.method === 'POST') {
    const { nome, email, telefone, empresa, observacoes } = req.body;
    
    if (!nome) {
      return res.status(400).json({ ok: false, erro: "Nome é obrigatório" });
    }

    // Tentar inserir ignorando RLS
    const { data, error } = await supabase
      .from('clientes')
      .insert({ nome, email, telefone, empresa, observacoes })
      .select();

    if (error) {
      console.error("Erro ao inserir:", error);
      return res.status(500).json({ ok: false, erro: error.message });
    }

    return res.json({ ok: true, cliente: data?.[0] });
  }

  return res.status(405).json({ ok: false, erro: "Método não permitido" });
}