<?php
// Simple admin UI to manage messages
session_start();
require_once __DIR__ . '/config.php';
if (empty($_SESSION['zapmix_admin'])){ header('Location: /admin.php'); exit; }
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ZapMix — Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="max-w-5xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Painel de Controle — Mensagens</h1>
      <div>
        <a href="?logout=1" class="px-3 py-2 bg-red-500 text-white rounded">Logout</a>
      </div>
    </div>

    <div id="alert" class="mb-4"></div>

    <div class="mb-6">
      <h2 class="font-semibold mb-2">Mensagens pendentes</h2>
      <div id="pending" class="space-y-3"></div>
    </div>

    <div>
      <h2 class="font-semibold mb-2">Mensagens aprovadas</h2>
      <div id="approved" class="space-y-3"></div>
    </div>
  </div>

<script>
async function loadMessages(){
  const res = await fetch('/api/messages.php');
  const items = await res.json();
  const pend = document.getElementById('pending');
  const appr = document.getElementById('approved');
  pend.innerHTML = '';
  appr.innerHTML = '';
  items.sort((a,b)=> new Date(b.created_at) - new Date(a.created_at));
  items.forEach(it=>{
    const el = document.createElement('div'); el.className='p-3 bg-white rounded shadow';
    el.innerHTML = `<div class="flex justify-between"><div><strong>${escapeHtml(it.name)}</strong> <span class="text-xs text-gray-500">${it.created_at}</span><div class="mt-2">${escapeHtml(it.text)}</div></div><div class="flex flex-col gap-2 ml-4"><button class="approve px-3 py-1 bg-green-500 text-white rounded" data-id="${it.id}">Aprovar</button><button class="delete px-3 py-1 bg-red-500 text-white rounded" data-id="${it.id}">Excluir</button></div></div>`;
    if(it.approved) appr.appendChild(el); else pend.appendChild(el);
  });
  document.querySelectorAll('.approve').forEach(b=>b.addEventListener('click', async e=>{
    const id=e.target.dataset.id; await postAction('approve',id); loadMessages();
  }));
  document.querySelectorAll('.delete').forEach(b=>b.addEventListener('click', async e=>{
    const id=e.target.dataset.id; if(!confirm('Excluir mensagem?')) return; await postAction('delete',id); loadMessages();
  }));
}

function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[c]); }
async function postAction(action,id){
  const fd = new FormData(); fd.append('action', action); if(id) fd.append('id', id);
  await fetch('/api/messages.php', { method: 'POST', body: fd });
}

loadMessages();
setInterval(loadMessages, 5000);
</script>
</body>
</html>