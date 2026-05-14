-- Tablas de la base de datos
CREATE TABLE usuario (
    usuarioId INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    passwordHash VARCHAR(255) NOT NULL,
    nombreCafeteria VARCHAR(100),
    monedasActuales DECIMAL(20,2) DEFAULT 0.00,
    clicsSucios DECIMAL(10,2) DEFAULT 0.00,
    ultimoAcceso DATETIME DEFAULT CURRENT_TIMESTAMP
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
    FOREIGN KEY (usuarioId) REFERENCES usuario(usuarioId),
    FOREIGN KEY (conejoId) REFERENCES conejo(conejoId)
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
    FOREIGN KEY (usuarioId) REFERENCES usuario(usuarioId),
    FOREIGN KEY (utensilioId) REFERENCES utensilio(utensilioId)
);

-- Inserción de datos iniciales para la tienda
INSERT INTO conejo (nombre, rol, produccionBase, costeBase) VALUES ('Conejo Novato', 'Camarero', 1.00, 10.00);
INSERT INTO conejo (nombre, rol, produccionBase, costeBase) VALUES ('Conejo Experto', 'Barista', 5.00, 50.00);
INSERT INTO utensilio (nombre, valorExtraClic, costeBase, limiteMax) VALUES ('Taza de Porcelana', 0.50, 20.00, 5);
INSERT INTO utensilio (nombre, valorExtraClic, costeBase, limiteMax) VALUES ('Tetera Dorada', 2.00, 100.00, 1);