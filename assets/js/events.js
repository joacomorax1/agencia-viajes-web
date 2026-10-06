// EVENTOS
// ========================================
searchButton.addEventListener("click", buscarPaquetes);
destinationInput.addEventListener("input", buscarPaquetes);
dateInput.addEventListener("change", buscarPaquetes);
typeSelect.addEventListener("change", buscarPaquetes);
confirmReservation.addEventListener("click", confirmarReserva);

destinationInput.addEventListener("keydown", evento => {
  if (evento.key === "Enter") {
    buscarPaquetes();
  }
});

// Simulación de notificaciones en tiempo real sobre ofertas.
setInterval(() => {
  const ofertasDisponibles = paquetes.filter(paquete => {
    return paquete.oferta && paquete.estaDisponible();
  });

  if (ofertasDisponibles.length === 0) {
    return;
  }

  const indiceAleatorio = Math.floor(Math.random() * ofertasDisponibles.length);
  const oferta = ofertasDisponibles[indiceAleatorio];

  mostrarNotificacion(
    `¡Oferta especial! ${oferta.destino} desde ${formatoPrecio(oferta.precio)}.`
  );
}, 15000);

// Muestra todos los paquetes al cargar la página.
buscarPaquetes();
mostrarCarrito();
