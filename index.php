<?php
declare(strict_types=1);

require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/travel.php';

$paquetes = catalogoPaquetes();

$_SESSION['cupos'] ??= array_column($paquetes, 'cupos', 'id');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenValido = isset($_POST['csrf_token'])
        && is_string($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);

    if (!$tokenValido) {
        http_response_code(403);
        exit('Solicitud no válida. Recarga la página e inténtalo nuevamente.');
    }

    $accion = (string) ($_POST['accion'] ?? '');
    $idPaquete = (string) ($_POST['paquete_id'] ?? '');
    $indice = array_search($idPaquete, array_column($paquetes, 'id'), true);

    if ($accion === 'agregar' && $indice !== false) {
        $disponibles = $_SESSION['cupos'][$idPaquete] ?? 0;
        $cantidadActual = $_SESSION['carrito'][$idPaquete] ?? 0;
        if ($cantidadActual < $disponibles) {
            $_SESSION['carrito'][$idPaquete] = $cantidadActual + 1;
            $_SESSION['reserva_mensaje'] = "{$paquetes[$indice]['ciudad']} se agregó al carrito.";
        } else {
            $_SESSION['reserva_mensaje'] = 'No puedes agregar más cupos de este paquete.';
        }
    } elseif ($accion === 'eliminar' && $indice !== false) {
        unset($_SESSION['carrito'][$idPaquete]);
        $_SESSION['reserva_mensaje'] = 'El paquete se eliminó del carrito.';
    } elseif ($accion === 'confirmar') {
        $hayCupos = $_SESSION['carrito'] !== [];
        foreach ($_SESSION['carrito'] as $id => $cantidad) {
            if (($_SESSION['cupos'][$id] ?? 0) < $cantidad) {
                $hayCupos = false;
                break;
            }
        }

        if ($hayCupos) {
            foreach ($_SESSION['carrito'] as $id => $cantidad) {
                $_SESSION['cupos'][$id] -= $cantidad;
            }
            $_SESSION['carrito'] = [];
            // Una operación de reserva cambia el identificador de sesión.
            session_regenerate_id(true);
            $_SESSION['reserva_mensaje'] = 'Reserva solicitada correctamente. Un asesor se comunicará contigo para el pago seguro.';
        } else {
            $_SESSION['reserva_mensaje'] = 'No fue posible confirmar la reserva: verifica la disponibilidad.';
        }
    } else {
        $_SESSION['reserva_mensaje'] = 'No fue posible procesar la solicitud.';
    }
    // Evita que actualizar la página reenvíe el POST y descuente otro cupo.
    header('Location: index.php');
    exit;
}
$reservaMensaje = $_SESSION['reserva_mensaje'] ?? '';
unset($_SESSION['reserva_mensaje']);
foreach ($paquetes as $indice => $paquete) {
    $paquetes[$indice]['cupos'] = $_SESSION['cupos'][$paquete['id']];
}

$paquetesPorId = array_column($paquetes, null, 'id');
$carrito = [];
$totalCarrito = 0;
foreach ($_SESSION['carrito'] as $idPaquete => $cantidad) {
    if (isset($paquetesPorId[$idPaquete]) && is_int($cantidad) && $cantidad > 0) {
        $paquete = $paquetesPorId[$idPaquete];
        $subtotal = $paquete['precio'] * $cantidad;
        $carrito[] = ['paquete' => $paquete, 'cantidad' => $cantidad, 'subtotal' => $subtotal];
        $totalCarrito += $subtotal;
    }
}

$filtro = FiltroViaje::desdeFormulario($_GET); // Recuperación de datos y paso de variables.
$resultados = array_values(array_filter($paquetes, fn (array $paquete): bool => $filtro->coincideCon($paquete)));
$ofertaInicial = current(array_filter($paquetes, fn (array $paquete): bool => $paquete['oferta'] && $paquete['cupos'] > 0));
$mensajeOferta = $reservaMensaje ?: ($ofertaInicial
    ? "¡Oferta especial! {$ofertaInicial['ciudad']} desde $" . number_format($ofertaInicial['precio'], 0, ',', '.') . '. Quedan ' . $ofertaInicial['cupos'] . ' cupos.'
    : 'Consulta nuestras nuevas ofertas de viaje.');

require __DIR__ . '/views/home.php';
