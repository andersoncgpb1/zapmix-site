import { createClient } from "@supabase/supabase-js";

const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_SERVICE_ROLE_KEY
);

export default async function handler(req, res) {
  // CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  // Verificação simplificada do token
  const auth = req.headers.authorization || "";
  const token = auth.replace("Bearer ", "");
  
  if (!token) {
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
        console.error("Erro Supabase GET:", error);
        return res.status(500).json({ ok: false, erro: error.message });
      }

      // Retorna sempre um array, mesmo que vazio
      return res.status(200).json({ ok: true, licencas: data || [] });
    } catch (err) {
      console.error("Erro interno GET:", err);
      return res.status(500).json({ ok: false, erro: err.message });
    }
  }

  // POST - Criar nova licença
  if (req.method === 'POST') {
    try {
      const { chave, cliente, cliente_id, validade, status, max_machines, modulos } = req.body;

      if (!cliente || !validade) {
        return res.status(400).json({ ok: false, erro: "Cliente e validade são obrigatórios" });
      }

      const { data, error } = await supabase
        .from('licenses')
        .insert({
          chave: chave || gerarChave(),
          cliente,
          cliente_id: cliente_id || null,
          validade,
          status: status || 'ATIVA',
          max_machines: max_machines || 1,
          modulos: modulos || ["whatsapp", "ndi", "enquete"]
        })
        .select();

      if (error) {
        console.error("Erro Supabase POST:", error);
        return res.status(500).json({ ok: false, erro: error.message });
      }

      return res.status(200).json({ ok: true, licenca: data?.[0] || null });
    } catch (err) {
      console.error("Erro interno POST:", err);
      return res.status(500).json({ ok: false, erro: err.message });
    }
  }

  // PUT - Atualizar licença
  if (req.method === 'PUT') {
    try {
      const { id, status, machine_id, validade, ultima_renovacao } = req.body;

      if (!id) {
        return res.status(400).json({ ok: false, erro: "ID é obrigatório" });
      }

      const updateData = {};
      if (status !== undefined) updateData.status = status;
      if (machine_id !== undefined) updateData.machine_id = machine_id;
      if (validade !== undefined) updateData.validade = validade;
      if (ultima_renovacao !== undefined) updateData.ultima_renovacao = ultima_renovacao;

      const { data, error } = await supabase
        .from('licenses')
        .update(updateData)
        .eq('id', id)
        .select();

      if (error) {
        console.error("Erro Supabase PUT:", error);
        return res.status(500).json({ ok: false, erro: error.message });
      }

      return res.status(200).json({ ok: true, licenca: data?.[0] || null });
    } catch (err) {
      console.error("Erro interno PUT:", err);
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
        console.error("Erro Supabase DELETE:", error);
        return res.status(500).json({ ok: false, erro: error.message });
      }

      return res.status(200).json({ ok: true });
    } catch (err) {
      console.error("Erro interno DELETE:", err);
      return res.status(500).json({ ok: false, erro: err.message });
    }
  }

  return res.status(405).json({ ok: false, erro: "Método não permitido" });
}

function gerarChave() {
  const bloco = () => Math.random().toString(36).substring(2, 6).toUpperCase();
  return `ZAPMIX-${bloco()}-${bloco()}-${bloco()}`;
}