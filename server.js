const express = require("express");
const http = require("http");
const cors = require("cors");
const QRCode = require("qrcode");
const axios = require("axios");
const fs = require("fs");
const path = require("path");
const { Server } = require("socket.io");
const { Client, LocalAuth } = require("whatsapp-web.js");
const multer = require("multer");
const crypto = require("crypto");

const app = express();
const serverHttp = http.createServer(app);
const io = new Server(serverHttp);

const PORT = process.env.PORT || 3000;
const PUBLIC_BASE_URL = process.env.PUBLIC_BASE_URL || `http://localhost:${PORT}`;
const DEFAULT_PHOTO_URL = `${PUBLIC_BASE_URL}/fotos/default.png`;

app.use(cors());
app.use(express.json({ limit: "100mb" }));
app.use(express.urlencoded({ limit: "100mb", extended: true }));
app.use(express.static("public"));

// Criar pastas
const fotosDir = path.join(__dirname, "public", "fotos");
const imagensDir = path.join(__dirname, "public", "imagens");
const videosDir = path.join(__dirname, "public", "videos");
const audiosDir = path.join(__dirname, "public", "audios");
const uploadsDir = path.join(__dirname, "public", "uploads");

if (!fs.existsSync(fotosDir)) fs.mkdirSync(fotosDir, { recursive: true });
if (!fs.existsSync(imagensDir)) fs.mkdirSync(imagensDir, { recursive: true });
if (!fs.existsSync(videosDir)) fs.mkdirSync(videosDir, { recursive: true });
if (!fs.existsSync(audiosDir)) fs.mkdirSync(audiosDir, { recursive: true });
if (!fs.existsSync(uploadsDir)) fs.mkdirSync(uploadsDir, { recursive: true });

let qrCodeDataUrl = null;
let whatsappStatus = "iniciando";
let pendingMessages = [];
let approvedMessages = [];
let currentMessage = null;

// ============ ENQUETE ============
let enqueteAtiva = true;
let perguntaEnquete = "O que você mais gosta?";
let opcoesEnquete = [
  { id: "coracao", nome: "Coracao", cor: "#ef4444", votos: 0, palavrasChave: ["coração", "coracao", "cardio", "heart"] },
  { id: "pele", nome: "Pele", cor: "#f59e0b", votos: 0, palavrasChave: ["pele", "derma", "skin"] },
  { id: "cerebro", nome: "Cerebro", cor: "#3b82f6", votos: 0, palavrasChave: ["cérebro", "cerebro", "neuro", "mente"] }
];

function getTotalVotos() {
  return opcoesEnquete.reduce((total, op) => total + op.votos, 0);
}

function getPorcentagem(opcaoId) {
  const total = getTotalVotos();
  if (total === 0) return 0;
  const opcao = opcoesEnquete.find(op => op.id === opcaoId);
  return Math.round((opcao.votos / total) * 100);
}

function getVencedor() {
  if (getTotalVotos() === 0) return null;
  return [...opcoesEnquete].sort((a, b) => b.votos - a.votos)[0];
}

function detectarVoto(mensagem) {
  if (!mensagem) return null;
  const msgLower = mensagem.toLowerCase().trim();
  for (const opcao of opcoesEnquete) {
    for (const palavra of opcao.palavrasChave) {
      if (msgLower.includes(palavra.toLowerCase())) return opcao.id;
    }
  }
  return null;
}

function contabilizarVoto(opcaoId) {
  const opcao = opcoesEnquete.find(op => op.id === opcaoId);
  if (opcao && enqueteAtiva) {
    opcao.votos++;
    emit();
    return true;
  }
  return false;
}

function resetarVotos() {
  opcoesEnquete.forEach(op => op.votos = 0);
  emit();
}

// ============ FUNÇÕES AUXILIARES ============
function emptyCurrent() {
  return {
    indice: 0,
    id: "",
    nome: "",
    mensagem: "",
    horario: "",
    foto: DEFAULT_PHOTO_URL,
    imagemUrl: null,
    videoUrl: null,
    audioUrl: null,
    status: "vazio"
  };
}

function cleanText(value) {
  return String(value || "").trim();
}

function nowBR() {
  return new Date().toLocaleString("pt-BR", { timeZone: "America/Fortaleza", hour12: false });
}

function normalizePhone(from) {
  return String(from || "").replace("@c.us", "").replace("@g.us", "").replace(/[^0-9]/g, "");
}

function toDatasourceItem(item, index = 0) {
  if (!item) return emptyCurrent();
  return {
    indice: index + 1,
    id: item.id || "",
    nome: item.nome || "",
    mensagem: item.mensagem || "",
    horario: item.horario || "",
    foto: item.foto || DEFAULT_PHOTO_URL,
    imagemUrl: item.imagemUrl || null,
    videoUrl: item.videoUrl || null,
    audioUrl: item.audioUrl || null,
    status: item.status || "aprovada"
  };
}

function state() {
  return {
    whatsappStatus,
    qrCodeDataUrl,
    pendingMessages,
    approvedMessages,
    currentMessage: currentMessage || emptyCurrent(),
    enquete: {
      ativa: enqueteAtiva,
      pergunta: perguntaEnquete,
      opcoes: opcoesEnquete.map(op => ({
        ...op,
        porcentagem: getPorcentagem(op.id)
      })),
      totalVotos: getTotalVotos(),
      vencedor: getVencedor()
    }
  };
}

function emit() {
  io.emit("messages:update", state());
}

async function baixarFotoPerfil(url, telefone) {
  try {
    if (!url || !telefone) return DEFAULT_PHOTO_URL;
    const filename = `${telefone}.jpg`;
    const filepath = path.join(fotosDir, filename);
    const response = await axios({ url, method: "GET", responseType: "arraybuffer", timeout: 12000 });
    fs.writeFileSync(filepath, response.data);
    return `${PUBLIC_BASE_URL}/fotos/${filename}`;
  } catch (error) {
    return DEFAULT_PHOTO_URL;
  }
}

async function salvarMidia(media, messageId, tipo) {
  try {
    let ext, dir, urlPath;
    if (tipo === "image") {
      ext = media.mimetype.split("/")[1] || "jpg";
      dir = imagensDir;
      urlPath = "/imagens";
    } else if (tipo === "video") {
      ext = media.mimetype.split("/")[1] || "mp4";
      dir = videosDir;
      urlPath = "/videos";
    } else if (tipo === "audio") {
      ext = media.mimetype.split("/")[1] || "ogg";
      dir = audiosDir;
      urlPath = "/audios";
    } else {
      return null;
    }
    const filename = `${messageId}_${Date.now()}.${ext}`;
    const filepath = path.join(dir, filename);
    const buffer = Buffer.from(media.data, "base64");
    fs.writeFileSync(filepath, buffer);
    return `${PUBLIC_BASE_URL}${urlPath}/${filename}`;
  } catch (error) {
    return null;
  }
}

function addPending(data) {
  const text = cleanText(data.mensagem);
  if (!text && !data.imagemUrl && !data.videoUrl && !data.audioUrl) return null;

  let mensagemFinal = text;
  if (text && data.origem !== "manual") {
    const voto = detectarVoto(text);
    if (voto && enqueteAtiva) {
      const opcao = opcoesEnquete.find(op => op.id === voto);
      if (opcao) {
        contabilizarVoto(voto);
        mensagemFinal = `VOTO: ${opcao.nome} - ${text}`;
      }
    }
  }

  const item = {
    id: Date.now().toString() + Math.random().toString(16).slice(2),
    nome: cleanText(data.nome || "Participante"),
    mensagem: mensagemFinal,
    foto: data.foto || DEFAULT_PHOTO_URL,
    imagemUrl: data.imagemUrl || null,
    videoUrl: data.videoUrl || null,
    audioUrl: data.audioUrl || null,
    origem: data.origem || "whatsapp",
    status: "pendente",
    createdAt: new Date().toISOString(),
    horario: nowBR()
  };

  pendingMessages.unshift(item);
  pendingMessages = pendingMessages.slice(0, 200);
  emit();
  return item;
}

// ============ FUNÇÃO PARA ENCONTRAR CHROME ============
function encontrarChrome() {
  const caminhos = [
    'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
    'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
    process.env.LOCALAPPDATA + '\\Google\\Chrome\\Application\\chrome.exe',
    process.env.PROGRAMFILES + '\\Google\\Chrome\\Application\\chrome.exe',
    process.env['PROGRAMFILES(X86)'] + '\\Google\\Chrome\\Application\\chrome.exe'
  ];
  
  for (const caminho of caminhos) {
    if (fs.existsSync(caminho)) {
      console.log(`✅ Usando navegador: ${caminho}`);
      return caminho;
    }
  }
  
  console.log('⚠️ Chrome/Edge não encontrado. O WhatsApp pode não funcionar.');
  return null;
}

// ============ WHATSAPP CLIENT ============
const chromePath = encontrarChrome();

const client = new Client({
  authStrategy: new LocalAuth({ clientId: "zapmix" }),
  puppeteer: {
    headless: true,
    executablePath: chromePath,
    args: ["--no-sandbox", "--disable-setuid-sandbox", "--disable-dev-shm-usage", "--disable-gpu"]
  }
});

client.on("qr", async (qr) => {
  whatsappStatus = "aguardando_qr";
  qrCodeDataUrl = await QRCode.toDataURL(qr);
  emit();
  console.log("📱 QR Code gerado!");
});

client.on("ready", () => {
  whatsappStatus = "conectado";
  qrCodeDataUrl = null;
  emit();
  console.log("✅ WhatsApp conectado!");
});

client.on("authenticated", () => {
  console.log("🔐 WhatsApp autenticado");
});

client.on("auth_failure", (msg) => {
  console.error("❌ Falha de autenticação:", msg);
  whatsappStatus = "falha_autenticacao";
  emit();
});

client.on("disconnected", (reason) => {
  console.log("⚠️ WhatsApp desconectado:", reason);
  whatsappStatus = "desconectado";
  emit();
});

client.on("message", async (msg) => {
  try {
    if (msg.from === "status@broadcast") return;
    
    const contact = await msg.getContact();
    const nome = contact.pushname || contact.name || contact.number || "Participante";
    const telefone = normalizePhone(msg.from);
    let mensagemTexto = msg.body || "";
    let foto = DEFAULT_PHOTO_URL;
    let imagemUrl = null;
    let videoUrl = null;
    let audioUrl = null;
    let mediaType = null;

    try {
      const profilePicUrl = await contact.getProfilePicUrl();
      if (profilePicUrl) foto = await baixarFotoPerfil(profilePicUrl, telefone);
    } catch (error) {}

    if (msg.hasMedia) {
      try {
        const media = await msg.downloadMedia();
        if (media) {
          if (media.mimetype.startsWith("image/")) {
            imagemUrl = await salvarMidia(media, msg.id.id, "image");
            mediaType = "image";
            if (!mensagemTexto) mensagemTexto = "Imagem enviada";
          } else if (media.mimetype.startsWith("video/")) {
            videoUrl = await salvarMidia(media, msg.id.id, "video");
            mediaType = "video";
            if (!mensagemTexto) mensagemTexto = "Video enviado";
          } else if (media.mimetype.startsWith("audio/")) {
            audioUrl = await salvarMidia(media, msg.id.id, "audio");
            mediaType = "audio";
            if (!mensagemTexto) mensagemTexto = "Audio enviado";
          }
        }
      } catch (err) {
        console.log("Erro ao baixar mídia:", err.message);
      }
    }

    if (!mensagemTexto && !imagemUrl && !videoUrl && !audioUrl) return;

    addPending({
      nome, telefone, mensagem: mensagemTexto, foto,
      imagemUrl, videoUrl, audioUrl, mediaType,
      origem: msg.from.endsWith("@g.us") ? "grupo" : "whatsapp"
    });
  } catch (error) {
    console.error("Erro ao processar mensagem:", error);
  }
});

client.initialize();

// ============ ROTAS PÚBLICAS (NÃO PRECISAM DE LICENÇA) ============
app.get("/api/state", (req, res) => res.json(state()));

app.post("/api/whatsapp/logout", async (req, res) => {
  try {
    await client.logout();
    whatsappStatus = "desconectado";
    qrCodeDataUrl = null;
    pendingMessages = [];
    approvedMessages = [];
    currentMessage = null;
    emit();
    console.log("📱 WhatsApp desconectado pelo usuário");
    res.json({ ok: true, message: "WhatsApp desconectado" });
  } catch (error) {
    console.error("Erro ao desconectar:", error);
    res.status(500).json({ error: "Erro ao desconectar" });
  }
});

// Rotas da Enquete
app.get("/api/enquete", (req, res) => {
  res.json({
    ativa: enqueteAtiva,
    pergunta: perguntaEnquete,
    opcoes: opcoesEnquete.map(op => ({
      id: op.id,
      nome: op.nome,
      cor: op.cor,
      votos: op.votos,
      porcentagem: getPorcentagem(op.id)
    })),
    totalVotos: getTotalVotos(),
    vencedor: getVencedor()
  });
});

app.get("/api/enquete/pergunta", (req, res) => {
  res.json({ pergunta: perguntaEnquete });
});

app.post("/api/enquete/pergunta", (req, res) => {
  const { pergunta } = req.body;
  if (pergunta) {
    perguntaEnquete = pergunta;
    emit();
    res.json({ ok: true });
  } else {
    res.status(400).json({ error: "Pergunta inválida" });
  }
});

app.post("/api/enquete/resetar", (req, res) => {
  resetarVotos();
  res.json({ ok: true });
});

app.post("/api/enquete/toggle", (req, res) => {
  enqueteAtiva = !enqueteAtiva;
  emit();
  res.json({ ativa: enqueteAtiva });
});

app.post("/api/enquete/opcoes", (req, res) => {
  const { opcoes } = req.body;
  if (opcoes && Array.isArray(opcoes) && opcoes.length >= 2) {
    opcoesEnquete = opcoes.map(op => ({
      id: op.id,
      nome: op.nome,
      cor: op.cor,
      votos: 0,
      palavrasChave: op.palavrasChave || [op.id]
    }));
    resetarVotos();
    emit();
    res.json({ ok: true });
  } else {
    res.status(400).json({ error: "Mínimo de 2 opções" });
  }
});

app.get("/datasource", (req, res) => res.json([toDatasourceItem(currentMessage, 0)]));
app.get("/datasource/approved", (req, res) => {
  const lista = approvedMessages.map((item, index) => toDatasourceItem(item, index));
  res.json(lista.length === 0 ? [emptyCurrent()] : lista);
});

app.post("/api/manual", (req, res) => {
  const item = addPending({
    nome: req.body.nome,
    mensagem: req.body.mensagem,
    foto: req.body.foto || DEFAULT_PHOTO_URL,
    origem: "manual"
  });
  if (!item) return res.status(400).json({ error: "Mensagem vazia." });
  res.json({ ok: true, item });
});

app.post("/api/messages/:id/update", (req, res) => {
  const item = pendingMessages.find(m => m.id === req.params.id) ||
               approvedMessages.find(m => m.id === req.params.id) ||
               (currentMessage && currentMessage.id === req.params.id ? currentMessage : null);
  if (!item) return res.status(404).json({ error: "Mensagem não encontrada." });
  if (req.body.nome !== undefined) item.nome = cleanText(req.body.nome);
  if (req.body.mensagem !== undefined) item.mensagem = cleanText(req.body.mensagem);
  if (req.body.foto !== undefined) item.foto = cleanText(req.body.foto);
  if (req.body.imagemUrl !== undefined) item.imagemUrl = req.body.imagemUrl;
  if (req.body.videoUrl !== undefined) item.videoUrl = req.body.videoUrl;
  if (req.body.audioUrl !== undefined) item.audioUrl = req.body.audioUrl;
  if (currentMessage && currentMessage.id === item.id) currentMessage = { ...item, status: "no_ar" };
  emit();
  res.json({ ok: true, item });
});

app.post("/api/messages/:id/approve", (req, res) => {
  const index = pendingMessages.findIndex(m => m.id === req.params.id);
  if (index === -1) return res.status(404).json({ error: "Mensagem não encontrada." });
  const [item] = pendingMessages.splice(index, 1);
  item.status = "aprovada";
  approvedMessages.unshift(item);
  emit();
  res.json({ ok: true, item });
});

app.post("/api/messages/:id/reject", (req, res) => {
  pendingMessages = pendingMessages.filter(m => m.id !== req.params.id);
  emit();
  res.json({ ok: true });
});

app.post("/api/messages/:id/onair", (req, res) => {
  const item = approvedMessages.find(m => m.id === req.params.id) || pendingMessages.find(m => m.id === req.params.id);
  if (!item) return res.status(404).json({ error: "Mensagem não encontrada." });
  currentMessage = { ...item, status: "no_ar" };
  emit();
  res.json({ ok: true, currentMessage });
});

app.post("/api/clear", (req, res) => {
  currentMessage = null;
  emit();
  res.json({ ok: true });
});

function limparTodasMidias() {
  let total = 0;
  const pastas = [fotosDir, imagensDir, videosDir, audiosDir, uploadsDir];
  
  for (const pasta of pastas) {
    if (fs.existsSync(pasta)) {
      const arquivos = fs.readdirSync(pasta);
      for (const arquivo of arquivos) {
        if (arquivo.toLowerCase() !== 'default.png') {
          try {
            fs.unlinkSync(path.join(pasta, arquivo));
            total++;
          } catch(e) {}
        }
      }
    }
  }
  return total;
}

app.post("/api/maintenance/clear-all", (req, res) => {
  const pendingCount = pendingMessages.length;
  const approvedCount = approvedMessages.length;
  
  pendingMessages = [];
  approvedMessages = [];
  currentMessage = null;
  
  const midiasDeletadas = limparTodasMidias();
  
  emit();
  
  res.json({ 
    ok: true, 
    pendingDeleted: pendingCount, 
    approvedDeleted: approvedCount,
    photosDeleted: midiasDeletadas
  });
});

// ============ ROTAS PARA BACKGROUND ============
const storage = multer.diskStorage({
  destination: (req, file, cb) => { cb(null, uploadsDir); },
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname);
    cb(null, `background_${Date.now()}${ext}`);
  }
});
const uploadMidia = multer({ storage, limits: { fileSize: 5 * 1024 * 1024 } });

let backgroundAtual = { type: "default", imageUrl: null };

app.post("/api/background/upload", uploadMidia.single('background'), (req, res) => {
  if (!req.file) return res.status(400).json({ error: "Nenhum arquivo enviado" });
  res.json({ ok: true, url: `/uploads/${req.file.filename}` });
});

app.post("/api/background/set", (req, res) => {
  const { background, imageUrl } = req.body;
  if (background) {
    backgroundAtual = { type: background, imageUrl: imageUrl || null };
    res.json({ ok: true, background });
  } else {
    res.status(400).json({ error: "Background inválido" });
  }
});

app.get("/api/background/get", (req, res) => {
  res.json(backgroundAtual);
});

// ============ SISTEMA DE LICENÇA ============
const licencasFile = path.join(__dirname, 'licencas.json');
let licencas = {};

function carregarLicencas() {
  try {
    if (fs.existsSync(licencasFile)) {
      const data = fs.readFileSync(licencasFile, 'utf8');
      licencas = JSON.parse(data);
      console.log(`📋 ${Object.keys(licencas).length} licenças carregadas`);
    } else {
      licencas = {
        "TEST-1234-ABCD-5678": {
          chave: "TEST-1234-ABCD-5678",
          titular: "Usuário Teste",
          tipo: "premium",
          maxComputadores: 3,
          computadores: [],
          ativa: true,
          dataCriacao: new Date().toISOString(),
          expiracao: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toISOString()
        }
      };
      fs.writeFileSync(licencasFile, JSON.stringify(licencas, null, 2));
      console.log("✅ Licença de teste criada: TEST-1234-ABCD-5678");
    }
  } catch (error) {
    console.log("Erro ao carregar licenças:", error);
  }
}

function salvarLicencas() {
  fs.writeFileSync(licencasFile, JSON.stringify(licencas, null, 2));
}

function validarFormatoChave(chave) {
  return /^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/.test(chave);
}

function gerarChave() {
  const random = crypto.randomBytes(16).toString('hex').toUpperCase();
  return `${random.substring(0,4)}-${random.substring(4,8)}-${random.substring(8,12)}-${random.substring(12,16)}`;
}

// Rotas de licença (públicas)
app.post("/api/licenca/validar", (req, res) => {
  const { chave, computadorId } = req.body;
  console.log(`🔍 Validando chave: ${chave}`);
  
  if (!chave || !validarFormatoChave(chave)) {
    return res.json({ valida: false, error: "Formato de chave inválido" });
  }
  
  const licenca = licencas[chave];
  
  if (!licenca) {
    console.log(`❌ Chave ${chave} NÃO encontrada`);
    return res.json({ valida: false, error: "Chave não encontrada" });
  }
  
  if (!licenca.ativa) {
    console.log(`❌ Chave ${chave} desativada`);
    return res.json({ valida: false, error: "Chave desativada" });
  }
  
  if (licenca.expiracao && new Date(licenca.expiracao) < new Date()) {
    console.log(`❌ Chave ${chave} expirada`);
    return res.json({ valida: false, error: "Licença expirada" });
  }
  
  if (licenca.computadores && licenca.computadores.length >= licenca.maxComputadores) {
    if (!licenca.computadores.includes(computadorId)) {
      return res.json({ valida: false, error: "Limite de computadores atingido" });
    }
  }
  
  if (computadorId && (!licenca.computadores || !licenca.computadores.includes(computadorId))) {
    if (!licenca.computadores) licenca.computadores = [];
    licenca.computadores.push(computadorId);
    licenca.ultimaAtivacao = new Date().toISOString();
    salvarLicencas();
  }
  
  console.log(`✅ Chave ${chave} válida!`);
  res.json({
    valida: true,
    tipo: licenca.tipo,
    titular: licenca.titular,
    expiracao: licenca.expiracao
  });
});

app.get("/api/licenca/status", (req, res) => {
  const licencaArquivo = path.join(__dirname, 'licenca_ativa.json');
  try {
    if (fs.existsSync(licencaArquivo)) {
      const data = fs.readFileSync(licencaArquivo, 'utf8');
      const licenca = JSON.parse(data);
      const licencaServidor = licencas[licenca.chave];
      
      if (licencaServidor && licencaServidor.ativa) {
        if (licencaServidor.expiracao && new Date(licencaServidor.expiracao) >= new Date()) {
          return res.json({ ativada: true, chave: licenca.chave, titular: licenca.titular });
        }
      }
      fs.unlinkSync(licencaArquivo);
    }
  } catch (error) {}
  res.json({ ativada: false });
});

app.post("/api/licenca/salvar", (req, res) => {
  const { chave, titular } = req.body;
  const licencaArquivo = path.join(__dirname, 'licenca_ativa.json');
  fs.writeFileSync(licencaArquivo, JSON.stringify({ chave, titular, dataAtivacao: new Date().toISOString() }, null, 2));
  res.json({ ok: true });
});

app.post("/api/licenca/remover", (req, res) => {
  const licencaArquivo = path.join(__dirname, 'licenca_ativa.json');
  try {
    if (fs.existsSync(licencaArquivo)) fs.unlinkSync(licencaArquivo);
    res.json({ ok: true });
  } catch (error) {
    res.status(500).json({ error: "Erro ao remover licença" });
  }
});

// Rota para admin gerar licença


// ============ FUNÇÃO PARA GERAR CHAVE ============
function gerarChave() {
  const random = crypto.randomBytes(16).toString('hex').toUpperCase();
  return `${random.substring(0,4)}-${random.substring(4,8)}-${random.substring(8,12)}-${random.substring(12,16)}`;
}

// ============ ROTA PARA GERAR LICENÇA ============
app.post("/api/gerar-licenca", (req, res) => {
  const { nome, email, codigo } = req.body;
  
  console.log(`📝 Requisição para gerar licença: ${nome} - ${email}`);
  
  if (!nome || !email) {
    return res.status(400).json({ erro: "Nome e e-mail são obrigatórios" });
  }
  
  const chave = gerarChave();
  const expiracao = new Date();
  expiracao.setDate(expiracao.getDate() + 365);
  
  licencas[chave] = {
    chave,
    titular: nome,
    email: email,
    tipo: "premium",
    maxComputadores: 1,
    computadores: [],
    ativa: true,
    dataCriacao: new Date().toISOString(),
    expiracao: expiracao.toISOString()
  };
  
  salvarLicencas();
  
  console.log(`✅ Licença gerada: ${chave} para ${nome}`);
  
  res.json({ 
    sucesso: true, 
    chave: chave, 
    titular: nome,
    expiracao: expiracao.toISOString()
  });
});

// ============ ROTA PARA GERAR LICENÇA (COM VERIFICAÇÃO DE E-MAIL ÚNICO) ============
app.post("/api/gerar-licenca", (req, res) => {
  const { nome, email, codigo } = req.body;
  
  console.log(`📝 Requisição para gerar licença: ${nome} - ${email}`);
  
  if (!nome || !email) {
    return res.status(400).json({ erro: "Nome e e-mail são obrigatórios" });
  }
  
  // ** VERIFICAR SE O E-MAIL JÁ POSSUI UMA LICENÇA **
  const emailExistente = Object.values(licencas).find(lic => lic.email === email);
  
  if (emailExistente) {
    console.log(`⚠️ E-mail ${email} já possui licença: ${emailExistente.chave}`);
    return res.status(400).json({ 
      erro: "Este e-mail já possui uma licença ativa!",
      chaveExistente: emailExistente.chave
    });
  }
  
  // Gerar nova chave
  const chave = gerarChave();
  const expiracao = new Date();
  expiracao.setDate(expiracao.getDate() + 365);
  
  licencas[chave] = {
    chave,
    titular: nome,
    email: email,
    tipo: "premium",
    maxComputadores: 1,
    computadores: [],
    ativa: true,
    dataCriacao: new Date().toISOString(),
    expiracao: expiracao.toISOString()
  };
  
  salvarLicencas();
  
  console.log(`✅ Licença gerada: ${chave} para ${nome} (${email})`);
  
  res.json({ 
    sucesso: true, 
    chave: chave, 
    titular: nome,
    expiracao: expiracao.toISOString()
  });
});

carregarLicencas();

// ============ MIDDLEWARE DE PROTEÇÃO ============
const rotasPublicas = [
  '/ativar.html',
  '/api/licenca/validar',
  '/api/licenca/salvar',
  '/api/licenca/status',
  '/api/licenca/remover',
  '/media/',
  '/fotos/',
  '/imagens/',
  '/videos/',
  '/audios/',
  '/uploads/',
  '/favicon.ico'
];

app.use((req, res, next) => {
  for (const rota of rotasPublicas) {
    if (req.path === rota || req.path.startsWith(rota)) {
      return next();
    }
  }
  
  const licencaArquivo = path.join(__dirname, 'licenca_ativa.json');
  
  if (!fs.existsSync(licencaArquivo)) {
    if (req.path.startsWith('/api/')) {
      return res.status(401).json({ error: "Licença não ativada", redirect: "/ativar.html" });
    }
    return res.redirect('/ativar.html');
  }
  
  try {
    const data = fs.readFileSync(licencaArquivo, 'utf8');
    const licenca = JSON.parse(data);
    const licencaServidor = licencas[licenca.chave];
    
    if (!licencaServidor || !licencaServidor.ativa) {
      fs.unlinkSync(licencaArquivo);
      if (req.path.startsWith('/api/')) {
        return res.status(401).json({ error: "Licença desativada", redirect: "/ativar.html" });
      }
      return res.redirect('/ativar.html');
    }
    
    if (licencaServidor.expiracao && new Date(licencaServidor.expiracao) < new Date()) {
      fs.unlinkSync(licencaArquivo);
      if (req.path.startsWith('/api/')) {
        return res.status(401).json({ error: "Licença expirada", redirect: "/ativar.html" });
      }
      return res.redirect('/ativar.html');
    }
  } catch (error) {
    if (req.path.startsWith('/api/')) {
      return res.status(401).json({ error: "Licença inválida", redirect: "/ativar.html" });
    }
    return res.redirect('/ativar.html');
  }
  
  next();
});

// ============ INICIAR SERVIDOR ============
serverHttp.listen(PORT, () => {
  console.log(`\n🚀 Servidor rodando em http://localhost:${PORT}`);
  console.log(`✅ WhatsApp + Enquete + Mídias`);
  console.log(`💡 Envie: coracao, pele, cerebro (case insensitive)`);
  
  if (!process.env.ELECTRON_RUN_AS_NODE) {
    setTimeout(() => {
      const { exec } = require('child_process');
      exec('start http://localhost:3000');
    }, 2000);
  }
});