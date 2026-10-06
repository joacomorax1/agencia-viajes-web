// BÚSQUEDA Y RENDERIZADO
// ========================================
function obtenerPaquetesFiltrados() {
  const destinoBuscado = destinationInput.value.trim().toLocaleLowerCase();
  const fechaBuscada = dateInput.value;
  const tipoBuscado = typeSelect.value;

  return paquetes
    .map((paquete, indice) => ({ paquete, indice }))
    .filter(({ paquete }) => {
      const coincideDestino = paquete.destino
        .toLocaleLowerCase()
        .includes(destinoBuscado);

      const coincideFecha = fechaBuscada === "" || paquete.fecha === fechaBuscada;
      const coincideTipo = tipoBuscado === "" || paquete.tipo === tipoBuscado;

      return paquete.estaDisponible() &&
        coincideDestino &&
        coincideFecha &&
        coincideTipo;
    });
}

function crearTarjeta(paquete, indice) {
  const tarjeta = document.createElement("article");
  tarjeta.className = "package-card";

  tarjeta.innerHTML = `
    <h3>${paquete.destino}</h3>
    <p><strong>Tipo:</strong> ${paquete.tipo}</p>
    <p><strong>Fecha:</strong> ${paquete.fecha}</p>
    <p><strong>Precio:</strong> ${formatoPrecio(paquete.precio)}</p>
    <p><strong>Disponibilidad:</strong> ${paquete.disponibilidad} cupos</p>
    ${paquete.oferta ? '<p class="offer">¡Oferta especial disponible!</p>' : ""}
  `;

  const botonReserva = document.createElement("button");
  botonReserva.className = "reserve-button";
  botonReserva.type = "button";
  botonReserva.textContent = "Agregar al carrito";
  botonReserva.addEventListener("click", () => agregarAlCarrito(indice));

  tarjeta.appendChild(botonReserva);
  return tarjeta;
}

function mostrarResultados(resultados) {
  resultsContainer.replaceChildren();

  if (resultados.length === 0) {
    resultsContainer.innerHTML = `
      <p class="empty-result">No se encontraron paquetes con esos filtros.</p>
    `;
    return;
  }

  resultados.forEach(({ paquete, indice }) => {
    resultsContainer.appendChild(crearTarjeta(paquete, indice));
  });
}

function buscarPaquetes() {
  const resultados = obtenerPaquetesFiltrados();
  mostrarResultados(resultados);
}

// ========================================
