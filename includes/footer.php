<?php
// footer.php - closing body and common scripts
?>

<footer class="footer">
  <div class="container">
    <div class="footer-content">
      <div class="footer-logo">
        <img src="/assets/logo.png" alt="ZapMix" class="footer-logo-img">
        <p>Solução profissional para integração do WhatsApp para TV.</p>
      </div>
      <div class="footer-links">
        <h4>Navegação</h4>
        <a href="#home">Início</a>
        <a href="#features">Recursos</a>
        <a href="#como-funciona">Como funciona</a>
        <a href="#download">Download</a>
      </div>
      <div class="footer-contact">
        <h4>Contato</h4>
        <p><i class="fab fa-whatsapp"></i> (83) 98628-0769</p>
        <p><i class="far fa-envelope"></i> andersoncgpb1@gmail.com</p>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2026 ZapMix - Todos os direitos reservados.</p>
    </div>
  </div>
</footer>

<script>
// small helper scripts preserved from original
(function(){
  const menuToggle = document.getElementById('menuToggle');
  const nav = document.getElementById('nav');
  const menuOverlay = document.getElementById('menuOverlay');
  const navLinks = document.querySelectorAll('.nav-link');
  const backToTop = document.getElementById('backToTop');
  if(!menuToggle) return;
  function closeMenu(){ nav.classList.remove('active'); menuToggle.classList.remove('active'); menuOverlay.classList.remove('active'); document.body.style.overflow=''; }
  function openMenu(){ nav.classList.add('active'); menuToggle.classList.add('active'); menuOverlay.classList.add('active'); document.body.style.overflow='hidden'; }
  menuToggle.addEventListener('click', ()=> nav.classList.contains('active')?closeMenu():openMenu());
  if(menuOverlay) menuOverlay.addEventListener('click', closeMenu);
  navLinks.forEach(l=>l.addEventListener('click', closeMenu));
  const header = document.getElementById('header');
  window.addEventListener('scroll', ()=> header && header.classList.toggle('header-scrolled', window.scrollY>50) );
  const sections = document.querySelectorAll('section');
  window.addEventListener('scroll', ()=>{
    let current=''; sections.forEach(s=>{ if(scrollY>=s.offsetTop-200) current=s.getAttribute('id'); });
    navLinks.forEach(link=>{ link.classList.remove('active'); if(link.getAttribute('href')===`#${current}`) link.classList.add('active'); });
  });
})();
</script>

</body>
</html>