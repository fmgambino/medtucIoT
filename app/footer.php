    <!-- Footer -->
    <footer class="footer">
      © 2025 MedTuCIoT – Electrónica Gambino
    </footer>
  </div>
</div>

<!-- SCRIPTS -->
<script>
  const BASE_PATH = '<?= rtrim(BASE_PATH, '/') ?>';
  window.BASE_PATH = BASE_PATH;
</script>

<script defer src="<?= BASE_PATH ?>/assets/js/main.js"></script>
<script defer src="<?= BASE_PATH ?>/assets/js/addSensor.js"></script>
<script defer src="<?= BASE_PATH ?>/assets/js/charts_sensores.js"></script>
<script defer src="<?= BASE_PATH ?>/assets/js/pwa.js"></script>

<script>
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register(BASE_PATH + '/app/service-wojer.js', {
    scope: BASE_PATH + '/app/'
  })
  .then(reg => {
    console.log('SW registrado', reg);
    if (navigator.serviceWorker.controller) return;
    reg.addEventListener('updatefound', () => {
      const newSW = reg.installing;
      newSW.addEventListener('statechange', () => {
        if (newSW.state === 'activated') {
          window.location.reload();
        }
      });
    });
  })
  .catch(err => console.error('Error SW:', err));
}
</script>
</body>
</html>
