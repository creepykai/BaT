<?php
/**
 * Vista del catálogo de la tienda.
 * Recorre los arrays de conejos y utensilios que le pasa el controlador para crear las tarjetas HTML.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Bunnies & Tea - Tienda</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&family=Gaegu:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=2">
    <style>
        body {
            overflow-y: auto !important;
            padding-bottom: 60px;
        }
        h1 {
            text-align: center;
            margin-top: 30px;
        }
        .coins-info {
            margin: 0 auto 20px auto;
            display: block;
            text-align: center;
        }
        .tabs {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }
        .shop-section {
            display: none;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            max-width: 1000px;
            margin: 0 auto;
            padding-bottom: 40px;
        }
        .shop-section.active {
            display: flex;
        }
        a[href="index.php"].volver-btn {
            display: block;
            text-align: center;
            margin-top: 40px;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>

    <nav class="barra-navegacion" style="justify-content: space-between; padding: 0 20px; display: flex; align-items: center; width: 100%; box-sizing: border-box;">
        <div style="display: flex; align-items: center;">
            <span class="nav-logo" style="margin-right: 20px;">BaT</span>
        </div>
        <ul class="nav-links" style="list-style: none; display: flex; align-items: center; gap: 20px; margin: 0; padding: 0 15px 0 0;">
            <li><a href="index.php" style="text-decoration: none; color: #4e342e; font-weight: 700;">Cafetería</a></li>
            <li><a href="tienda.php" style="text-decoration: none; color: #4e342e; font-weight: 700;">Tienda</a></li>
        </ul>
    </nav>

    <h1> Tienda </h1>
    
    <div class="coins-info">
        Ahorros: 🪙 <span id="display-monedas-tienda"><?php echo number_format($misMonedas, 2); ?></span>
    </div>

    <?php if(!empty($mensaje)) echo $mensaje; ?>

    <div class="tabs">
        <button class="tab-btn <?php echo ($activeTab === 'personal') ? 'active' : ''; ?>" onclick="showTab('personal')">Contratar Personal</button>
        <button class="tab-btn <?php echo ($activeTab === 'utensilios') ? 'active' : ''; ?>" onclick="showTab('utensilios')">Mejorar Utensilios</button>
    </div>

    <div id="personal" class="shop-section <?php echo ($activeTab === 'personal') ? 'active' : ''; ?>">
        <?php foreach ($catalogoConejos as $c): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($c['nombre']); ?></h3>
                <div class="desc">Produce: <strong>+<?php echo $c['produccionBase']; ?></strong>/s</div>
                <div style="font-size: 0.8em;">Nivel de Formación: <?php echo $c['cantidadPoseida'] ?? 0; ?></div>
                <p><strong><?php echo $c['costeBase']; ?> 🪙</strong></p>
                <form method="POST">
                    <input type="hidden" name="conejoId" value="<?php echo $c['conejoId']; ?>">
                    <button class="btn-buy btn-compra-dinamico" data-coste="<?php echo $c['costeBase']; ?>" data-maxed="false" <?php echo ($misMonedas < $c['costeBase']) ? 'disabled' : ''; ?>>
                        <?php echo ($c['cantidadPoseida'] > 0) ? 'Subir Nivel' : 'Contratar'; ?>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="utensilios" class="shop-section <?php echo ($activeTab === 'utensilios') ? 'active' : ''; ?>">
        <?php foreach ($catalogoUtensilios as $u): ?>
            <?php $maxedOut = ($u['cantidadPoseida'] >= $u['limiteMax']) ? 'true' : 'false'; ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($u['nombre']); ?></h3>
                <div class="desc">
                    <?php if ($u['valorExtraClic'] > 0): ?>
                        Valor clic: <strong>+<?php echo $u['valorExtraClic']; ?></strong>
                    <?php endif; ?>
                    <?php if (isset($u['produccionPasivaExtra']) && $u['produccionPasivaExtra'] > 0): ?>
                        Producción: <strong>+<?php echo $u['produccionPasivaExtra']; ?>/s</strong>
                    <?php endif; ?>
                </div>
                <div style="font-size: 0.8em;"><?php echo ($maxedOut === 'true') ? '¡Ya lo tienes!' : 'Disponible'; ?></div>
                <p><strong><?php echo $u['costeBase']; ?> 🪙</strong></p>
                <form method="POST">
                    <input type="hidden" name="utensilioId" value="<?php echo $u['utensilioId']; ?>">
                    <button class="btn-buy btn-compra-dinamico" data-coste="<?php echo $u['costeBase']; ?>" data-maxed="<?php echo $maxedOut; ?>" <?php echo ($maxedOut === 'true' || $misMonedas < $u['costeBase']) ? 'disabled' : ''; ?>>Mejorar</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="index.php" class="volver-btn" style="font-family:'Gaegu'; font-size:1.5em; color:var(--primary); text-decoration: none;">⬅ Volver a la Cafetería</a>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.shop-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        setInterval(() => {
            fetch('controllers/controladorProduccionPasiva.php')
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        let dineroActual = parseFloat(data.new_balance);
                        document.getElementById('display-monedas-tienda').innerText = dineroActual.toFixed(2);
                        
                        document.querySelectorAll('.btn-compra-dinamico').forEach(boton => {
                            let coste = parseFloat(boton.getAttribute('data-coste'));
                            let isMaxed = boton.getAttribute('data-maxed') === 'true';
                            
                            if (!isMaxed && dineroActual >= coste) {
                                boton.disabled = false;
                                boton.style.opacity = '1';
                                boton.style.cursor = 'pointer';
                            } else {
                                boton.disabled = true;
                                boton.style.opacity = '0.5';
                                boton.style.cursor = 'not-allowed';
                            }
                        });

                        if (data.logros_nuevos && data.logros_nuevos.length > 0) {
                            data.logros_nuevos.forEach(logro => {
                                mostrarLogro(logro.nombre, logro.descripcion);
                            });
                        }
                    }
                })
                .catch(error => console.error("Error al sincronizar tienda:", error));
        }, 1000);

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
    </script>
    <?php if (isset($logrosDesbloqueados) && !empty($logrosDesbloqueados)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                <?php foreach ($logrosDesbloqueados as $logro): ?>
                    mostrarLogro("<?= htmlspecialchars($logro['nombre']) ?>", "<?= htmlspecialchars($logro['descripcion']) ?>");
                <?php endforeach; ?>
            });
        </script>
    <?php endif; ?>
    <div id="toast-container"></div>
</body>
</html>
