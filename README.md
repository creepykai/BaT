# Bunnies & Tea 🐰☕

Proyecto final para el módulo "Proyecto intermodular II" (2º DAW).
Bunnies & Tea es un **idle game** ambientado en una cafetería de conejos. Trata sobre gestionar una cafetería, hacer clics para ganar monedas y contratar conejos para que generen ingresos pasivos.

**Autora:** Irene Diges García  
**Curso:** 2º DAW  

### Stack Tecnológico
La estructura sigue el patrón MVC (Modelo-Vista-Controlador) y todo el backend está programado desde cero sin frameworks:

* **Backend:** PHP (Orientado a Objetos).
* **Base de Datos:** MariaDB. Utilizando transacciones SQL y sentencias preparadas mediante PDO para evitar fallos si el usuario hace muchos clics a la vez al comprar.
* **Frontend:** HTML5, CSS3 y Vanilla JS. Usa la Fetch API para que el juego se actualice en tiempo real por detrás, sin que la página parpadee ni se recargue nunca.
* **Infraestructura:** Docker y Docker Compose para levantar el servidor y la base de datos con un comando, sin instalar XAMPP.

### Cómo ejecutar el proyecto
Solo necesitas tener Docker y Git instalados en tu ordenador.

1. Clona el repositorio.
2. Abre la terminal en la carpeta raíz del proyecto.
3. Ejecuta el siguiente comando para montar y encender el servidor:
   ```bash
   docker-compose up -d --build
   ```
4. Abre tu navegador y entra en: `http://localhost:8080`

*(Si necesitas revisar la base de datos directamente, puedes entrar a phpMyAdmin en `http://localhost:8081`)*.

### Funcionalidades principales
* **Sistema de usuarios:** Registro, inicio de sesión y guardado de progreso continuo en base de datos.
* **Economía matemática:** Tienda para comprar conejos (ingresos pasivos) y utensilios (mejoran el clic). El coste de los objetos sube exponencialmente cada vez que compras uno.
* **Mecánica de limpieza (Penalización):** Los clics manuales ensucian la cafetería. Si se ensucia demasiado (50 clics sucios), la producción de los conejos baja un 50% hasta que pagues una tasa por limpiar el local.
* **Prestigio (Soft Reset):** Opción para reiniciar la partida perdiendo empleados a cambio de "Hojas de Té Doradas" que te dan un multiplicador permanente para la siguiente partida.
* **Exportar/Importar:** Botones para descargar tu partida en formato `.json` o subirla desde otro ordenador.

### Estructura de carpetas
* `/src`: Todo el código del juego. Dentro están los controladores, modelos, vistas y los archivos CSS/JS en `/assets`.
* `/db`: Contiene `init.sql`, el archivo que crea y rellena las tablas de la base de datos automáticamente al encender Docker.
* `/tests`: Pruebas unitarias hechas con PHPUnit para comprobar que las matemáticas del juego no fallan.
* `/docs`: La documentación y memoria del proyecto.
