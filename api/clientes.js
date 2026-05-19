import { createClient } from "@supabase/supabase-js";

const supabaseUrl = process.env.SUPABASE_URL;
const supabaseKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

const supabase = createClient(supabaseUrl, supabaseKey);

function verificarToken(token) {
  if (!token) return false;
  return true;
}

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  const auth = req.headers.authorization || "";
  const token = auth.replace("Bearer ", "");
  
  if (!verificarToken(token)) {
    return res.status(401).json({ ok: false, erro: "Não autorizado" });
  }

  // GET - Listar clientes
  if (req.method === 'GET') {
    try {
      const { data, error } = await supabase
        .from('clientes')
        .select('*')
        .order('id');

      if (error) {
        return res.status(500).json({ ok: false, erro: error.message });
      }

      return res.json({ ok: true, clientes: data || [] });
    } catch (err) {
      return res.status(500).json({ ok: false, erro: err.message });
    }
  }

  // POST - Criar cliente
  if (req.method === 'POST') {
    try {
      const { nome, email, telefone, empresa, observacoes } = req.body;
      
      if (!nome) {
        return res.status(400).json({ ok: false, erro: "Nome é obrigatório" });
      }

      const { data, error } = await supabase
        .from('clientes')
        .insert({ nome, email, telefone, empresa, observacoes })
        .select();

      if (error) {
        return res.status(500).json({ ok: false, erro: error.message });
      }

      return res.json({ ok: true, cliente: data?.[0] });
    } catch (err) {
      return res.status(500).json({ ok: false, erro: err.message });
    }
  }

  // PUT - Atualizar cliente
  if (req.method === 'PUT') {
    try {
      const { id, nome, email, telefone, empresa, observacoes, status } = req.body;
      
      if (!id) {
        return res.status(400).json({ ok: false, erro: "ID é obrigatório" });
      }

      const update = {};
      if (nome !== undefined) update.nome = nome;
      if (email !== undefined) update.email = email;
      if (telefone !== undefined) update.telefone = telefone;
      if (empresa !== undefined) update.empresa = empresa;
      if (observacoes !== undefined) update.observacoes = observacoes;
      if (status !== undefined) update.status = status;

      const { data, error } = await supabase
        .from('clientes')
        .update(update)
        .eq('id', id)
        .select();

      if (error) {
        return res.status(500).json({ ok: false, erro: error.message });
      }

      return res.json({ ok: true, cliente: data?.[0] });
    } catch (err) {
      return res.status(500).json({ ok: false, erro: err.message });
    }
  }

  // DELETE - Excluir cliente
  if (req.method === 'DELETE') {
    try {
      const { id } = req.body;
      
      const { error } = await supabase
        .from('clientes')
        .delete()
        .eq('id', id);

      if (error) {
        return res.status(500).json({ ok: false, erro: error.message });
      }

      return res.json({ ok: true });
    } catch (err) {
      return res.status(500).json({ ok: false, erro: err.message });
    }
  }

  return res.status(405).json({ ok: false, erro: "Método não permitido" });
}