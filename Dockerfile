# 1. Imagen base
FROM php:8.2-apache

# 2. Instalar herramientas del sistema operativo necesarias (unzip, git y sqlite)
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libsqlite3-dev \
    sqlite3 \
    && rm -rf /var/lib/apt/lists/*

# 3. Instalar las extensiones de PHP (MySQL para tu juego, SQLite para los tests)
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite

# 4. Habilitar la reescritura de URLs en Apache
RUN a2enmod rewrite

# 5. ¡LA MAGIA! Traemos Composer ya instalado desde su imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer