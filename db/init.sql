-- Tablas de la base de datos
CREATE TABLE usuario (
    usuarioId INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    passwordHash VARCHAR(255) NOT NULL,
    nombreCafeteria VARCHAR(100),
    monedasActuales DECIMAL(20,2) DEFAULT 0.00,
    clicsSucios DECIMAL(10,2) DEFAULT 0.00,
    ultimoAcceso DATETIME DEFAULT CURRENT_TIMESTAMP,
    monedasHistoricas DECIMAL(20,2) DEFAULT 0.00,
    puntosLegado INT DEFAULT 0
);

-- Tabla de Conejos (La tienda de animales)
CREATE TABLE conejo (
    conejoId INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    rol VARCHAR(50),
    produccionBase DECIMAL(10,2) NOT NULL,
    costeBase DECIMAL(15,2) NOT NULL
);

-- Relación Usuario-Conejo
CREATE TABLE usuario_conejo (
    usuario_conejoId INT AUTO_INCREMENT PRIMARY KEY,
    usuarioId INT,
    conejoId INT,
    cantidad INT DEFAULT 1,
    FOREIGN KEY (usuarioId) REFERENCES usuario(usuarioId) ON DELETE CASCADE,
    FOREIGN KEY (conejoId) REFERENCES conejo(conejoId) ON DELETE CASCADE
);

-- Tabla de Utensilios (Las mejoras de tus clics)
CREATE TABLE utensilio (
    utensilioId INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    valorExtraClic DECIMAL(10,2) NOT NULL,
    costeBase DECIMAL(15,2) NOT NULL,
    limiteMax INT DEFAULT 1
);

-- Relación Usuario-Utensilio (Inventario de mejoras)
CREATE TABLE usuario_utensilio (
    usuario_utensilioId INT AUTO_INCREMENT PRIMARY KEY,
    usuarioId INT,
    utensilioId INT,
    FOREIGN KEY (usuarioId) REFERENCES usuario(usuarioId) ON DELETE CASCADE,
    FOREIGN KEY (utensilioId) REFERENCES utensilio(utensilioId) ON DELETE CASCADE
);

-- --------------------------------------------------------
-- NUEVAS TABLAS: SISTEMA DE LOGROS
-- --------------------------------------------------------

-- Tabla de Logros (Catálogo)
CREATE TABLE logro (
    logroId INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    tipoCondicion VARCHAR(50) NOT NULL, 
    valorCondicion DECIMAL(20,2) NOT NULL,
    multiplicadorRecompensa DECIMAL(5,2) NOT NULL DEFAULT 1.00
);

-- Relación Usuario-Logro (Inventario de logros desbloqueados)
CREATE TABLE usuario_logro (
    usuario_logroId INT AUTO_INCREMENT PRIMARY KEY,
    usuarioId INT,
    logroId INT,
    fechaDesbloqueo DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuarioId) REFERENCES usuario(usuarioId) ON DELETE CASCADE,
    FOREIGN KEY (logroId) REFERENCES logro(logroId) ON DELETE CASCADE
);

-- --------------------------------------------------------
-- INSERCIÓN DE DATOS INICIALES (TIENDA Y LOGROS)
-- --------------------------------------------------------

-- Tienda de Conejos
INSERT INTO conejo (nombre, rol, produccionBase, costeBase) VALUES 
('Conejo Novato', 'Camarero', 1.00, 15.00),
('Conejo Chef', 'Cocinero', 5.00, 100.00),
('Conejo Mayordomo', 'Servicio VIP', 20.00, 500.00);

-- Tienda de Utensilios
INSERT INTO utensilio (nombre, valorExtraClic, costeBase, limiteMax) VALUES 
('Cuchara de Madera', 1.00, 50.00, 1),
('Tetera de Porcelana', 5.00, 250.00, 1);

-- Logros
INSERT INTO logro (nombre, descripcion, tipoCondicion, valorCondicion, multiplicadorRecompensa) VALUES 
('Primer sorbo', 'Haz tu primer clic manual para preparar té.', 'clics', 1.00, 1.01),
('Amante del té', 'Acumula tus primeras 100 monedas históricas.', 'monedas_totales', 100.00, 1.05),
('El primer peludo', 'Contrata a tu primer conejo para la cafetería.', 'cantidad_conejos', 1.00, 1.10);