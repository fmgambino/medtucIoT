<?php
// /index.php

// Carga la configuración (define BASE_PATH y $pdo)
require __DIR__ . '/app/config.php';

// Redirige a la página de inicio de sesión
header('Location: ' . BASE_PATH . 'login');
exit;
