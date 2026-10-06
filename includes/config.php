<?php
declare(strict_types=1);

/*
 * Configuración de la conexión. En producción defina estas variables en el
 * servidor (no guarde contraseñas reales en el repositorio):
 * DB_HOST, DB_PORT, DB_NAME, DB_USER y DB_PASS.
 */
return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'name' => getenv('DB_NAME') ?: 'AGENCIA',
    'user' => getenv('DB_USER') ?: 'agencia_app',
    'pass' => getenv('DB_PASS') ?: '',
];
