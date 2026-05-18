export default function handler(req, res) {
  if (req.method !== "POST") {
    return res.status(405).json({ ok: false, error: "Método não permitido" });
  }

  const { chave, machineId } = req.body || {};

  const licencas = {
    "ZAPMIX-2026-TESTE": {
      cliente: "Cliente Teste",
      status: "ATIVA",
      validade: "2026-12-31",
      modulos: ["whatsapp", "ndi", "enquete"]
    }
  };

  if (!chave || !licencas[chave]) {
    return res.status(403).json({
      ok: false,
      status: "INVALIDA",
      error: "Licença inválida"
    });
  }

  const licenca = licencas[chave];

  if (licenca.status !== "ATIVA") {
    return res.status(403).json({
      ok: false,
      status: licenca.status
    });
  }

  const hoje = new Date();
  const validade = new Date(licenca.validade + "T23:59:59");

  if (hoje > validade) {
    return res.status(403).json({
      ok: false,
      status: "EXPIRADA"
    });
  }

  return res.json({
    ok: true,
    status: "ATIVA",
    cliente: licenca.cliente,
    validade: licenca.validade,
    machineId,
    modulos: licenca.modulos
  });
}