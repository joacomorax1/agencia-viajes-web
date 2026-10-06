<?php
declare(strict_types=1);

/** Crea una conexión PDO segura y reutilizable a MySQL. */
function conectarBaseDatos(): PDO
{
    static $conexion = null;
    if ($conexion instanceof PDO) {
        return $conexion;
    }

    $config = require __DIR__ . '/config.php';
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $config['host'],
        $config['port'],
        $config['name']
    );

    try {
        $conexion = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $conexion;
    } catch (PDOException $exception) {
        // No se expone el detalle de conexión ni credenciales al visitante.
        error_log('No se pudo conectar a AGENCIA: ' . $exception->getMessage());
        throw new RuntimeException('No fue posible conectar con la base de datos. Revisa la configuración del servidor.');
    }
}

