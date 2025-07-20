<?php
// /medtuciot/index.php

// Carga la configuración (define BASE_PATH y $pdo)
require __DIR__ . '/app/config.php';

// Redirige la raíz al login (rutas amigables manejadas por .htaccess)
header('Location: ' . BASE_PATH . '/login');
exit;
