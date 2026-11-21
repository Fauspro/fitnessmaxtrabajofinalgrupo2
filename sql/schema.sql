CREATE DATABASE IF NOT EXISTS tienda CHARACTER SET utf8mb4;
USE tienda;
CREATE TABLE IF NOT EXISTS productos (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL, precio DECIMAL(10,2) NOT NULL, descripcion VARCHAR(255) NULL, imagen VARCHAR(255));
CREATE TABLE IF NOT EXISTS usuarios (id INT AUTO_INCREMENT PRIMARY KEY, usuario VARCHAR(50) UNIQUE NOT NULL, clave VARCHAR(255) NOT NULL, rol VARCHAR(20) NOT NULL DEFAULT 'admin');
CREATE TABLE IF NOT EXISTS planes (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(50) NOT NULL, precio DECIMAL(10,2) NOT NULL, descripcion VARCHAR(255));
CREATE TABLE IF NOT EXISTS socios (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL, dni VARCHAR(15) UNIQUE NOT NULL, email VARCHAR(100), telefono VARCHAR(30), plan_id INT NULL, estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo', clave VARCHAR(255) NULL COMMENT 'hash de la contraseña del socio (se crea al activar la cuenta)', alta TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (plan_id) REFERENCES planes(id) ON DELETE SET NULL);
CREATE TABLE IF NOT EXISTS clases (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(60) NOT NULL, dia VARCHAR(15) NOT NULL, hora TIME NOT NULL, cupo INT NOT NULL DEFAULT 15, instructor VARCHAR(80), plan_id INT NULL COMMENT 'plan mínimo requerido', FOREIGN KEY (plan_id) REFERENCES planes(id) ON DELETE SET NULL);
CREATE TABLE IF NOT EXISTS reservas (id INT AUTO_INCREMENT PRIMARY KEY, socio_id INT NOT NULL, clase_id INT NOT NULL, fecha DATE NOT NULL, estado ENUM('confirmada','cancelada') NOT NULL DEFAULT 'confirmada', FOREIGN KEY (socio_id) REFERENCES socios(id) ON DELETE CASCADE, FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS pagos (id INT AUTO_INCREMENT PRIMARY KEY, socio_id INT NOT NULL, concepto VARCHAR(100) NOT NULL, monto DECIMAL(10,2) NOT NULL, fecha DATE NOT NULL, estado ENUM('pagado','pendiente') NOT NULL DEFAULT 'pendiente', FOREIGN KEY (socio_id) REFERENCES socios(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS preguntas (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(50) NOT NULL, mensaje VARCHAR(300) NOT NULL, fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS pedidos (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL, email VARCHAR(100) NULL, telefono VARCHAR(30) NOT NULL, metodo_pago ENUM('tarjeta','efectivo') NOT NULL DEFAULT 'efectivo', tarjeta_ultimos4 CHAR(4) NULL, total DECIMAL(10,2) NOT NULL, estado ENUM('nuevo','entregado','cancelado') NOT NULL DEFAULT 'nuevo', socio_id INT NULL, fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (socio_id) REFERENCES socios(id) ON DELETE SET NULL);
CREATE TABLE IF NOT EXISTS pedido_items (id INT AUTO_INCREMENT PRIMARY KEY, pedido_id INT NOT NULL, item VARCHAR(100) NOT NULL, precio DECIMAL(10,2) NOT NULL, cantidad INT NOT NULL, FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE);

INSERT INTO planes (id,nombre,precio,descripcion) VALUES
(1,'Básico',18000,'Acceso al gimnasio · Sala de musculación · Cardio · Vestuarios'),
(2,'Premium',26000,'Todo el Plan Básico · Clases grupales · Funcional · Boxeo · Yoga'),
(3,'Full',35000,'Acceso ilimitado · Todas las clases · Nutricionista · Personal Trainer · Seguimiento mensual')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), precio=VALUES(precio), descripcion=VALUES(descripcion);

INSERT INTO clases (id,nombre,dia,hora,cupo,instructor,plan_id) VALUES
(1,'Funcional','Lunes','19:00',15,'Coach Diego',2),(2,'Spinning','Martes','20:00',12,'Coach Diego',2),
(3,'Yoga','Miércoles','18:00',15,'Coach Laura',2),(4,'Pilates','Jueves','19:00',12,'Coach Laura',2),
(5,'Boxeo','Viernes','20:00',15,'Pablo González',2),(6,'Funcional libre','Sábado','10:00',20,'Pablo González',1)
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO productos (id,nombre,precio,descripcion,imagen) VALUES
(1,'Proteína Whey',38000,'Ideal para aumentar y recuperar masa muscular.','whey.svg'),
(2,'Creatina',27000,'Mayor fuerza y mejor rendimiento en cada entrenamiento.','creatina.svg'),
(3,'Pre Entreno',24500,'Energía y concentración para entrenar al máximo.','pre.svg'),
(4,'BCAA',22000,'Favorece la recuperación muscular después del ejercicio.','bcaa.svg'),
(5,'Glutamina',21000,'Ayuda a la recuperación y al cuidado muscular.','glutamina.svg'),
(6,'Multivitamínico',15000,'Vitaminas y minerales para tu rendimiento diario.','multivitaminico.svg'),
(7,'Barras de proteína (x12)',18500,'Snack práctico con alto contenido de proteínas.','barras.svg'),
(8,'Quemador de grasa',29000,'Complemento para acompañar tu plan de definición.','quemador.svg'),
(9,'Shaker 700 ml',6500,'Mezclador con tapa antiderrame y rejilla.','shaker.svg'),
(10,'Guantes de entrenamiento',12000,'Agarre firme y protección para tus manos.','guantes.svg'),
(11,'Cinturón de levantamiento',25000,'Soporte lumbar para cargas pesadas.','cinturon.svg'),
(12,'Bandas elásticas (set x3)',9800,'Resistencia progresiva para entrenar en cualquier lugar.','bandas.svg'),
(13,'Toalla deportiva',5500,'Microfibra de secado rápido.','toalla.svg'),
(14,'Botella térmica 1 L',11000,'Mantiene tu bebida fría durante horas.','botella.svg')
ON DUPLICATE KEY UPDATE id=id;

-- Usuario inicial: admin / password  (cambiala después de entrar)
INSERT INTO usuarios (usuario,clave,rol) VALUES ('admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin') ON DUPLICATE KEY UPDATE usuario=usuario;
