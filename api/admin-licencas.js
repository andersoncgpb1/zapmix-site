import crypto from "crypto";
import { createClient } from "@supabase/supabase-js";

const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_SERVICE_ROLE_KEY
);

function verificarToken(req) {
  try {
    const auth = req.headers.authorization || "";
    const token = auth.replace("Bearer ", "");

    if (!token || !token.includes(".")) return false;

    const [base64, assinatura] = token.split(".");

    const assinaturaCorreta = crypto
      .createHmac("sha256", process.env.ADMIN_SECRET)
      .update(base64)
      .digest("base64url");

    if (assinatura !== assinaturaCorreta) return false;

    const payload = JSON.parse(Buffer.from(base64, "base64url").toString());

    if (Date.now() > payload.exp) return false;

    return true;
  } catch {
    return false;
  }
}

function gerarChave() {
  const bloco = () => crypto.randomBytes(2).toString("hex").toUpperCase();
  return `ZAPMIX-${bloco()}-${bloco()}-${bloco()}`;
}

export default async function handler(req, res) {
  res.setHeader("Access-Control-Allow-Origin", "*");
  res.setHeader("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
  res.setHeader("Access-Control-Allow-Headers", "Content-Type, Authorization");

  if (req.method === "OPTIONS") {
    return res.status(200).end();
  }

  if (!verificarToken(req)) {
    return res.status(401).json({ ok: false, erro: "Não autorizado" });
  }

  if (req.method === "GET") {
    const { data, error } = await supabase
      .from("licenses")
      .select(`
        *,
        clientes (
          id,
          nome,
          email
        )
      `)
      .order("created_at", { ascending: false });

    if (error) {
      return res.status(500).json({ ok: false, erro: error.message });
    }

    return res.json({ ok: true, licencas: data });
  }

  if (req.method === "POST") {
    const {
      chave,
      cliente,
      cliente_id,
      validade,
      status,
      max_machines,
      modulos
    } = req.body || {};

    if (!cliente || !validade) {
      return res.status(400).json({
        ok: false,
        erro: "Cliente e validade são obrigatórios"
      });
    }

    const insert = {
      chave: chave || gerarChave(),
      cliente,
      validade,
      status: status || "ATIVA",
      max_machines: max_machines || 1,
      modulos: modulos || ["whatsapp", "ndi", "enquete"],
      created_at: new Date().toISOString()
    };

    if (cliente_id !== undefined && cliente_id !== null && cliente_id !== "") {
      insert.cliente_id = Number(cliente_id);
    }

    const { data, error } = await supabase
      .from("licenses")
      .insert(insert)
      .select(`
        *,
        clientes (
          id,
          nome,
          email
        )
      `)
      .single();

    if (error) {
      return res.status(500).json({ ok: false, erro: error.message });
    }

    return res.json({ ok: true, licenca: data });
  }

  if (req.method === "PUT") {
    const {
      id,
      cliente,
      cliente_id,
      validade,
      status,
      machine_id,
      max_machines,
      modulos
    } = req.body || {};

    if (!id) {
      return res.status(400).json({ ok: false, erro: "ID é obrigatório" });
    }

    const update = {};

    if (cliente !== undefined) update.cliente = cliente;
    if (validade !== undefined) update.validade = validade;
    if (status !== undefined) update.status = status;
    if (machine_id !== undefined) update.machine_id = machine_id;
    if (max_machines !== undefined) update.max_machines = max_machines;
    if (modulos !== undefined) update.modulos = modulos;

    if (cliente_id !== undefined) {
      update.cliente_id =
        cliente_id === null || cliente_id === ""
          ? null
          : Number(cliente_id);
    }

    const { data, error } = await supabase
      .from("licenses")
      .update(update)
      .eq("id", id)
      .select(`
        *,
        clientes (
          id,
          nome,
          email
        )
      `)
      .single();

    if (error) {
      return res.status(500).json({ ok: false, erro: error.message });
    }

    return res.json({ ok: true, licenca: data });
  }

  if (req.method === "DELETE") {
    const { id } = req.body || {};

    if (!id) {
      return res.status(400).json({ ok: false, erro: "ID é obrigatório" });
    }

    const { error } = await supabase
      .from("licenses")
      .delete()
      .eq("id", id);

    if (error) {
      return res.status(500).json({ ok: false, erro: error.message });
    }

    return res.json({ ok: true });
  }

  return res.status(405).json({ ok: false, erro: "Método não permitido" });
}