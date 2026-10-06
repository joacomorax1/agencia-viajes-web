<?php
declare(strict_types=1);
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

require __DIR__ . '/includes/database.php';

function esc(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function redirigir(string $mensaje, string $tipo = 'ok'): never {
    $_SESSION['flash'] = [$tipo, $mensaje];
    header('Location: gestion.php');
    exit;
}
function textoPost(string $campo): string { return trim((string) ($_POST[$campo] ?? '')); }
function enteroPost(string $campo, int $minimo = 0): int|false {
    return filter_var($_POST[$campo] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => $minimo]]);
}
function decimalPost(string $campo): float|false {
    $valor = str_replace(',', '.', textoPost($campo));
    return filter_var($valor, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);
}

try {
    $db = conectarBaseDatos();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
            redirigir('La solicitud expiró. Recarga la página e inténtalo otra vez.', 'error');
        }
        $accion = (string) ($_POST['accion'] ?? '');

        if ($accion === 'vuelo') {
            $origen = textoPost('origen'); $destino = textoPost('destino');
            $fecha = textoPost('fecha'); $plazas = enteroPost('plazas'); $precio = decimalPost('precio');
            $objetoFecha = DateTime::createFromFormat('!Y-m-d', $fecha);
            $fechaValida = $objetoFecha instanceof DateTime && $objetoFecha->format('Y-m-d') === $fecha;
            if ($origen === '' || $destino === '' || !$fechaValida || $plazas === false || $precio === false) {
                redirigir('Completa correctamente todos los datos del vuelo.', 'error');
            }
            $db->prepare('INSERT INTO VUELO (origen, destino, fecha, plazas_disponibles, precio) VALUES (?, ?, ?, ?, ?)')
                ->execute([$origen, $destino, $fecha, $plazas, $precio]);
            redirigir('Vuelo registrado correctamente.');
        }

        if ($accion === 'hotel') {
            $nombre = textoPost('nombre'); $ubicacion = textoPost('ubicacion');
            $habitaciones = enteroPost('habitaciones'); $tarifa = decimalPost('tarifa');
            if ($nombre === '' || $ubicacion === '' || $habitaciones === false || $tarifa === false) {
                redirigir('Completa correctamente todos los datos del hotel.', 'error');
            }
            $db->prepare('INSERT INTO HOTEL (nombre, ubicacion, habitaciones_disponibles, tarifa_noche) VALUES (?, ?, ?, ?)')
                ->execute([$nombre, $ubicacion, $habitaciones, $tarifa]);
            redirigir('Hotel registrado correctamente.');
        }

        if ($accion === 'reserva') {
            $cliente = enteroPost('id_cliente', 1); $vuelo = enteroPost('id_vuelo', 1); $hotel = enteroPost('id_hotel', 1);
            if ($cliente === false || $vuelo === false || $hotel === false) redirigir('Selecciona un cliente, vuelo y hotel válidos.', 'error');
            $db->beginTransaction();
            try {
                // Los bloqueos evitan sobreventa cuando dos personas reservan al mismo tiempo.
                $consultaVuelo = $db->prepare('SELECT plazas_disponibles FROM VUELO WHERE id_vuelo = ? FOR UPDATE');
                $consultaVuelo->execute([$vuelo]);
                $consultaHotel = $db->prepare('SELECT habitaciones_disponibles FROM HOTEL WHERE id_hotel = ? FOR UPDATE');
                $consultaHotel->execute([$hotel]);
                $datosVuelo = $consultaVuelo->fetch(); $datosHotel = $consultaHotel->fetch();
                if (!$datosVuelo || !$datosHotel || $datosVuelo['plazas_disponibles'] < 1 || $datosHotel['habitaciones_disponibles'] < 1) {
                    throw new RuntimeException('El vuelo u hotel seleccionado ya no tiene disponibilidad.');
                }
                $db->prepare('INSERT INTO RESERVA (id_cliente, id_vuelo, id_hotel) VALUES (?, ?, ?)')->execute([$cliente, $vuelo, $hotel]);
                $db->prepare('UPDATE VUELO SET plazas_disponibles = plazas_disponibles - 1 WHERE id_vuelo = ?')->execute([$vuelo]);
                $db->prepare('UPDATE HOTEL SET habitaciones_disponibles = habitaciones_disponibles - 1 WHERE id_hotel = ?')->execute([$hotel]);
                $db->commit();
                redirigir('Reserva registrada y disponibilidad actualizada.');
            } catch (Throwable $error) {
                if ($db->inTransaction()) $db->rollBack();
                redirigir($error->getMessage(), 'error');
            }
        }
        redirigir('Acción no reconocida.', 'error');
    }

    $vuelos = $db->query('SELECT * FROM VUELO ORDER BY fecha, id_vuelo')->fetchAll();
    $hoteles = $db->query('SELECT * FROM HOTEL ORDER BY nombre')->fetchAll();
    $reservas = $db->query('SELECT * FROM RESERVA ORDER BY fecha_reserva DESC, id_reserva DESC')->fetchAll();
    // Consulta avanzada: JOIN + GROUP BY + HAVING para hoteles con más de dos reservas.
    $hotelesPopulares = $db->query(
        'SELECT h.id_hotel, h.nombre, h.ubicacion, h.habitaciones_disponibles, COUNT(r.id_reserva) AS total_reservas
         FROM HOTEL h INNER JOIN RESERVA r ON r.id_hotel = h.id_hotel
         GROUP BY h.id_hotel, h.nombre, h.ubicacion, h.habitaciones_disponibles
         HAVING COUNT(r.id_reserva) > 2
         ORDER BY total_reservas DESC, h.nombre ASC'
    )->fetchAll();
} catch (Throwable $error) {
    $errorConexion = $error->getMessage(); $vuelos = $hoteles = $reservas = $hotelesPopulares = [];
}
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
require __DIR__ . '/views/management.php';
