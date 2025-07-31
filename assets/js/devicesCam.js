document.addEventListener('DOMContentLoaded', () => {
  const camIcon = document.getElementById('openCameraPopup');

  if (!camIcon) {
    console.warn('No se encontró el icono con id "openCameraPopup"');
    return;
  }

  camIcon.addEventListener('click', () => {
    const cameraURL = camIcon.dataset.url || 'https://www.skylinewebcams.com/es/webcam/argentina/tierra-del-fuego/ushuaia/ushuaia.html';
    const isDark = document.body.classList.contains('dark');

    Swal.fire({
      title: 'Cámara IP en Vivo',
      html: `
        <div style="position:relative;padding-top:56.25%;width:100%;">
          <iframe src="${cameraURL}"
                  style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;"
                  allow="autoplay; fullscreen"></iframe>
        </div>
      `,
      width: '90%',
      background: isDark ? '#2A2A2A' : '#fff',
      color: isDark ? '#EEE' : '#333',
      showCloseButton: true,
      showConfirmButton: false
    });
  });
});
