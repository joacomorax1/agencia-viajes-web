// CARRITO Y RESERVAS (vista estática para poder visualizarlo sin servidor PHP)
// ========================================
function agregarAlCarrito(indice) {
  const paquete = paquetes[indice];
  const cantidad = carrito.get(indice) || 0;

  if (cantidad < paquete.disponibilidad) {
    carrito.set(indice, cantidad + 1);
    mostrarNotificacion(
      `${paquete.destino} se agregó al carrito.`
    );
  } else {
    mostrarNotificacion(
      `No puedes agregar más cupos para ${paquete.destino}.`
    );
  }

  mostrarCarrito();
}

function mostrarCarrito() {
  cartList.replaceChildren();
  let total = 0;

  carrito.forEach((cantidad, indice) => {
    const paquete = paquetes[indice];
    const item = document.createElement("li");
    const subtotal = paquete.precio * cantidad;
    total += subtotal;
    item.innerHTML = `<span><strong>${paquete.destino}</strong><br>${cantidad} pasajero(s)</span>`;

    const acciones = document.createElement("span");
    acciones.textContent = formatoPrecio(subtotal) + " ";
    const eliminar = document.createElement("button");
    eliminar.type = "button";
    eliminar.className = "remove-button";
    eliminar.textContent = "Eliminar";
    eliminar.addEventListener("click", () => {
      carrito.delete(indice);
      mostrarCarrito();
    });
    acciones.appendChild(eliminar);
    item.appendChild(acciones);
    cartList.appendChild(item);
  });

  const estaVacio = carrito.size === 0;
  emptyCart.hidden = !estaVacio;
  cartContent.hidden = estaVacio;
  cartTotal.textContent = `Total estimado: ${formatoPrecio(total)}`;
}

function confirmarReserva() {
  if (carrito.size === 0) return;

  carrito.forEach((cantidad, indice) => {
    paquetes[indice].disponibilidad -= cantidad;
  });
  carrito.clear();
  mostrarNotificacion("Reserva solicitada correctamente. Te contactaremos para el pago seguro.");
  mostrarCarrito();
  buscarPaquetes();
}

// ========================================
