CREATE DATABASE IF NOT EXISTS homegame
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE homegame;

DROP TABLE IF EXISTS imagenes;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;

CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  descripcion TEXT,
  precio DECIMAL(10,2) NOT NULL,
  categoria_id INT NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_productos_categorias
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
) ENGINE=InnoDB;

CREATE TABLE imagenes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  producto_id INT NOT NULL,
  url VARCHAR(255) NOT NULL,
  CONSTRAINT fk_imagenes_productos
    FOREIGN KEY (producto_id) REFERENCES productos(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO categorias (nombre) VALUES
('Consolas'),
('Periféricos'),
('Monitores'),
('Audio');

INSERT INTO productos (nombre, descripcion, precio, categoria_id, stock) VALUES
('Consola Retro 8K', 'Consola portátil con más de 5000 juegos clásicos.', 249.90, 1, 15),
('Consola Pro X', 'Consola de sobremesa 4K con 1 TB de almacenamiento.', 1799.00, 1, 5),
('Control Inalámbrico', 'Control ergonómico con vibración y batería de 20 horas.', 189.50, 2, 40),
('Teclado Mecánico RGB', 'Teclado mecánico switches rojos con iluminación RGB.', 259.00, 2, 22),
('Mouse Gamer 16000 DPI', 'Mouse óptico con 7 botones programables.', 129.90, 2, 0),
('Monitor 24" 144Hz', 'Monitor Full HD IPS, 1 ms de respuesta.', 649.00, 3, 8),
('Monitor Curvo 27" 165Hz', 'Monitor curvo QHD ideal para juegos.', 1149.00, 3, 3),
('Audífonos 7.1 Surround', 'Audífonos con micrófono y sonido envolvente.', 219.00, 4, 30),
('Parlantes Bluetooth', 'Par de parlantes con 20 W de potencia.', 159.00, 4, 0),
('Micrófono USB', 'Micrófono condensador para streaming.', 175.00, 4, 12);

INSERT INTO imagenes (producto_id, url) VALUES
(1, 'https://picsum.photos/seed/consola1a/400/300'),
(1, 'https://picsum.photos/seed/consola1b/400/300'),
(2, 'https://picsum.photos/seed/consola2a/400/300'),
(2, 'https://picsum.photos/seed/consola2b/400/300'),
(3, 'https://picsum.photos/seed/control3a/400/300'),
(4, 'https://picsum.photos/seed/teclado4a/400/300'),
(4, 'https://picsum.photos/seed/teclado4b/400/300'),
(5, 'https://picsum.photos/seed/mouse5a/400/300'),
(6, 'https://picsum.photos/seed/monitor6a/400/300'),
(7, 'https://picsum.photos/seed/monitor7a/400/300'),
(7, 'https://picsum.photos/seed/monitor7b/400/300'),
(8, 'https://picsum.photos/seed/audio8a/400/300'),
(9, 'https://picsum.photos/seed/audio9a/400/300'),
(10, 'https://picsum.photos/seed/mic10a/400/300');