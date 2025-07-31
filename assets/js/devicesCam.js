document.addEventListener('DOMContentLoaded', () => {
  const camIcon = document.getElementById('openCameraPopup');

  if (camIcon) {
    camIcon.addEventListener('click', () => {
      const cameraURL = 'http://TU_CAMARA_IP/stream'; // Reemplaza con la URL real del stream

      const popup = window.open('', 'cameraPopup', 'width=800,height=600');
      if (!popup) {
        alert('Por favor, permite las ventanas emergentes en tu navegador.');
        return;
      }

      popup.document.write(`
        <!DOCTYPE html>
        <html lang="es">
        <head>
          <meta charset="UTF-8">
          <title>Cámara IP</title>
          <style>
            body {
              margin: 0;
              background-color: #000;
              display: flex;
              align-items: center;
              justify-content: center;
              height: 100vh;
            }
            video {
              max-width: 100%;
              max-height: 100%;
            }
          </style>
        </head>
        <body>
          <video id="ipCam" controls autoplay></video>
          <script>
            const video = document.getElementById('ipCam');
            video.src = '${cameraURL}';
          <\/script>
        </body>
        </html>
      `);
    });
  } else {
    console.warn('No se encontró el icono con id "openCameraPopup"');
  }
});
