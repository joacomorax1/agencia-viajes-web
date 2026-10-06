// FUNCIONES DE APOYO
// ========================================
function formatoPrecio(precio) {
  return new Intl.NumberFormat("es-CL", {
    style: "currency",
    currency: "CLP",
    maximumFractionDigits: 0
  }).format(precio);
}

function mostrarNotificacion(mensaje) {
  notification.textContent = mensaje;
  notification.classList.add("show");

  clearTimeout(mostrarNotificacion.temporizador);

  mostrarNotificacion.temporizador = setTimeout(() => {
    notification.classList.remove("show");
  }, 4000);
}

// ========================================
