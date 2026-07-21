Deploy instructions (complete PHP site)

1) Ensure your hosting supports PHP 7.4+ and has write permissions for the data/ folder.
2) If you want Tailwind compiled CSS on the server, upload assets/tailwind.css generated locally. If not present, site will use CDN fallback.
3) Files to upload to public_html (example):
   - index.php, admin.php, admin_ui.php, exibidor.php, exibidor-enquete.php
   - includes/ (header.php, footer.php)
   - api/ (messages.php)
   - config.php
   - assets/ (images, js) - include existing assets/ folder
   - data/ (messages.json, poll.json)
4) Set permissions: data/ must be writable by the webserver (chmod 755 or 775 depending on provider).
5) Change default admin password: edit config.php and generate a new password hash with PHP's password_hash() function.

Generating tailwind.css locally:
- On your development machine with Node.js installed, run:
  npm install --no-audit --no-fund
  npm run build:css
- Copy ./assets/tailwind.css to the server's assets/ folder.

ZIP creation (local): run provided build-and-zip.ps1 or zip the project folder excluding node_modules.

If you want, I can now generate a ZIP of the repository as-is (note: tailwind.css may be missing).