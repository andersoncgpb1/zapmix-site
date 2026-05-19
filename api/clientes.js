import { createClient } from "@supabase/supabase-js";

const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_SERVICE_ROLE_KEY
);

export default async function handler(req, res) {
  // CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  // Verificar token admin
  const auth = req.headers.authorization || "";
  const token = auth.replace("Bearer ", "");
  
  // Validação simples do token (ajuste conforme sua implementação)
  if (!token) {
    return res.status(401).json({ ok: false, erro: "Não autorizado" });
  }

  // GET - Listar todos os clientes
  if (req.method === 'GET') {
    const { data, error } = await supabase
      .from('clientes')
      .select('*')
      .order('created_at', { ascending: false });

    if (error) {
      return res.status(500).json({ ok: false, erro: error.message });
    }

    return res.json({ ok: true, clientes: data });
  }

  // POST - Criar novo cliente
  if (req.method === 'POST') {
    const { nome, email, telefone, empresa, observacoes, status } = req.body;

    if (!nome) {
      return res.status(400).json({ ok: false, erro: "Nome é obrigatório" });
    }

    const { data, error } = await supabase
      .from('clientes')
      .insert({
        nome,
        email: email || null,
        telefone: telefone || null,
        empresa: empresa || null,
        observacoes: observacoes || null,
        status: status || 'ATIVO'
      })
      .select()
      .single();

    if (error) {
      return res.status(500).json({ ok: false, erro: error.message });
    }

    return res.json({ ok: true, cliente: data });
  }

  // PUT - Atualizar cliente
  if (req.method === 'PUT') {
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
    update.updated_at = new Date();

    const { data, error } = await supabase
      .from('clientes')
      .update(update)
      .eq('id', id)
      .select()
      .single();

    if (error) {
      return res.status(500).json({ ok: false, erro: error.message });
    }

    return res.json({ ok: true, cliente: data });
  }

  // DELETE - Excluir cliente
  if (req.method === 'DELETE') {
    const { id } = req.body;

    if (!id) {
      return res.status(400).json({ ok: false, erro: "ID é obrigatório" });
    }

    const { error } = await supabase
      .from('clientes')
      .delete()
      .eq('id', id);

    if (error) {
      return res.status(500).json({ ok: false, erro: error.message });
    }

    return res.json({ ok: true });
  }

  return res.status(405).json({ ok: false, erro: "Método não permitido" });
}