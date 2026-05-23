<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Bunnies & Tea - Cafetería</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&family=Gaegu:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="<?php echo $estaSucio ? 'cafeteria-sucia' : ''; ?>">

    <nav class="nav-bar" style="justify-content: space-between; padding: 0 20px;">
        <div style="display: flex; align-items: center;">
            <span class="nav-logo" style="margin-right: 20px;">BaT</span>
            <div class="coins-display" style="padding-left: 0;">
                <span class="coin-ui"></span>
                <span id="monedas-total" class="coin-amount"><?php echo number_format($datosUsuario['monedasActuales'], 2); ?></span>
                <div class="pps-tag" style="margin-left: 35px; margin-top: -5px;">
                    Producción: +<span id="pps-display"><?php echo number_format($produccionPorSegundo, 2); ?></span>/s
                </div>
            </div>
            <?php if ($puntosLegado > 0): ?>
                <div style="display: inline-block; margin-left: 20px; color: #81C784; font-weight: bold; font-family: Arial, sans-serif; font-size: 14px;">
                    <?= htmlspecialchars($puntosLegado) ?> Hojas (x<?= number_format(1 + ($puntosLegado * 0.05), 2) ?> Bonus)
                </div>
            <?php endif; ?>
        </div>
        <ul class="nav-links" style="list-style: none; display: flex; align-items: center; gap: 20px; margin: 0; padding: 0 15px 0 0;">
            <li><a href="index.php" style="text-decoration: none; color: #4e342e; font-weight: 700;">Cafetería</a></li>
            <li><a href="tienda.php" style="text-decoration: none; color: #4e342e; font-weight: 700;">Tienda</a></li>
            <li><button id="btn-ajustes">⚙️</button></li>
        </ul>
    </nav>

    <main class="game-canvas">
        <div id="aviso-penalizacion" style="display: <?php echo $estaSucio ? 'block' : 'none'; ?>;">
            ¡La cafetería está muy sucia! La producción de los conejos se ha reducido a la mitad.
            <button id="btn-limpiar">Limpiar (20 🪙)</button>
        </div>
        
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
            
            <div class="grafico-contenedor" style="width: 100%; max-width: 300px; margin: 20px auto;">
                <h3 style="text-align: center; color: #5D4037; font-size: 16px; margin-bottom: 15px;">Rendimiento de Conejos</h3>
                <canvas id="graficoProduccion"></canvas>
            </div>
            
            <button id="btn-reiniciar-partida" class="btn-config btn-peligro">Reiniciar Partida</button>
            <a href="controllers/exportController.php" class="btn-config" style="background-color: #5d4037; color: white;">Descargar Partida (.json)</a>
            <a href="logout.php" class="btn-config btn-salir">Cerrar Sesión</a>
            
            <button id="btn-cerrar-ajustes" class="btn-config btn-volver">Volver al Juego</button>
        </div>
    </div>

    <script>
        const displayMonedas = document.getElementById('monedas-total');
        const botonClicker = document.getElementById('clicker-btn');
        const notificacion = document.getElementById('venta-notif');

        let clicsPendientes = 0;
        let valorClicVisual = parseFloat(<?php echo $valorClicActual; ?>);
        let monedasVisuales = parseFloat(<?php echo $datosUsuario['monedasActuales']; ?>);

        botonClicker.addEventListener('click', () => {
            clicsPendientes++;
            monedasVisuales += valorClicVisual;
            displayMonedas.innerText = monedasVisuales.toFixed(2);
            animarNotificacion(valorClicVisual);
        });

        document.getElementById('btn-limpiar').addEventListener('click', () => {
            fetch('controllers/cleanController.php', {
                method: 'POST'
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    monedasVisuales = parseFloat(data.new_balance);
                    displayMonedas.innerText = monedasVisuales.toFixed(2);
                    
                    document.body.classList.remove('cafeteria-sucia');
                    document.getElementById('aviso-penalizacion').style.display = 'none';
                    
                    const barra = document.getElementById('barra-suciedad');
                    const texto = document.getElementById('texto-suciedad');
                    const panel = document.getElementById('status-limpieza');
                    barra.style.width = "0%";
                    texto.innerText = "Limpio";
                    panel.style.borderColor = '#81c784';
                } else {
                    alert("No puedes limpiar: " + data.message);
                }
            })
            .catch(error => console.error(error));
        });

        function animarNotificacion(cantidad) {
            notificacion.innerText = "¡Venta! +" + parseFloat(cantidad).toFixed(2);
            notificacion.classList.add('show');
            setTimeout(() => { notificacion.classList.remove('show'); }, 1000);
        }

        setInterval(() => {
            if (clicsPendientes > 0) {
                let clicsAEnviar = clicsPendientes;
                clicsPendientes = 0; 

                fetch('controllers/coinController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ clics: clicsAEnviar })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        monedasVisuales = parseFloat(data.new_balance);
                        displayMonedas.innerText = monedasVisuales.toFixed(2);
                        
                        if (data.clics_sucios !== undefined) {
                            const clics = data.clics_sucios;
                            const barra = document.getElementById('barra-suciedad');
                            const texto = document.getElementById('texto-suciedad');
                            const panel = document.getElementById('status-limpieza');

                            const porcentaje = Math.min((clics / 50) * 100, 100);
                            barra.style.width = porcentaje + "%";

                            if (clics >= 50) {
                                texto.innerText = "¡MUY SUCIO!";
                                panel.style.borderColor = '#e57373';
                                document.getElementById('aviso-penalizacion').style.display = 'block';
                                document.body.classList.add('cafeteria-sucia');
                            } else {
                                texto.innerText = "Limpio";
                                panel.style.borderColor = '#81c784';
                                document.getElementById('aviso-penalizacion').style.display = 'none';
                                document.body.classList.remove('cafeteria-sucia');
                            }
                        }

                        if (data.logros_nuevos && data.logros_nuevos.length > 0) {
                            data.logros_nuevos.forEach(logro => {
                                mostrarLogro(logro.nombre, logro.descripcion);
                            });
                        }
                    }
                })
                .catch(error => console.error(error));
            }
        }, 3000);

        setInterval(() => {
            fetch('controllers/passiveProductionController.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (clicsPendientes === 0) {
                        monedasVisuales = parseFloat(data.new_balance);
                        displayMonedas.innerText = monedasVisuales.toFixed(2);
                    } else {
                        const diff = parseFloat(data.new_balance) - (monedasVisuales - (clicsPendientes * valorClicVisual));
                        if (diff > 0) {
                            monedasVisuales += diff;
                            displayMonedas.innerText = monedasVisuales.toFixed(2);
                        }
                    }
                    
                    if (data.current_pps !== undefined) {
                        document.getElementById('pps-display').innerText = parseFloat(data.current_pps).toFixed(2);
                    }

                    if (data.clics_sucios !== undefined) {
                        const clics = data.clics_sucios;
                        const barra = document.getElementById('barra-suciedad');
                        const texto = document.getElementById('texto-suciedad');
                        const panel = document.getElementById('status-limpieza');

                        const porcentaje = Math.min((clics / 50) * 100, 100);
                        barra.style.width = porcentaje + "%";

                        if (clics >= 50) {
                            texto.innerText = "¡MUY SUCIO!";
                            panel.style.borderColor = '#e57373';
                            document.getElementById('aviso-penalizacion').style.display = 'block';
                            document.body.classList.add('cafeteria-sucia');
                        } else {
                            texto.innerText = "Limpio";
                            panel.style.borderColor = '#81c784';
                            document.getElementById('aviso-penalizacion').style.display = 'none';
                            document.body.classList.remove('cafeteria-sucia');
                        }
                    }
                }
            });
        }, 1000);

        const modalAjustes = document.getElementById('modal-ajustes');
        document.getElementById('btn-ajustes').addEventListener('click', () => {
            modalAjustes.classList.add('modal-activo');
            renderizarGrafico();
        });
        document.getElementById('btn-cerrar-ajustes').addEventListener('click', () => modalAjustes.classList.remove('modal-activo'));

        document.getElementById('btn-reiniciar-partida').addEventListener('click', () => {
            const confirmar = confirm("¿Estás segura de que quieres empezar de cero? Perderás todos tus empleados y monedas.");
            
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
                        console.error(error);
                        alert("Error de conexión al reiniciar.");
                    });
            }
        });
        function mostrarLogro(nombre, descripcion) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'toast-logro';
            toast.innerHTML = `<strong>¡LOGRO DESBLOQUEADO!</strong><br><span style="font-size: 14px;">${nombre}: ${descripcion}</span>`;
            
            container.appendChild(toast);

            setTimeout(() => toast.classList.add('show'), 10);

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }

        let graficoProduccionObj = null;

        function renderizarGrafico() {
            fetch('controllers/statsController.php')
                .then(res => res.json())
                .then(response => {
                    if (response.status !== 'success') return;

                    const ctx = document.getElementById('graficoProduccion');
                    if (!ctx) return;

                    let labels = ['Sin empleados'];
                    let datos = [1];
                    let colores = ['#e0e0e0'];

                    if (response.data && response.data.length > 0) {
                        labels = response.data.map(item => item.nombre);
                        datos = response.data.map(item => parseFloat(item.produccionTotal));
                        colores = ['#5D4037', '#8D6E63', '#D7CCC8', '#A1887F', '#3E2723', '#FFC107', '#81C784', '#FF8A65'];
                    }

                    if (graficoProduccionObj) {
                        graficoProduccionObj.destroy();
                    }

                    graficoProduccionObj = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: datos,
                                backgroundColor: colores,
                                borderWidth: 2,
                                borderColor: '#fffafa'
                            }]
                        },
                        options: {
                            responsive: true,
                            cutout: '75%',
                            plugins: {
                                legend: { 
                                    position: 'bottom',
                                    labels: { font: { family: 'Arial', size: 12 } }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error(error));
        }
    </script>
    <div id="toast-container"></div>
</body>
</html>
