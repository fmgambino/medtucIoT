// assets/js/pwa.js

document.addEventListener('DOMContentLoaded', () => {
  let deferredPrompt = null;
  const btnInstall = document.getElementById('btnInstall');

  console.log('[PWA] pwa.js cargado, esperando beforeinstallprompt');

  // 1) Capturar beforeinstallprompt
  window.addEventListener('beforeinstallprompt', (e) => {
    console.log('[PWA] beforeinstallprompt disparado');
    e.preventDefault();
    deferredPrompt = e;

    // Mostrar el botón de instalación (si existe) y el SweetAlert
    showInstallPrompt();
    if (btnInstall) btnInstall.style.display = 'block';
  });

  // 2) Función para mostrar SweetAlert2
  async function showInstallPrompt() {
    if (!deferredPrompt) return;
    const { value } = await Swal.fire({
      title: 'Instalar MedTuc IoT',
      text: '¿Quieres instalar esta app en tu dispositivo?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Instalar',
      cancelButtonText: 'Cancelar',
      reverseButtons: true,
      allowOutsideClick: false,
      allowEscapeKey: false
    });
    if (value) {
      console.log('[PWA] Usuario aceptó SweetAlert, disparando prompt nativo');
      deferredPrompt.prompt();
      const { outcome } = await deferredPrompt.userChoice;
      console.log('[PWA] Resultado instalación:', outcome);
      deferredPrompt = null;
    } else {
      console.log('[PWA] Usuario canceló SweetAlert');
    }
  }

  // 3) Botón manual
  if (btnInstall) {
    btnInstall.style.display = 'none';
    btnInstall.addEventListener('click', async () => {
      if (!deferredPrompt) {
        console.warn('[PWA] deferredPrompt no disponible');
        return;
      }
      console.log('[PWA] Botón instalar clickeado');
      btnInstall.style.display = 'none';
      deferredPrompt.prompt();
      const { outcome } = await deferredPrompt.userChoice;
      console.log('[PWA] Resultado instalación (botón):', outcome);
      deferredPrompt = null;
    });
  }

  // 4) Detectar instalación completada
  window.addEventListener('appinstalled', () => {
    console.log('[PWA] App instalada');
    if (btnInstall) btnInstall.style.display = 'none';
    deferredPrompt = null;
  });

  // 5) Diagnóstico rápido (opcional)
  setTimeout(() => {
    fetch('/medtucIoT/app/manifest.json')
      .then(r => console.log('[PWA] manifest.json status:', r.status))
      .catch(e => console.error('[PWA] Error manifest.json:', e));
    if (navigator.serviceWorker.controller) {
      console.log('[PWA] Service Worker controla la página');
    } else {
      console.warn('[PWA] Service Worker NO controla la página');
    }
  }, 1000);
});
