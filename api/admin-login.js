import crypto from "crypto";

function criarToken(user) {
  const payload = {
    user,
    exp: Date.now() + 1000 * 60 * 60 * 8
  };

  const base64 = Buffer.from(JSON.stringify(payload)).toString("base64url");

  const assinatura = crypto
    .createHmac("sha256", process.env.ADMIN_SECRET)
    .update(base64)
    .digest("base64url");

  return `${base64}.${assinatura}`;
}

export default function handler(req, res) {
  if (req.method !== "POST") {
    return res.status(405).json({ ok: false, erro: "Método não permitido" });
  }

  const { usuario, senha } = req.body || {};

  if (
    usuario !== process.env.ADMIN_USER ||
    senha !== process.env.ADMIN_PASSWORD
  ) {
    return res.status(401).json({
      ok: false,
      erro: "Usuário ou senha inválidos"
    });
  }

  return res.json({
    ok: true,
    token: criarToken(usuario)
  });
}