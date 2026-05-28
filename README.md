# Bunnies & Tea 🐰☕

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-10.11-003545?style=for-the-badge&logo=mariadb&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Testing](https://img.shields.io/badge/PHPUnit-100%25_Coverage-4A5B8C?style=for-the-badge&logo=phpunit&logoColor=white)

Proyecto final para el módulo "Proyecto intermodular II" (2º DAW).
Bunnies & Tea es un **idle game** ambientado en una cafetería de conejos. Trata sobre gestionar una cafetería, hacer clics para ganar monedas y contratar conejos para que generen ingresos pasivos.

**Autora:** Irene Diges García  
**Curso:** 2º DAW  

---

### Stack Tecnológico
La estructura sigue el patrón arquitectónico estricto **MVC (Modelo-Vista-Controlador)** y todo el backend está programado desde cero sin frameworks externos:

* **Backend:** PHP Nativo (Orientado a Objetos).
* **Base de Datos:** MariaDB. Utilizando transacciones ACID (`beginTransaction`/`commit`) y sentencias preparadas nativas mediante PDO (`ATTR_EMULATE_PREPARES = false`) para evitar fallos de concurrencia y blindar el sistema contra Inyecciones SQL.
* **Frontend:** HTML5, CSS3 y Vanilla JS. Usa la Fetch API para que la economía del juego se actualice en tiempo real en segundo plano, sin recargar la página en ningún momento.
* **Infraestructura:** Docker y Docker Compose para levantar el servidor web, la base de datos y un administrador visual con un solo comando, asegurando la portabilidad del entorno.

### Seguridad y Calidad del Código
* **Pruebas Unitarias (TDD):** El proyecto cuenta con un conjunto de pruebas automatizadas escritas en **PHPUnit**, ejecutadas sobre una base de datos SQLite en memoria. Se ha alcanzado un **~95% de cobertura** en la lógica de negocio (Modelos), garantizando la integridad de las matemáticas del juego, el sistema de logros y la exportación/importación de partidas.
* **Seguridad:** Autenticación de usuarios securizada mediante el algoritmo `BCRYPT` (`password_hash`). Validación cruzada cliente-servidor y saneamiento estricto de inputs para prevenir vulnerabilidades XSS.

---

### Cómo ejecutar el proyecto
Solo necesitas tener Docker y Git instalados en tu ordenador.

1. Clona el repositorio.
2. Abre la terminal en la carpeta raíz del proyecto.
3. Ejecuta el siguiente comando para montar y encender los microservicios:
   ```bash
   docker-compose up -d --build
   ```
4. Abre tu navegador y entra en: `http://localhost:8080` (Nota: El juego también es accesible por cualquier dispositivo conectado a tu misma red Wi-Fi introduciendo tu IP local).

*(Si necesitas administrar la base de datos directamente, puedes entrar a phpMyAdmin en `http://localhost:8081`)*.

---

### 🎮 Funcionalidades principales
* **Sistema de usuarios:** Registro, inicio de sesión y guardado de progreso continuo en base de datos.
* **Economía matemática:** Tienda para comprar conejos (ingresos pasivos) y utensilios (mejoran el clic). El coste de los objetos escala exponencialmente con cada compra para balancear el juego.
* **Mecánica de limpieza (Penalización):** Los clics manuales ensucian la cafetería. Si se ensucia demasiado (50 clics sucios), la producción pasiva recibe una penalización del 50% hasta que se pague la tasa de limpieza.
* **Prestigio (Soft Reset):** Opción para reiniciar la partida sacrificando empleados a cambio de "Hojas de Té Doradas", otorgando un multiplicador de ingresos permanente para la siguiente partida.
* **Logros Dinámicos:** Sistema de recompensas en tiempo real al alcanzar hitos (ej. conseguir X monedas totales o contratar Y conejos).
* **Exportar/Importar:** Botones para descargar el estado exacto de tu partida en formato `.json`, o subirla para continuar jugando desde otro ordenador.

---

### Estructura de carpetas
* `/src`: Todo el código del juego. Separado semánticamente en `controllers/`, `models/`, `views/` y los estáticos en `assets/`.
* `/db`: Contiene `init.sql`, el script que inicializa el esquema y el catálogo de la base de datos automáticamente al levantar el contenedor de MariaDB.
* `/tests`: Suite de Pruebas Unitarias.
* `/docs`: Documentación y memoria oficial del proyecto para la defensa técnica.
