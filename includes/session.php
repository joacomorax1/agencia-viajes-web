<?php
declare(strict_types=1);

// Configure cookies before starting the session.
$esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.gc_maxlifetime', '3600');
session_name('AGENCIA_SESION');
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'secure' => $esHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

$ahora = time();
$inactiva = isset($_SESSION['ultima_actividad']) && $ahora - (int) $_SESSION['ultima_actividad'] > 1800;
$demasiadoAntigua = isset($_SESSION['creada_en']) && $ahora - (int) $_SESSION['creada_en'] > 28800;
if ($inactiva || $demasiadoAntigua) {
    $_SESSION = [];
    $nombreCookieSesion = session_name();
    $parametrosCookie = session_get_cookie_params();
    setcookie($nombreCookieSesion, '', [
        'expires' => time() - 42000,
        'path' => $parametrosCookie['path'],
        'secure' => $parametrosCookie['secure'],
        'httponly' => $parametrosCookie['httponly'],
        'samesite' => $parametrosCookie['samesite'] ?? 'Lax',
    ]);
    unset($_COOKIE[$nombreCookieSesion]);
    session_destroy();
    session_start();
    $_SESSION['reserva_mensaje'] = 'Tu sesión expiró por seguridad. Puedes volver a seleccionar tus paquetes.';
}

$_SESSION['ultima_actividad'] = $ahora;
$_SESSION['creada_en'] ??= $ahora;
$_SESSION['carrito'] ??= [];
$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
