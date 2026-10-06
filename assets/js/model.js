// MODELO: paquete turístico
// ========================================
class PaqueteTuristico {
  constructor(destino, fecha, tipo, precio, disponibilidad, oferta = false) {
    this.destino = destino;
    this.fecha = fecha;
    this.tipo = tipo;
    this.precio = precio;
    this.disponibilidad = disponibilidad;
    this.oferta = oferta;
  }

  estaDisponible() {
    return this.disponibilidad > 0;
  }

  reservar() {
    if (!this.estaDisponible()) {
      return false;
    }

    this.disponibilidad--;
    return true;
  }
}

// ========================================
// DATOS DE LA APLICACIÓN
// ========================================
const paquetes = [
  new PaqueteTuristico("Cancún", "2026-10-15", "Paquete", 850000, 5, true),
  new PaqueteTuristico("Santiago", "2026-10-10", "Vuelo", 120000, 12),
  new PaqueteTuristico("Buenos Aires", "2026-11-05", "Hotel", 230000, 8),
  new PaqueteTuristico("Río de Janeiro", "2026-12-20", "Paquete", 990000, 3, true)
];

// ========================================
