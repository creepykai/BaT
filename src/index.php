<?php
require_once 'verificar_sesion.php';
require_once 'db.php';
require_once 'models/GameEngine.php';

$game = new GameEngine($pdo);
$usuarioId = $_SESSION['usuarioId'];

$stmt = $pdo->prepare("SELECT email, monedasActuales, nombreCafeteria FROM usuario WHERE usuarioId = ?");
$stmt->execute([$usuarioId]);
$datosUsuario = $stmt->fetch();

$stmtC = $pdo->prepare("
    SELECT c.nombre, COUNT(uc.usuario_conejoId) as total 
    FROM conejo c
    INNER JOIN usuario_conejo uc ON c.conejoId = uc.conejoId
    WHERE uc.usuarioId = ?
    GROUP BY c.conejoId
");
$stmtC->execute([$usuarioId]);
$misConejos = $stmtC->fetchAll();

$produccionPorSegundo = $game->getProduccionTotal($usuarioId);

$stmtS = $pdo->prepare("SELECT clicsSucios FROM usuario WHERE usuarioId = ?");
$stmtS->execute([$usuarioId]);
$clicsActuales = $stmtS->fetchColumn();
$estaSucio = ($clicsActuales >= 50);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bunnies & Tea - Cafetería</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&family=Gaegu:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #8d6e63;
            --accent: #ffccbc;
            --bg: #fff9f0;
            --text: #4e342e;
            --success: #81c784;
            --card-bg: #ffffff;
            --border-radius: 25px 15px 30px 10px;
        }

        body { 
            font-family: 'Quicksand', sans-serif; 
            background-color: var(--bg);
            background-image: radial-gradient(#d7ccc8 0.5px, transparent 0.5px);
            background-size: 20px 20px;
            color: var(--text);
            margin: 0; overflow: hidden; 
        }


        .nav-bar { 
            position: fixed; top: 0; width: 100%; height: 80px;
            background: white; border-bottom: 3px solid var(--primary);
            display: flex; justify-content: space-between; align-items: center; z-index: 100;
            border-radius: 0 0 20px 20px;
        }

        .coins-display { padding-left: 30px; }
        .coin-amount { font-size: 1.8em; font-weight: 700; color: var(--primary); vertical-align: middle; }
        

        .coin-ui {
            display: inline-block; width: 28px; height: 28px; background: #ffd54f;
            border: 2px solid #f9a825; border-radius: 50%; vertical-align: middle;
            margin-right: 8px; position: relative; box-shadow: 2px 2px 0 #bc8a5f;
        }
        .coin-ui::after { content: '$'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 16px; color: #f9a825; font-weight: bold; }

        .pps-tag { font-size: 0.9em; color: var(--success); font-weight: 600; margin-left: 35px; margin-top: -5px; }

        .nav-buttons { padding-right: 30px; display: flex; gap: 15px; }
        .nav-btn { 
            background: var(--accent); border: 3px solid var(--primary); 
            border-radius: var(--border-radius); padding: 8px 20px; cursor: pointer; 
            text-decoration: none; color: var(--text); font-weight: 700;
            box-shadow: 3px 3px 0 var(--primary); font-family: 'Gaegu', cursive; font-size: 1.2em;
        }


        .game-canvas { 
            margin-top: 80px; height: calc(100vh - 80px); width: 100%;
            background: #e9edc9; position: relative; 
            display: flex; justify-content: center; align-items: center;
        }


        .stats-personal {
            position: absolute; top: 30px; left: 30px; 
            background: white; padding: 20px; 
            border-radius: var(--border-radius); border: 3px solid var(--primary);
            min-width: 180px; box-shadow: 5px 5px 0 rgba(141, 110, 99, 0.1);
        }
        .stats-personal h3 { margin: 0 0 10px 0; font-size: 1.4em; }


        #clicker-btn {
            position: absolute; bottom: 40px; right: 40px;
            width: 140px; height: 140px; background: var(--accent);
            border-radius: 50%; border: 4px solid var(--primary); 
            display: flex; justify-content: center; align-items: center;
            cursor: pointer; box-shadow: 6px 6px 0px var(--primary); transition: all 0.1s;
        }
        #clicker-btn:active { transform: translate(3px, 3px); box-shadow: 2px 2px 0px var(--primary); }

        .btn-label { font-family: 'Gaegu', cursive; font-weight: 700; font-size: 1.6em; color: var(--primary); text-align: center; }


        .notification { 
            position: absolute; bottom: 40px; left: 40px; 
            background: white; padding: 15px 30px; border-radius: 50px; 
            border: 3px solid var(--primary); opacity: 0; 
            transform: translateY(30px); transition: all 0.4s; font-family: 'Gaegu', cursive; font-size: 1.4em;
            z-index: 999; pointer-events: none;
        }
        .notification.show { opacity: 1; transform: translateY(0); }

        .main-nav {
            background-color: #ffffff;
            padding: 10px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        .nav-logo {
            font-size: 1.5em;
            font-weight: bold;
            color: #8d6e63;
        }
        .nav-links {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
        }
        .nav-links a {
            text-decoration: none;
            color: #4e342e;
            font-weight: 500;
        }
        .nav-links a:hover {
            color: #8d6e63;
        }
        .btn-logout {
            color: #e57373 !important;
        }


        .modal-oculto { 
            display: none; 
        }

        .modal-activo { 
            display: flex !important; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100vw; 
            height: 100vh; 
            background: rgba(0, 0, 0, 0.7); 
            z-index: 9999; 
            justify-content: center; 
            align-items: center; 
        }

        .modal-contenido { 
            background: #fffafa; 
            padding: 30px; 
            border-radius: 20px; 
            text-align: center; 
            width: 320px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.5); 
            border: 4px solid #8d6e63;
        }

        .btn-config {
            display: block; 
            width: 100%; 
            box-sizing: border-box;
            padding: 12px; 
            margin: 10px 0;
            border: none; 
            border-radius: 10px; 
            font-weight: bold; 
            font-family: 'Gaegu', cursive; 
            font-size: 1.2em;
            line-height: 1.2;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: background 0.3s;
        }

        .btn-peligro { background-color: #ff8a80; color: #fff; }
        .btn-peligro:hover { background-color: #e57373; }

        .btn-salir { background-color: #8d6e63; color: white; }

        .btn-volver { background-color: #d7ccc8; color: #4e342e; margin-top: 20px; }
        .btn-volver:hover { background-color: #bdbdbd; }

        #btn-ajustes {
            background: none;
            border: none;
            font-size: 1.8em; 
            cursor: pointer;
            padding: 5px;
            margin-left: 5px; 
            display: inline-flex;
            align-items: center;
            justify-content: center;
            vertical-align: middle;
            transition: transform 0.2s;
            z-index: 101; 
            width: 45px;
            height: 45px;
            overflow: visible;
        }

        #btn-ajustes:hover {
            transform: rotate(45deg); 
        }

        @keyframes palpitarError {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        #aviso-penalizacion {
            animation: palpitarError 1.5s infinite;
        }

    </style>
</head>
<body>

    <nav class="nav-bar" style="justify-content: space-between; padding: 0 20px;">
        <div style="display: flex; align-items: center;">
            <span class="nav-logo" style="margin-right: 20px;">🐰 BaT</span>
            <div class="coins-display" style="padding-left: 0;">
                <span class="coin-ui"></span>
                <span id="monedas-total" class="coin-amount"><?php echo number_format($datosUsuario['monedasActuales'], 2); ?></span>
                <div class="pps-tag" style="margin-left: 35px; margin-top: -5px;">
                    Producción: +<span id="pps-display"><?php echo number_format($produccionPorSegundo, 2); ?></span>/s
                    <span id="aviso-penalizacion" style="display: none; color: #e57373; font-size: 0.8em; font-weight: bold; margin-left: 10px; background: #ffebee; padding: 2px 8px; border-radius: 10px;">¡-50% 📉!</span>
                </div>
            </div>
        </div>
        <ul class="nav-links" style="list-style: none; display: flex; align-items: center; gap: 20px; margin: 0; padding: 0 15px 0 0;">
            <li><a href="index.php" style="text-decoration: none; color: #4e342e; font-weight: 700;">☕ Cafetería</a></li>
            <li><a href="tienda.php" style="text-decoration: none; color: #4e342e; font-weight: 700;">🛍️ Tienda</a></li>
            <li><button id="btn-ajustes">⚙️</button></li>
        </ul>
    </nav>

    <main class="game-canvas">
        
        <div class="stats-personal">
            <h3>Tu Equipo</h3>
            <?php if (empty($misConejos)): ?>
                <p style="font-size: 0.9em; opacity: 0.7;">No hay personal trabajando.</p>
            <?php else: ?>
                <?php foreach($misConejos as $conejito): ?>
                    <div style="margin-bottom: 8px;">
                        <strong>x<?php echo $conejito['total']; ?></strong> <?php echo htmlspecialchars($conejito['nombre']); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="status-limpieza" class="stats-personal" style="top: 220px; border-color: <?php echo $estaSucio ? '#e57373' : '#81c784'; ?>">
            <p style="margin: 0 0 5px 0;">Estado: <strong id="texto-suciedad"><?php echo $estaSucio ? "¡MUY SUCIO!" : "Limpio"; ?></strong></p>
            <div style="background: #eee; height: 10px; border-radius: 5px;">
                <div id="barra-suciedad" style="background: var(--primary); height: 100%; width: <?php echo min(100, ($clicsActuales/50)*100); ?>%; transition: 0.3s; max-width: 100%;"></div>
            </div>
            <button id="btn-limpiar" class="nav-btn" style="width: 100%; margin-top: 10px; display: <?php echo $estaSucio ? 'block' : 'none'; ?>">
                Limpiar Cafetería (Gratis)
            </button>
        </div>

        <div id="bunny-container" style="font-size: 100px;">
             </div> 
        
        <div id="venta-notif" class="notification">¡Venta!</div>

        <div id="clicker-btn">
            <div class="btn-label">SERVIR<br>TÉ</div>
        </div> 
    </main>

    <div id="modal-ajustes" class="modal-oculto">
        <div class="modal-contenido">
            <h2 style="color: #4e342e;">⚙️ Configuración</h2>
            <hr style="border-top: 1px solid #d7ccc8; margin-bottom: 20px;">
            
            <button id="btn-reiniciar-partida" class="btn-config btn-peligro">🗑️ Reiniciar Partida</button>
            <a href="logout.php" class="btn-config btn-salir">🚪 Cerrar Sesión</a>
            
            <button id="btn-cerrar-ajustes" class="btn-config btn-volver">Volver al Juego</button>
        </div>
    </div>

    <script>
        const displayMonedas = document.getElementById('monedas-total');
        const botonClicker = document.getElementById('clicker-btn');
        const notificacion = document.getElementById('venta-notif');

        botonClicker.addEventListener('click', () => {
            actualizarServidor();
        });

        document.getElementById('btn-limpiar').addEventListener('click', () => {
            fetch('controllers/cleanController.php').then(() => {
                location.reload(); 
            });
        });

        function actualizarServidor() {
            fetch('controllers/coinController.php', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    displayMonedas.innerText = parseFloat(data.new_balance).toFixed(2);
                    animarNotificacion(data.cantidad_ganada);

                    const clics = data.clics_sucios;
                    const barra = document.getElementById('barra-suciedad');
                    const btnLimpiar = document.getElementById('btn-limpiar');
                    const texto = document.getElementById('texto-suciedad');
                    const panel = document.getElementById('status-limpieza');

                    const porcentaje = Math.min((clics / 50) * 100, 100);
                    barra.style.width = porcentaje + "%";

                    if (clics >= 50) {
                        btnLimpiar.style.display = 'block';
                        texto.innerText = "¡MUY SUCIO!";
                        panel.style.borderColor = '#e57373';
                    }
                }
            });
        }

        function animarNotificacion(cantidad) {
            notificacion.innerText = "¡Venta! +" + parseFloat(cantidad).toFixed(2);
            notificacion.classList.add('show');
            setTimeout(() => { notificacion.classList.remove('show'); }, 1000);
        }


        setInterval(() => {
            fetch('controllers/passiveProductionController.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    displayMonedas.innerText = parseFloat(data.new_balance).toFixed(2);
                    
                    if (data.current_pps !== undefined) {
                        document.getElementById('pps-display').innerText = parseFloat(data.current_pps).toFixed(2);
                    }

                    if (data.clics_sucios !== undefined) {
                        const clics = data.clics_sucios;
                        const barra = document.getElementById('barra-suciedad');
                        const btnLimpiar = document.getElementById('btn-limpiar');
                        const texto = document.getElementById('texto-suciedad');
                        const panel = document.getElementById('status-limpieza');

                        const porcentaje = Math.min((clics / 50) * 100, 100);
                        barra.style.width = porcentaje + "%";

                        if (clics >= 50) {
                            btnLimpiar.style.display = 'block';
                            texto.innerText = "¡MUY SUCIO!";
                            panel.style.borderColor = '#e57373';
                            document.getElementById('aviso-penalizacion').style.display = 'inline-block';
                        } else {
                            btnLimpiar.style.display = 'none';
                            texto.innerText = "Limpio";
                            panel.style.borderColor = '#81c784';
                            document.getElementById('aviso-penalizacion').style.display = 'none';
                        }
                    }
                }
            });
        }, 1000);

        const modalAjustes = document.getElementById('modal-ajustes');
        document.getElementById('btn-ajustes').addEventListener('click', () => modalAjustes.classList.add('modal-activo'));
        document.getElementById('btn-cerrar-ajustes').addEventListener('click', () => modalAjustes.classList.remove('modal-activo'));

        document.getElementById('btn-reiniciar-partida').addEventListener('click', () => {
            const confirmar = confirm("⚠️ ¿Estás segura de que quieres empezar de cero? Perderás todos tus empleados y monedas.");
            
            if (confirmar) {
                fetch('controllers/resetController.php', { method: 'POST' })
                    .then(respuesta => respuesta.json())
                    .then(data => {
                        if (data.status === 'success') {
                            window.location.reload();
                        } else {
                            alert("Hubo un error al reiniciar la partida.");
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        alert("Error de conexión al reiniciar.");
                    });
            }
        });
    </script>
</body>
</html>