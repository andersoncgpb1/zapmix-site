import { createClient } from "@supabase/supabase-js";

// Inicializar Supabase
const supabaseUrl = process.env.SUPABASE_URL;
const supabaseKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

if (!supabaseUrl || !supabaseKey) {
  console.error("ERRO: Variáveis SUPABASE_URL ou SUPABASE_SERVICE_ROLE_KEY não configuradas!");
}

const supabase = createClient(supabaseUrl, supabaseKey);

// Função para verificar token (simplificada)
function verificarToken(token) {
  if (!token) return false;
  // Aceita qualquer token não vazio para teste
  return true;
}

export default async function handler(req, res) {
  // CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  // Verificar token
  const auth = req.headers.authorization || "";
  const token = auth.replace("Bearer ", "");
  
  if (!verificarToken(token)) {
    return res.status(401).json({ ok: false, erro: "Não autorizado" });
  }

  // GET - Listar todas licenças
  if (req.method === 'GET') {
    try {
      const { data, error } = await supabase
        .from('licenses')
        .select('*')
        .order('id', { ascending: false });

      if (error) {
        return res.status(500).json({ ok: false, erro: error.message });
      }

      return res.status(200).json({ ok: true, licencas: data || [] });
    } catch (err) {
      return res.status(500).json({ ok: false, erro: err.message });
    }
  }

  // POST - Criar nova licença
  if (req.method === 'POST') {
    try {
      const { chave, cliente, validade, status, cliente_id, max_machines, modulos } = req.body;
      
      if (!cliente || !validade) {
        return res.status(400).json({ ok: false, erro: "Cliente e validade são obrigatórios" });
      }

      const { data, error } = await supabase
        .from('licenses')
        .insert({
          chave: chave || gerarChave(),
          cliente: cliente,
          cliente_id: cliente_id || null,
          validade: validade,
          status: status || 'ATIVA',
          max_machines: max_machines || 1,
          modulos: modulos || ['whatsapp', 'ndi', 'enquete'],
          created_at: new Date().toISOString()
        })
        .select()
        .single();

      if (error) {
        return res.status(500).json({ ok: false, erro: error.message });
      }

      return res.status(200).json({ ok: true, licenca: data });
    } catch (err) {
      return res.status(500).json({ ok: false, erro: err.message });
    }
  }

  // PUT - Atualizar licença
  if (req.method === 'PUT') {
    try {
      const { id, status, machine_id, validade } = req.body;

      if (!id) {
        return res.status(400).json({ ok: false, erro: "ID é obrigatório" });
      }

      const update = {};
      if (status !== undefined) update.status = status;
      if (machine_id !== undefined) update.machine_id = machine_id;
      if (validade !== undefined) update.validade = validade;

      const { data, error } = await supabase
        .from('licenses')
        .update(update)
        .eq('id', id)
        .select()
        .single();

      if (error) {
        return res.status(500).json({ ok: false, erro: error.message });
      }

      return res.status(200).json({ ok: true, licenca: data });
    } catch (err) {
      return res.status(500).json({ ok: false, erro: err.message });
    }
  }

  // DELETE - Excluir licença
  if (req.method === 'DELETE') {
    try {
      const { id } = req.body;

      if (!id) {
        return res.status(400).json({ ok: false, erro: "ID é obrigatório" });
      }

      const { error } = await supabase
        .from('licenses')
        .delete()
        .eq('id', id);

      if (error) {
        return res.status(500).json({ ok: false, erro: error.message });
      }

      return res.status(200).json({ ok: true });
    } catch (err) {
      return res.status(500).json({ ok: false, erro: err.message });
    }
  }

  return res.status(405).json({ ok: false, erro: "Método não permitido" });
}

function gerarChave() {
  const bytes = new Uint8Array(8);
  crypto.getRandomValues(bytes);
  const hex = Array.from(bytes, b => b.toString(16).padStart(2, '0')).join('').toUpperCase();
  return `ZAPMIX-${hex.slice(0,4)}-${hex.slice(4,8)}-${hex.slice(8,12)}`;
}