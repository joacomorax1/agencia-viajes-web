<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Gestión | Horizonte Travel</title>
<link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<header>
<h1>Horizonte Travel</h1>
<p>Administración de servicios y reservas</p>
</header>
<main class="management">
<p>
<a class="clear-link" href="index.php">← Volver al buscador</a>
</p>
<?php if (isset($errorConexion)): ?>
<p class="message error">
<?= esc($errorConexion) ?> Ejecuta primero <code>database/schema.sql</code> y configura <code>includes/config.php</code>.</p>
<?php else: ?>
<?php if ($flash): ?>
<p class="message <?= esc($flash[0]) ?>">
<?= esc($flash[1]) ?>
</p>
<?php endif; ?>
<section class="form-grid" aria-label="Ingreso de servicios">
<form class="data-form" method="post" data-validate>
<h2>Registrar vuelo</h2>
<input type="hidden" name="csrf" value="<?= esc($_SESSION['csrf']) ?>">
<input type="hidden" name="accion" value="vuelo">
<label>Origen<input name="origen" required maxlength="100">
</label>
<label>Destino<input name="destino" required maxlength="100">
</label>
<label>Fecha<input name="fecha" type="date" required>
</label>
<label>Plazas disponibles<input name="plazas" type="number" min="0" required>
</label>
<label>Precio (CLP)<input name="precio" type="number" min="0" step="0.01" required>
</label>
<button>Guardar vuelo</button>
</form>
<form class="data-form" method="post" data-validate>
<h2>Registrar hotel</h2>
<input type="hidden" name="csrf" value="<?= esc($_SESSION['csrf']) ?>">
<input type="hidden" name="accion" value="hotel">
<label>Nombre<input name="nombre" required maxlength="150">
</label>
<label>Ubicación<input name="ubicacion" required maxlength="150">
</label>
<label>Habitaciones disponibles<input name="habitaciones" type="number" min="0" required>
</label>
<label>Tarifa por noche (CLP)<input name="tarifa" type="number" min="0" step="0.01" required>
</label>
<button>Guardar hotel</button>
</form>
</section>
<section class="search-container">
<h2>Registrar reserva</h2>
<form class="filters" method="post" data-validate>
<input type="hidden" name="csrf" value="<?= esc($_SESSION['csrf']) ?>">
<input type="hidden" name="accion" value="reserva">
<label>ID cliente<input name="id_cliente" type="number" min="1" required>
</label>
<label>Vuelo<select name="id_vuelo" required>
<option value="">Selecciona</option>
<?php foreach ($vuelos as $v): ?>
<option value="<?= $v['id_vuelo'] ?>">#<?= $v['id_vuelo'] ?> · <?= esc($v['origen']) ?>–<?= esc($v['destino']) ?> (<?= $v['plazas_disponibles'] ?> plazas)</option>
<?php endforeach; ?>
</select>
</label>
<label>Hotel<select name="id_hotel" required>
<option value="">Selecciona</option>
<?php foreach ($hoteles as $h): ?>
<option value="<?= $h['id_hotel'] ?>">#<?= $h['id_hotel'] ?> · <?= esc($h['nombre']) ?> (<?= $h['habitaciones_disponibles'] ?> hab.)</option>
<?php endforeach; ?>
</select>
</label>
<button>Confirmar reserva</button>
</form>
</section>
<section>
<h2>Vuelos registrados</h2>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>ID</th>
<th>Origen</th>
<th>Destino</th>
<th>Fecha</th>
<th>Plazas</th>
<th>Precio</th>
</tr>
</thead>
<tbody>
<?php foreach ($vuelos as $v): ?>
<tr>
<td>
<?= $v['id_vuelo'] ?>
</td>
<td>
<?= esc($v['origen']) ?>
</td>
<td>
<?= esc($v['destino']) ?>
</td>
<td>
<?= esc($v['fecha']) ?>
</td>
<td>
<?= $v['plazas_disponibles'] ?>
</td>
<td>$<?= number_format((float)$v['precio'], 0, ',', '.') ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</section>
<section>
<h2>Hoteles registrados</h2>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Ubicación</th>
<th>Habitaciones</th>
<th>Tarifa/noche</th>
</tr>
</thead>
<tbody>
<?php foreach ($hoteles as $h): ?>
<tr>
<td>
<?= $h['id_hotel'] ?>
</td>
<td>
<?= esc($h['nombre']) ?>
</td>
<td>
<?= esc($h['ubicacion']) ?>
</td>
<td>
<?= $h['habitaciones_disponibles'] ?>
</td>
<td>$<?= number_format((float)$h['tarifa_noche'], 0, ',', '.') ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</section>
<section>
<h2>Reservas registradas</h2>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>ID</th>
<th>Cliente</th>
<th>Fecha</th>
<th>Vuelo</th>
<th>Hotel</th>
</tr>
</thead>
<tbody>
<?php foreach ($reservas as $r): ?>
<tr>
<td>
<?= $r['id_reserva'] ?>
</td>
<td>
<?= $r['id_cliente'] ?>
</td>
<td>
<?= esc($r['fecha_reserva']) ?>
</td>
<td>
<?= $r['id_vuelo'] ?>
</td>
<td>
<?= $r['id_hotel'] ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</section>
<section>
<h2>Hoteles con más de dos reservas</h2>
<p class="helper-text">Consulta avanzada con <code>INNER JOIN</code>, <code>GROUP BY</code> y <code>HAVING</code>.</p>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Hotel</th>
<th>Ubicación</th>
<th>Habitaciones disponibles</th>
<th>Total reservas</th>
</tr>
</thead>
<tbody>
<?php foreach ($hotelesPopulares as $h): ?>
<tr>
<td>
<?= esc($h['nombre']) ?>
</td>
<td>
<?= esc($h['ubicacion']) ?>
</td>
<td>
<?= $h['habitaciones_disponibles'] ?>
</td>
<td>
<?= $h['total_reservas'] ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</section>
<?php endif; ?>
</main>
<script>document.querySelectorAll('[data-validate]').forEach(f=>f.addEventListener('submit',e=>{if(!f.checkValidity()){e.preventDefault();f.reportValidity();}}));</script>
</body>
</html>

