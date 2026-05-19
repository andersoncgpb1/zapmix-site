import { createClient } from "@supabase/supabase-js";

const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_SERVICE_ROLE_KEY
);

export default async function handler(req, res) {
  if (req.method !== "POST") {
    return res.status(405).json({ ok: false, error: "Método não permitido" });
  }

  const { chave, machineId } = req.body || {};

  if (!chave || !machineId) {
    return res.status(400).json({
      ok: false,
      status: "INVALIDA",
      error: "Chave ou máquina inválida"
    });
  }

  const { data: licenca, error } = await supabase
    .from("licenses")
    .select("*")
    .eq("chave", chave)
    .single();

  if (error || !licenca) {
    return res.status(403).json({
      ok: false,
      status: "INVALIDA",
      error: "Licença inválida"
    });
  }

  if (licenca.status !== "ATIVA") {
    return res.status(403).json({
      ok: false,
      status: licenca.status,
      error: "Licença bloqueada ou inativa"
    });
  }

  const hoje = new Date();
  const validade = new Date(licenca.validade + "T23:59:59");

  if (hoje > validade) {
    return res.status(403).json({
      ok: false,
      status: "EXPIRADA",
      error: "Licença expirada"
    });
  }

  if (licenca.machine_id && licenca.machine_id !== machineId) {
    return res.status(403).json({
      ok: false,
      status: "BLOQUEADA",
      error: "Licença já ativada em outro computador"
    });
  }

  if (!licenca.machine_id) {
    await supabase
      .from("licenses")
      .update({ machine_id: machineId })
      .eq("id", licenca.id);
  }

  return res.json({
    ok: true,
    status: "ATIVA",
    cliente: licenca.cliente,
    validade: licenca.validade,
    machineId,
    modulos: licenca.modulos || []
  });
}