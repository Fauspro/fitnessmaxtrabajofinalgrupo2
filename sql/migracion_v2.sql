-- Para bases de datos YA creadas con la versión anterior (ejecutar una sola vez en phpMyAdmin, base "tienda").
-- Requiere MariaDB (XAMPP) por el "IF NOT EXISTS" en ADD COLUMN.
USE tienda;
ALTER TABLE productos ADD COLUMN IF NOT EXISTS descripcion VARCHAR(255) NULL AFTER precio;
ALTER TABLE socios ADD COLUMN IF NOT EXISTS clave VARCHAR(255) NULL AFTER estado;
CREATE TABLE IF NOT EXISTS pedidos (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL, telefono VARCHAR(30) NOT NULL, total DECIMAL(10,2) NOT NULL, estado ENUM('nuevo','entregado','cancelado') NOT NULL DEFAULT 'nuevo', socio_id INT NULL, fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (socio_id) REFERENCES socios(id) ON DELETE SET NULL);
CREATE TABLE IF NOT EXISTS pedido_items (id INT AUTO_INCREMENT PRIMARY KEY, pedido_id INT NOT NULL, item VARCHAR(100) NOT NULL, precio DECIMAL(10,2) NOT NULL, cantidad INT NOT NULL, FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE);
UPDATE planes SET descripcion='Acceso al gimnasio · Sala de musculación · Cardio · Vestuarios' WHERE id=1;
UPDATE planes SET descripcion='Todo el Plan Básico · Clases grupales · Funcional · Boxeo · Yoga' WHERE id=2;
UPDATE planes SET descripcion='Acceso ilimitado · Todas las clases · Nutricionista · Personal Trainer · Seguimiento mensual' WHERE id=3;
-- Versión con formulario solo en el checkout y caja de preguntas en Nosotros
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS email VARCHAR(100) NULL AFTER nombre;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS metodo_pago ENUM('tarjeta','efectivo') NOT NULL DEFAULT 'efectivo' AFTER telefono;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS tarjeta_ultimos4 CHAR(4) NULL AFTER metodo_pago;
CREATE TABLE IF NOT EXISTS preguntas (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(50) NOT NULL, mensaje VARCHAR(300) NOT NULL, fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP);

-- Productos de la tienda (agrega solo los que todavía no existen, por nombre)
INSERT INTO productos (nombre,precio,descripcion,imagen)
SELECT t.n, t.p, t.d, t.i FROM (
 SELECT 'Proteína Whey' AS n, 38000 AS p, 'Ideal para aumentar y recuperar masa muscular.' AS d, 'whey.svg' AS i UNION ALL
 SELECT 'Creatina', 27000, 'Mayor fuerza y mejor rendimiento en cada entrenamiento.', 'creatina.svg' UNION ALL
 SELECT 'Pre Entreno', 24500, 'Energía y concentración para entrenar al máximo.', 'pre.svg' UNION ALL
 SELECT 'BCAA', 22000, 'Favorece la recuperación muscular después del ejercicio.', 'bcaa.svg' UNION ALL
 SELECT 'Glutamina', 21000, 'Ayuda a la recuperación y al cuidado muscular.', 'glutamina.svg' UNION ALL
 SELECT 'Multivitamínico', 15000, 'Vitaminas y minerales para tu rendimiento diario.', 'multivitaminico.svg' UNION ALL
 SELECT 'Barras de proteína (x12)', 18500, 'Snack práctico con alto contenido de proteínas.', 'barras.svg' UNION ALL
 SELECT 'Quemador de grasa', 29000, 'Complemento para acompañar tu plan de definición.', 'quemador.svg' UNION ALL
 SELECT 'Shaker 700 ml', 6500, 'Mezclador con tapa antiderrame y rejilla.', 'shaker.svg' UNION ALL
 SELECT 'Guantes de entrenamiento', 12000, 'Agarre firme y protección para tus manos.', 'guantes.svg' UNION ALL
 SELECT 'Cinturón de levantamiento', 25000, 'Soporte lumbar para cargas pesadas.', 'cinturon.svg' UNION ALL
 SELECT 'Bandas elásticas (set x3)', 9800, 'Resistencia progresiva para entrenar en cualquier lugar.', 'bandas.svg' UNION ALL
 SELECT 'Toalla deportiva', 5500, 'Microfibra de secado rápido.', 'toalla.svg' UNION ALL
 SELECT 'Botella térmica 1 L', 11000, 'Mantiene tu bebida fría durante horas.', 'botella.svg'
) t WHERE NOT EXISTS (SELECT 1 FROM productos x WHERE x.nombre = t.n);
