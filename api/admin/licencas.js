import { createClient } from "@supabase/supabase-js";

const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_SERVICE_ROLE_KEY
);

function autorizado(req) {
  return req.headers["x-admin-token"] === process.env.ADMIN_TOKEN;
}

export default async function handler(req, res) {
  if (!autorizado(req)) {
    return res.status(401).json({ ok: false, error: "Não autorizado" });
  }

  if (req.method === "GET") {
    const { data, error } = await supabase
      .from("licenses")
      .select("*")
      .order("created_at", { ascending: false });

    if (error) return res.status(500).json({ ok: false, error: error.message });

    return res.json({ ok: true, licencas: data });
  }

  if (req.method === "POST") {
    const { chave, cliente, validade, status, max_machines, modulos } = req.body;

    const { data, error } = await supabase
      .from("licenses")
      .insert({
        chave,
        cliente,
        validade,
        status: status || "ATIVA",
        max_machines: max_machines || 1,
        modulos: modulos || ["whatsapp", "ndi", "enquete"]
      })
      .select()
      .single();

    if (error) return res.status(500).json({ ok: false, error: error.message });

    return res.json({ ok: true, licenca: data });
  }

  if (req.method === "PUT") {
    const { id, cliente, validade, status, machine_id, max_machines, modulos } = req.body;

    const { data, error } = await supabase
      .from("licenses")
      .update({
        cliente,
        validade,
        status,
        machine_id,
        max_machines,
        modulos
      })
      .eq("id", id)
      .select()
      .single();

    if (error) return res.status(500).json({ ok: false, error: error.message });

    return res.json({ ok: true, licenca: data });
  }

  if (req.method === "DELETE") {
    const { id } = req.body;

    const { error } = await supabase
      .from("licenses")
      .delete()
      .eq("id", id);

    if (error) return res.status(500).json({ ok: false, error: error.message });

    return res.json({ ok: true });
  }

  return res.status(405).json({ ok: false, error: "Método não permitido" });
}