<?php
require_once __DIR__ . '/includes/header.php';
?>

<!-- Reused body content from original index.html -->
<header class="header" id="header">
  <div class="container">
    <div class="logo">
      <img src="assets/logo.png" alt="ZapMix" class="logo-img">
      <span>ZapMix</span>
    </div>

    <button class="menu-toggle" id="menuToggle" aria-label="Menu">
      <span></span>
      <span></span>
      <span></span>
    </button>

    <nav class="nav" id="nav">
      <a href="#home" class="nav-link">Início</a>
      <a href="#features" class="nav-link">Recursos</a>
      <a href="#telas" class="nav-link">Telas</a>
      <a href="#como-funciona" class="nav-link">Como funciona</a>
      <a href="#download" class="btn-download-header">📥 Download</a>
    </nav>
  </div>
</header>

<div class="menu-overlay" id="menuOverlay"></div>

<main>
  <!-- (rest of the original content preserved) -->
<?php
// To keep this example concise the rest of the original HTML sections are left in index.html
// If you want the full body moved into this file, merge the original index.html body here.
// For now include original index.html body fragment to preserve full layout.
readfile(__DIR__ . '/index.html.fragment');
?>

</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>