<?php
// index.php - ZapMix Homepage with TailAdmin styling
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZapMix — Solução Inteligente para TV</title>
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/tailwind.css">
    <style>
        body { font-family: 'Inter', -apple-system, system-ui, sans-serif; }
        .hero-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }
        .feature-card { transition: all 0.3s ease; }
        .feature-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(144, 209, 5, 0.1); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header/Navigation -->
    <header class="sticky top-0 z-50 bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-2">
                    <img src="/assets/logo.png" alt="ZapMix" class="w-8 h-8" onerror="this.style.display='none'">
                    <span class="text-xl font-bold text-gray-900">ZapMix</span>
                </div>
                
                <nav class="hidden md:flex gap-8">
                    <a href="#recursos" class="text-gray-700 hover:text-blue-600">Recursos</a>
                    <a href="#telas" class="text-gray-700 hover:text-blue-600">Telas</a>
                    <a href="#funciona" class="text-gray-700 hover:text-blue-600">Como funciona</a>
                    <a href="#download" class="text-gray-700 hover:text-blue-600">FAQ</a>
                </nav>
                
                <div class="flex gap-3">
                    <a href="/admin.php" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Admin</a>
                    <a href="#download" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">Download</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero-gradient text-white py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 bg-blue-500/20 px-4 py-2 rounded-full mb-6">
                            <i class="fas fa-bolt text-blue-400"></i>
                            <span class="text-sm text-blue-300">Controle ao vivo para produções de TV</span>
                        </div>
                        
                        <h1 class="text-5xl font-bold mb-6">
                            Leve o WhatsApp para sua <span class="text-blue-400">produção ao vivo</span>
                        </h1>
                        
                        <p class="text-gray-300 text-lg mb-8 leading-relaxed">
                            O ZapMix transforma mensagens, mídias, enquetes e sorteios do WhatsApp em telas profissionais para vMix e OBS, com operação simples para sua equipe técnica.
                        </p>
                        
                        <div class="flex gap-4">
                            <a href="#download" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                <i class="fas fa-download"></i> Baixar ZapMix
                            </a>
                            <a href="#telas" class="inline-flex items-center gap-2 px-6 py-3 border border-white text-white rounded-lg hover:bg-white/10">
                                <i class="fas fa-desktop"></i> Ver telas
                            </a>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-6 mt-12">
                            <div>
                                <div class="text-3xl font-bold">500+</div>
                                <div class="text-gray-400">Downloads</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold">4.9★</div>
                                <div class="text-gray-400">Satisfação</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold">100+</div>
                                <div class="text-gray-400">Produtores</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl p-8 h-96">
                        <img src="/assets/screenshot-dashboard.png" alt="Dashboard" class="w-full h-full object-cover rounded-lg" onerror="this.parentElement.innerHTML='<div class=\"flex items-center justify-center h-full\"><i class=\"fas fa-desktop text-white text-6xl opacity-20\"></i></div>'">
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="recursos" class="py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Recursos Exclusivos</h2>
                    <p class="text-xl text-gray-600">Tudo que você precisa para transformar participação do público em conteúdo de tela</p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature Cards -->
                    <div class="feature-card bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-comments text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">WhatsApp Integrado</h3>
                        <p class="text-gray-600">Receba mensagens, fotos, vídeos e áudios em tempo real diretamente no seu fluxo de produção.</p>
                    </div>
                    
                    <div class="feature-card bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-poll text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Enquete Interativa</h3>
                        <p class="text-gray-600">Crie enquetes personalizadas com até 6 opções, palavras-chave e resultado animado em tempo real.</p>
                    </div>
                    
                    <div class="feature-card bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-gift text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Sorteio ao Vivo</h3>
                        <p class="text-gray-600">Realize sorteios com participantes do WhatsApp e exiba o resultado em tela pronta para transmissão.</p>
                    </div>
                    
                    <div class="feature-card bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-layer-group text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">vMix & OBS</h3>
                        <p class="text-gray-600">Telas otimizadas para Web Browser do vMix e navegador do OBS, com leitura limpa para transmissão.</p>
                    </div>
                    
                    <div class="feature-card bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-images text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Suporte a Mídias</h3>
                        <p class="text-gray-600">Organize vídeos, áudios e imagens enviados pelo WhatsApp sem improviso na transmissão ao vivo.</p>
                    </div>
                    
                    <div class="feature-card bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                            <i class="fas fa-qrcode text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Conexão Instantânea</h3>
                        <p class="text-gray-600">Conecte com QR Code em segundos, sem configuração complexa para operação do estúdio.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Telas Section -->
        <section id="telas" class="py-20 bg-gray-900 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-4xl font-bold mb-16 text-center">Telas Otimizadas</h2>
                
                <div class="grid md:grid-cols-2 gap-12">
                    <div class="bg-gray-800 p-8 rounded-2xl">
                        <div class="flex items-center gap-3 mb-4">
                            <i class="fas fa-monitor text-blue-400 text-2xl"></i>
                            <h3 class="text-2xl font-bold">Dashboard</h3>
                        </div>
                        <p class="text-gray-400 mb-4">Acompanhe conexão, mensagens, enquetes e mídias em uma central simples.</p>
                        <div class="bg-gray-700 h-40 rounded-lg flex items-center justify-center">
                            <span class="text-gray-500">/dashboard</span>
                        </div>
                    </div>
                    
                    <div class="bg-gray-800 p-8 rounded-2xl">
                        <div class="flex items-center gap-3 mb-4">
                            <i class="fas fa-chart-bar text-blue-400 text-2xl"></i>
                            <h3 class="text-2xl font-bold">Enquete</h3>
                        </div>
                        <p class="text-gray-400 mb-4">Exibe opções, votos e barras de porcentagem com atualização em tempo real.</p>
                        <div class="bg-gray-700 h-40 rounded-lg flex items-center justify-center">
                            <span class="text-gray-500">/exibidor-enquete</span>
                        </div>
                    </div>
                    
                    <div class="bg-gray-800 p-8 rounded-2xl">
                        <div class="flex items-center gap-3 mb-4">
                            <i class="fas fa-gift text-blue-400 text-2xl"></i>
                            <h3 class="text-2xl font-bold">Sorteio</h3>
                        </div>
                        <p class="text-gray-400 mb-4">Configure participantes e exiba resultado com visual limpo para broadcast.</p>
                        <div class="bg-gray-700 h-40 rounded-lg flex items-center justify-center">
                            <span class="text-gray-500">/exibidor-sorteio</span>
                        </div>
                    </div>
                    
                    <div class="bg-gray-800 p-8 rounded-2xl">
                        <div class="flex items-center gap-3 mb-4">
                            <i class="fas fa-comment-dots text-blue-400 text-2xl"></i>
                            <h3 class="text-2xl font-bold">Mensagens</h3>
                        </div>
                        <p class="text-gray-400 mb-4">Mostra mensagem, nome, foto e mídia em destaque com transições suaves.</p>
                        <div class="bg-gray-700 h-40 rounded-lg flex items-center justify-center">
                            <span class="text-gray-500">/exibidor</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Como Funciona -->
        <section id="funciona" class="py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-4xl font-bold text-center text-gray-900 mb-16">Como Funciona</h2>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                            <span class="text-2xl font-bold text-blue-600">1</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Baixe e Instale</h3>
                        <p class="text-gray-600">Baixe o instalador e execute no seu computador em menos de 1 minuto. Sem dependências.</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                            <span class="text-2xl font-bold text-blue-600">2</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Conecte o WhatsApp</h3>
                        <p class="text-gray-600">Escaneie o QR Code com seu WhatsApp e esteja online instantaneamente.</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                            <span class="text-2xl font-bold text-blue-600">3</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Integre ao vMix/OBS</h3>
                        <p class="text-gray-600">Adicione as URLs no Web Browser do vMix ou navegador do OBS. Simples!</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-4xl font-bold text-center text-gray-900 mb-12">Perguntas Frequentes</h2>
                
                <div class="max-w-3xl mx-auto space-y-4">
                    <details class="bg-white p-6 rounded-lg shadow-sm cursor-pointer">
                        <summary class="font-bold text-gray-900 flex justify-between items-center">
                            Precisa instalar dependências?
                            <i class="fas fa-chevron-down"></i>
                        </summary>
                        <p class="text-gray-600 mt-4">Não! O ZapMix é um executável autossuficiente para Windows. Basta baixar e executar.</p>
                    </details>
                    
                    <details class="bg-white p-6 rounded-lg shadow-sm cursor-pointer">
                        <summary class="font-bold text-gray-900 flex justify-between items-center">
                            Funciona com vMix e OBS?
                            <i class="fas fa-chevron-down"></i>
                        </summary>
                        <p class="text-gray-600 mt-4">Sim! Use as telas pelo Web Browser do vMix ou pela fonte Navegador do OBS.</p>
                    </details>
                    
                    <details class="bg-white p-6 rounded-lg shadow-sm cursor-pointer">
                        <summary class="font-bold text-gray-900 flex justify-between items-center">
                            Por que usar via navegador?
                            <i class="fas fa-chevron-down"></i>
                        </summary>
                        <p class="text-gray-600 mt-4">Mantém a integração mais simples, leve e estável. O WhatsApp Web usa Chromium.</p>
                    </details>
                    
                    <details class="bg-white p-6 rounded-lg shadow-sm cursor-pointer">
                        <summary class="font-bold text-gray-900 flex justify-between items-center">
                            Precisa de internet?
                            <i class="fas fa-chevron-down"></i>
                        </summary>
                        <p class="text-gray-600 mt-4">Sim, para conectar o WhatsApp e receber mensagens em tempo real.</p>
                    </details>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section id="download" class="py-20 hero-gradient text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-4xl font-bold mb-4">Pronto para colocar o público no ar?</h2>
                <p class="text-xl text-gray-300 mb-8">Baixe o ZapMix e comece em poucos minutos</p>
                
                <a href="https://example.com/download" class="inline-flex items-center gap-2 px-8 py-4 bg-blue-500 text-white rounded-lg hover:bg-blue-600 font-bold text-lg">
                    <i class="fas fa-download"></i> Baixar ZapMix Agora
                </a>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="text-white font-bold mb-4">ZapMix</div>
                    <p class="text-sm">Solução profissional para integração do WhatsApp para TV.</p>
                </div>
                <div>
                    <div class="text-white font-bold mb-4">Navegação</div>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#recursos" class="hover:text-white">Recursos</a></li>
                        <li><a href="#funciona" class="hover:text-white">Como funciona</a></li>
                        <li><a href="#faq" class="hover:text-white">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <div class="text-white font-bold mb-4">Suporte</div>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/admin.php" class="hover:text-white">Admin</a></li>
                        <li><a href="mailto:support@zapmix.com" class="hover:text-white">Email</a></li>
                    </ul>
                </div>
                <div>
                    <div class="text-white font-bold mb-4">Contato</div>
                    <p class="text-sm">📱 (83) 98628-0769</p>
                    <p class="text-sm">📧 andersoncgpb1@gmail.com</p>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 text-center text-sm">
                <p>&copy; 2026 ZapMix - Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>
