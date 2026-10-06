-- Ejecutar una vez desde MySQL Workbench o consola: mysql -u root -p < database/schema.sql
CREATE DATABASE IF NOT EXISTS AGENCIA
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE AGENCIA;

CREATE TABLE IF NOT EXISTS VUELO (
  id_vuelo INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  origen VARCHAR(100) NOT NULL,
  destino VARCHAR(100) NOT NULL,
  fecha DATE NOT NULL,
  plazas_disponibles SMALLINT UNSIGNED NOT NULL,
  precio DECIMAL(12,2) UNSIGNED NOT NULL,
  CONSTRAINT chk_vuelo_plazas CHECK (plazas_disponibles >= 0),
  CONSTRAINT chk_vuelo_precio CHECK (precio >= 0),
  INDEX idx_vuelo_busqueda (origen, destino, fecha)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS HOTEL (
  id_hotel INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  ubicacion VARCHAR(150) NOT NULL,
  habitaciones_disponibles SMALLINT UNSIGNED NOT NULL,
  tarifa_noche DECIMAL(12,2) UNSIGNED NOT NULL,
  CONSTRAINT chk_hotel_habitaciones CHECK (habitaciones_disponibles >= 0),
  CONSTRAINT chk_hotel_tarifa CHECK (tarifa_noche >= 0),
  INDEX idx_hotel_ubicacion (ubicacion)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS RESERVA (
  id_reserva INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT UNSIGNED NOT NULL,
  fecha_reserva DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_vuelo INT UNSIGNED NOT NULL,
  id_hotel INT UNSIGNED NOT NULL,
  CONSTRAINT fk_reserva_vuelo FOREIGN KEY (id_vuelo) REFERENCES VUELO(id_vuelo),
  CONSTRAINT fk_reserva_hotel FOREIGN KEY (id_hotel) REFERENCES HOTEL(id_hotel),
  INDEX idx_reserva_hotel (id_hotel),
  INDEX idx_reserva_vuelo (id_vuelo)
) ENGINE=InnoDB;

-- Datos mínimos: tres vuelos, tres hoteles y diez reservas relacionadas.
INSERT IGNORE INTO VUELO (id_vuelo, origen, destino, fecha, plazas_disponibles, precio) VALUES
  (1, 'Santiago', 'Cancún', '2026-10-15', 18, 850000.00),
  (2, 'Santiago', 'Buenos Aires', '2026-11-05', 24, 230000.00),
  (3, 'Santiago', 'Río de Janeiro', '2026-12-20', 12, 990000.00);

INSERT IGNORE INTO HOTEL (id_hotel, nombre, ubicacion, habitaciones_disponibles, tarifa_noche) VALUES
  (1, 'Costa Azul Resort', 'Cancún, México', 11, 175000.00),
  (2, 'Hotel Plaza', 'Buenos Aires, Argentina', 16, 85000.00),
  (3, 'Copacabana Palace', 'Río de Janeiro, Brasil', 8, 210000.00);

INSERT IGNORE INTO RESERVA (id_reserva, id_cliente, fecha_reserva, id_vuelo, id_hotel) VALUES
  (1, 101, '2026-09-01 10:00:00', 1, 1), (2, 102, '2026-09-02 10:00:00', 1, 1),
  (3, 103, '2026-09-03 10:00:00', 1, 1), (4, 104, '2026-09-04 10:00:00', 2, 2),
  (5, 105, '2026-09-05 10:00:00', 2, 2), (6, 106, '2026-09-06 10:00:00', 2, 2),
  (7, 107, '2026-09-07 10:00:00', 3, 3), (8, 108, '2026-09-08 10:00:00', 3, 3),
  (9, 109, '2026-09-09 10:00:00', 3, 3), (10, 110, '2026-09-10 10:00:00', 1, 1);
