<?php
require_once 'verificar_sesion.php';

require_once 'db.php';
require_once 'models/ShopManager.php';

$shop = new ShopManager($pdo);
$usuarioId = $_SESSION['usuarioId'];
$mensaje = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'models/AchievementManager.php';
    $achievements = new AchievementManager($pdo);
    
    if (isset($_POST['conejoId'])) {
        if ($shop->comprarConejo($usuarioId, $_POST['conejoId'])) {
            $mensaje = "<div class='alert success'>¡Nuevo conejo contratado! 🐰</div>";
            $nuevosLogros = $achievements->chequearLogros($usuarioId);
            if (!empty($nuevosLogros)) {
                foreach ($nuevosLogros as $l) {
                    $mensaje .= "<div class='alert success'>🏆 ¡Logro desbloqueado!: <strong>" . htmlspecialchars($l['nombre']) . "</strong> - " . htmlspecialchars($l['descripcion']) . "</div>";
                }
            }
        } else { 
            $mensaje = "<div class='alert error'>No se pudo contratar al conejo.</div>"; 
        }
    }
    
    if (isset($_POST['utensilioId'])) {
        if ($shop->comprarUtensilio($usuarioId, $_POST['utensilioId'])) {
            $mensaje = "<div class='alert success'>¡Utensilio mejorado! ☕</div>";
            $nuevosLogros = $achievements->chequearLogros($usuarioId);
            if (!empty($nuevosLogros)) {
                foreach ($nuevosLogros as $l) {
                    $mensaje .= "<div class='alert success'>🏆 ¡Logro desbloqueado!: <strong>" . htmlspecialchars($l['nombre']) . "</strong> - " . htmlspecialchars($l['descripcion']) . "</div>";
                }
            }
        } else { 
            $mensaje = "<div class='alert error'>No se pudo comprar el utensilio.</div>"; 
        }
    }
}

$catalogoConejos = $shop->getCatalogo($usuarioId);
$catalogoUtensilios = $shop->getUtensiliosCatalogo($usuarioId);

$stmt = $pdo->prepare("SELECT monedasActuales FROM usuario WHERE usuarioId = ?");
$stmt->execute([$usuarioId]);
$misMonedas = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Bunnies & Tea - Tienda</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&family=Gaegu:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary: #8d6e63; 
            --accent: #ffccbc; 
            --bg: #fff9f0; 
            --text: #4e342e; 
            --border-radius: 20px 10px 25px 15px; 
        }
        
        body { 
            font-family: 'Quicksand', sans-serif; 
            background-color: var(--bg); 
            color: var(--text); 
            text-align: center; 
            padding: 20px; 
        }
        
        .coins-info { 
            font-size: 1.5em; 
            font-weight: 700; 
            background: white; 
            display: inline-block; 
            padding: 10px 25px; 
            border: 3px solid var(--primary); 
            border-radius: var(--border-radius); 
            box-shadow: 4px 4px 0 var(--primary); 
            margin-bottom: 30px; 
        }

        .tabs { 
            display: flex; 
            justify-content: center; 
            gap: 10px; 
            margin-bottom: 30px; 
        }
        
        .tab-btn { 
            background: white; 
            border: 3px solid var(--primary); 
            padding: 10px 30px; 
            font-family: 'Gaegu', cursive; 
            font-size: 1.5em; 
            cursor: pointer; 
            border-radius: 15px; 
            transition: all 0.2s; 
        }
        
        .tab-btn.active { 
            background: var(--accent); 
            transform: translateY(-3px); 
            box-shadow: 4px 4px 0 var(--primary); 
        }

        .shop-section { 
            display: none; 
        }
        
        .shop-section.active { 
            display: flex; 
            justify-content: center; 
            gap: 25px; 
            flex-wrap: wrap; 
        }

        .card { 
            background: white; 
            border: 3px solid var(--primary); 
            padding: 20px; 
            width: 220px; 
            border-radius: var(--border-radius); 
            box-shadow: 5px 5px 0 rgba(0,0,0,0.05); 
        }
        
        .card h3 { 
            font-family: 'Gaegu', cursive; 
            font-size: 1.8em; 
            margin: 0; 
            color: var(--primary); 
        }
        
        .desc { 
            background: var(--bg); 
            padding: 10px; 
            border-radius: 10px; 
            font-size: 0.8em; 
            margin: 10px 0; 
            border: 1px dashed var(--accent); 
        }
        
        .btn-buy { 
            background: white; 
            border: 2px solid var(--primary); 
            border-radius: 10px; 
            padding: 8px; 
            width: 100%; 
            cursor: pointer; 
            font-family: 'Gaegu', cursive; 
            font-size: 1.2em; 
            font-weight: bold; 
        }
        
        .btn-buy:disabled { 
            opacity: 0.5; 
            cursor: not-allowed; 
        }

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

        .tab-btn, .btn-buy {
            touch-action: manipulation;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-tap-highlight-color: transparent; 
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 10px;
            }
            .card {
                width: 90%;
                max-width: 280px;
            }
            .coins-info {
                font-size: 1.2em;
                padding: 8px 15px;
            }
            .tab-btn {
                padding: 8px 15px;
                font-size: 1.2em;
            }
        }
    </style>
</head>
<body>

    <nav class="main-nav">
        <div class="nav-container">
            <span class="nav-logo">🐰 BaT</span>
            <ul class="nav-links">
                <li><a href="index.php">☕ Cafetería</a></li>
                <li><a href="tienda.php">🛍️ Tienda</a></li>
                <li><a href="logout.php" class="btn-logout">🚪 Salir</a></li>
            </ul>
        </div>
    </nav>

    <h1>La Tienda 🐰</h1>
    
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
    </script>
</body>
</html>