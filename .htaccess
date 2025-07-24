# +--------------------------------------------------+
# | .htaccess universal para entorno local y Hostinger |
# | Colócalo en la raíz del proyecto                 |
# +--------------------------------------------------+

# 1) Activar mod_rewrite
RewriteEngine On

# 2) Si estás en local (XAMPP), activa esta línea:
#    RewriteBase /medtucIoT/

# En producción (Hostinger - subdominio raíz), NO actives RewriteBase
#    RewriteBase /

# 💡 Usa SOLO una RewriteBase según el entorno

# 3) Redirigir raíz (/) a login
RewriteRule ^$ login [L,R=302]

# 4) Rutas amigables → scripts en /app/
RewriteRule ^login/?$         app/login.php         [L,QSA]
RewriteRule ^register/?$      app/register.php      [L,QSA]
RewriteRule ^dashboard/?$     app/dashboard.php     [L,QSA]
RewriteRule ^logout/?$        app/logout.php        [L,QSA]
RewriteRule ^sensor/?$        app/sensor.php        [L,QSA]
RewriteRule ^get_latest/?$    app/get_latest.php    [L,QSA]
RewriteRule ^get_history/?$   app/get_history.php   [L,QSA]
RewriteRule ^recuperar_acceso/?$ app/recuperar_acceso.php [L,QSA]


# 5) Excepciones: permitir acceso directo a APIs necesarias (incluye imgPerfil.php)
RewriteCond %{THE_REQUEST} \s/app/(sensor|get_latest|get_history|logout|imgPerfil)\.php [NC]
RewriteRule ^ - [L]
RewriteCond %{THE_REQUEST} \s/app/(sensor|get_latest|get_history|logout|imgPerfil|recuperar_acceso)\.php [NC]


# 6) Bloquear acceso directo a otros /app/*.php excepto los permitidos arriba
RewriteCond %{THE_REQUEST} \s/app/.*\.php [NC]
RewriteCond %{THE_REQUEST} \s/app/(?!logout\.php|get_latest\.php|get_history\.php|sensor\.php|imgPerfil\.php).*\.php [NC]
RewriteRule ^app/.*\.php$ - [F]
