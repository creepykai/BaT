# Bunnies & Tea 🐰☕

Este es mi proyecto para el módulo Proyecto intermodular II. He desarrollado un **idle game** ambientado en una cafetería de conejos. El objetivo principal ha sido crear una aplicación web completa, desde la base de datos hasta el despliegue.

**Autora:** Irene Diges García  
**Curso:** 2º DAW  
**Repositorio:** https://github.com/creepykai/BaT


### Stack Tecnológico
He organizado el código siguiendo el patrón **MVC** (Modelo-Vista-Controlador) para que sea escalable y fácil de mantener:

* **Backend:** PHP (Programación Orientada a Objetos).
* **Base de Datos:** MariaDB. He implementado transacciones SQL para asegurar que las compras en la tienda sean atómicas y no haya errores de saldo.
* **Frontend:** HTML5, CSS3 y JavaScript vanilla. Utilizo **Fetch API** para gestionar las peticiones asíncronas (AJAX), permitiendo que el juego progrese en tiempo real sin recargar la página.
* **Despliegue:** Todo el entorno está contenedorizado con **Docker** y Docker Compose (Apache + MariaDB).

###  Cómo ejecutar el proyecto
Si tienes Docker instalado, puedes levantar el proyecto en segundos:

1. Clona el repositorio.
2. Abre una terminal en la carpeta raíz del proyecto.
3. Ejecuta el comando:
   bash
   docker-compose up -d

4. Accede en tu navegador a: `http://localhost`

###  Funcionalidades actuales
* **Gestión de Usuarios:** Registro y login con sesiones seguras y contraseñas encriptadas mediante `password_hash`.
* **Tienda Dinámica:** Compra de distintos tipos de conejos (producción automática) y utensilios (mejora del clic manual).
* **Seguridad Económica:** Todas las validaciones de dinero se hacen en el servidor para evitar que se pueda trucar el saldo desde la consola de JavaScript.
* **Sistema de Estado (.state):** Permite exportar e importar el progreso del jugador en un archivo local.
* **Mecánica de Limpieza:** Si no se limpia la cafetería, la suciedad acumulada penaliza la producción de monedas un 50%.

###  Estructura de archivos
* **`/src`**: Contiene la lógica del negocio (Modelos, Vistas y Controladores).
* **`/public`**: Archivos estáticos como el CSS, los scripts de JS y los recursos gráficos.
* **`/database`**: Script `.sql` con la estructura de tablas y los datos iniciales.
* **`docker-compose.yml`**: Configuración de los servicios de Docker.
