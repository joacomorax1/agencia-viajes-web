<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/css/styles.css">
  <title>Agencia de Viajes</title>
</head>
<body>
  <header>
    <h1>Agencia de Viajes</h1>
    <p>Encuentra tu próximo destino</p>
  </header>
  <main>
    <p><a class="clear-link" href="gestion.php">Gestionar vuelos, hoteles y reservas →</a></p>
    <?= notificacionEmergente($mensajeOferta) ?>
    <section class="search-container" aria-labelledby="search-title">
      <h2 id="search-title">Buscar paquetes turísticos</h2>
      <p class="helper-text">Completa los criterios para recibir opciones personalizadas.</p>
      <!-- method="get" permite recuperar los datos con $_GET en PHP y compartir la búsqueda por URL. -->
      <form class="filters" method="get" action="index.php">
        <label>Destino / nombre del hotel
          <input type="text" name="nombre_hotel" value="<?= e($filtro->nombreHotel) ?>" placeholder="Ej.: Costa Azul Resort">
        </label>
        <label>Ciudad
          <input type="text" name="ciudad" value="<?= e($filtro->ciudad) ?>" placeholder="Ej.: Cancún">
        </label>
        <label>País
          <input type="text" name="pais" value="<?= e($filtro->pais) ?>" placeholder="Ej.: México">
        </label>
        <label>Fecha de viaje
          <input type="date" name="fecha_viaje" value="<?= e($filtro->fechaViaje) ?>">
        </label>
        <label>Duración (noches)
          <input type="number" name="duracion_viaje" value="<?= $filtro->duracionViaje ?: '' ?>" min="1" max="30" placeholder="Ej.: 7">
        </label>
        <button type="submit">Buscar</button>
        <a class="clear-link" href="index.php">Limpiar filtros</a>
      </form>
    </section>

    <section aria-labelledby="results-title">
      <h2 id="results-title">Resultados disponibles</h2>
      <div id="results-container">
        <?php if ($resultados === []): ?>
          <p class="empty-result">No encontramos opciones para esos filtros. Prueba con otra fecha o destino.</p>
        <?php else: foreach ($resultados as $paquete): ?>
          <article class="package-card">
            <h3><?= e($paquete['hotel']) ?></h3>
            <p><strong><?= e($paquete['ciudad']) ?>, <?= e($paquete['pais']) ?></strong></p>
            <p class="package-description"><?= e($paquete['descripcion']) ?></p>
            <p>Salida: <strong><?= e($paquete['fecha']) ?></strong> · <?= $paquete['duracion'] ?> noches</p>
            <p>Desde <strong>$<?= number_format($paquete['precio'], 0, ',', '.') ?> CLP</strong></p>
            <p>Disponibilidad actualizada: <?= $paquete['cupos'] ?> cupos</p>
            <details class="package-details">
              <summary>¿Qué incluye?</summary>
              <ul><?php foreach ($paquete['incluye'] as $detalle): ?><li><?= e($detalle) ?></li><?php endforeach; ?></ul>
            </details>
            <?php if ($paquete['oferta']): ?><p class="offer">¡Oferta especial disponible!</p><?php endif; ?>
            <form method="post" class="reservation-form">
              <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
              <input type="hidden" name="accion" value="agregar">
              <input type="hidden" name="paquete_id" value="<?= e($paquete['id']) ?>">
              <button class="reserve-button" type="submit">Agregar al carrito</button>
            </form>
          </article>
        <?php endforeach; endif; ?>
      </div>
    </section>

    <section class="booking-guide" aria-labelledby="booking-title">
      <h2 id="booking-title">Tu viaje, en tres pasos</h2>
      <div class="guide-grid">
        <article><span class="guide-number">1</span><h3>Encuentra tu destino</h3><p>Filtra por ciudad, país, fecha o duración y compara las opciones disponibles.</p></article>
        <article><span class="guide-number">2</span><h3>Arma tu itinerario</h3><p>Revisa qué incluye cada paquete y agrega al carrito los viajes que te interesan.</p></article>
        <article><span class="guide-number">3</span><h3>Solicita tu reserva</h3><p>Confirma tu solicitud y nuestro equipo te contactará para ayudarte con los siguientes pasos.</p></article>
      </div>
    </section>

    <section class="cart-container" aria-labelledby="cart-title">
      <h2 id="cart-title">Carrito de paquetes</h2>
      <?php if ($carrito === []): ?>
        <p>Tu carrito está vacío. Agrega un paquete para continuar con tu reserva.</p>
      <?php else: ?>
        <ul class="cart-list">
          <?php foreach ($carrito as $item): ?>
            <li>
              <span>
                <strong><?= e($item['paquete']['hotel']) ?></strong><br>
                <?= e($item['paquete']['ciudad']) ?> · <?= $item['cantidad'] ?> pasajero(s)
              </span>
              <span>
                $<?= number_format($item['subtotal'], 0, ',', '.') ?> CLP
                <form method="post" class="inline-form">
                  <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                  <input type="hidden" name="accion" value="eliminar">
                  <input type="hidden" name="paquete_id" value="<?= e($item['paquete']['id']) ?>">
                  <button type="submit" class="remove-button">Eliminar</button>
                </form>
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
        <p class="cart-total">Total estimado: $<?= number_format($totalCarrito, 0, ',', '.') ?> CLP</p>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="accion" value="confirmar">
          <button type="submit">Solicitar reserva segura</button>
        </form>
      <?php endif; ?>
    </section>
  </main>
  <script>
    const notification = document.querySelector('#notification');
    const offers = <?= json_encode(array_values(array_map(
      fn (array $p): string => "¡Oferta actualizada! {$p['ciudad']} desde $" . number_format($p['precio'], 0, ',', '.') . ' CLP.',
      array_filter($paquetes, fn (array $p): bool => $p['oferta'])
    )), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
    const showNotification = message => {
      notification.textContent = message;
      notification.classList.add('show');
      clearTimeout(showNotification.timer);
      showNotification.timer = setTimeout(() => notification.classList.remove('show'), 5000);
    };
    setInterval(() => showNotification(offers[Math.floor(Math.random() * offers.length)]), 15000);
  </script>
</body>
</html>

