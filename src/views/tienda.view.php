<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Bunnies & Tea - Tienda</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&family=Gaegu:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <nav class="main-nav">
        <div class="nav-container">
            <span class="nav-logo">BaT</span>
            <ul class="nav-links">
                <li><a href="index.php">Cafetería</a></li>
                <li><a href="tienda.php">Tienda</a></li>
                <li><a href="logout.php" class="btn-logout">Salir</a></li>
            </ul>
        </div>
    </nav>

    <h1> Tienda </h1>
    
    <div class="coins-info">
        Ahorros: 🪙 <span id="display-monedas-tienda"><?php echo number_format($misMonedas, 2); ?></span>
    </div>

    <?php echo $mensaje; ?>

    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('personal')">Contratar Personal</button>
        <button class="tab-btn" onclick="showTab('utensilios')">Mejorar Utensilios</button>
    </div>

    <div id="personal" class="shop-section active">
        <?php foreach ($catalogoConejos as $c): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($c['nombre']); ?></h3>
                <div class="desc">Produce: <strong>+<?php echo $c['produccionBase']; ?></strong>/s</div>
                <div style="font-size: 0.8em;">Posees: <?php echo $c['cantidadPoseida'] ?? 0; ?></div>
                <p><strong><?php echo $c['costeBase']; ?> 🪙</strong></p>
                <form method="POST">
                    <input type="hidden" name="conejoId" value="<?php echo $c['conejoId']; ?>">
                    <button class="btn-buy" <?php echo ($misMonedas < $c['costeBase']) ? 'disabled' : ''; ?>>Contratar</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="utensilios" class="shop-section">
        <?php foreach ($catalogoUtensilios as $u): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($u['nombre']); ?></h3>
                <div class="desc">Valor clic: <strong>+<?php echo $u['valorExtraClic']; ?></strong></div>
                <div style="font-size: 0.8em;"><?php echo ($u['cantidadPoseida'] >= 1) ? '¡Ya lo tienes!' : 'Disponible'; ?></div>
                <p><strong><?php echo $u['costeBase']; ?> 🪙</strong></p>
                <form method="POST">
                    <input type="hidden" name="utensilioId" value="<?php echo $u['utensilioId']; ?>">
                    <button class="btn-buy" <?php echo ($u['cantidadPoseida'] >= $u['limiteMax'] || $misMonedas < $u['costeBase']) ? 'disabled' : ''; ?>>Mejorar</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="index.php" style="display:block; margin-top:40px; font-family:'Gaegu'; font-size:1.5em; color:var(--primary);">⬅ Volver</a>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.shop-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        setInterval(() => {
            fetch('produccion_pasiva.php')
                .then(r => r.json())
                .then(d => {
                    document.getElementById('display-monedas-tienda').innerText = parseFloat(d.new_balance).toFixed(2);
                });
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
