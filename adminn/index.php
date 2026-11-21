<?php
session_start();

// Verificar que el usuario haya iniciado sesión como administrador
if (
    !isset($_SESSION["usuario"]) ||
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "admin"
) {
    header("Location: login.php");
    exit();
}
include("config/bacap.php");
$q = function ($sql) use ($conexion) { try { return (float)$conexion->query($sql)->fetchColumn(); } catch (Exception $e) { return 0; } };
$nSocios = (int)$q("SELECT COUNT(*) FROM socios WHERE estado='activo'");
$nPlanes = (int)$q("SELECT COUNT(*) FROM planes");
$nReservas = (int)$q("SELECT COUNT(*) FROM reservas WHERE estado='confirmada' AND fecha >= CURDATE()");
$ingresos = $q("SELECT COALESCE(SUM(monto),0) FROM pagos WHERE estado='pagado' AND MONTH(fecha)=MONTH(CURDATE()) AND YEAR(fecha)=YEAR(CURDATE())");

// ALERTAS: morosos (pagos pendientes) y vencimientos (la cuota se considera vigente 30 días desde el último pago)
try {
    $morosos = $conexion->query("SELECT s.nombre, s.telefono, COUNT(p.id) AS cuotas, SUM(p.monto) AS deuda, MIN(p.fecha) AS desde
        FROM pagos p JOIN socios s ON s.id = p.socio_id
        WHERE p.estado = 'pendiente' AND s.estado = 'activo'
        GROUP BY s.id, s.nombre, s.telefono ORDER BY desde")->fetchAll(PDO::FETCH_ASSOC);
    $ultimos = $conexion->query("SELECT s.nombre, s.telefono, MAX(p.fecha) AS ultimo
        FROM socios s LEFT JOIN pagos p ON p.socio_id = s.id AND p.estado = 'pagado'
        WHERE s.estado = 'activo'
        GROUP BY s.id, s.nombre, s.telefono")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $morosos = []; $ultimos = []; }
$vencimientos = [];
foreach ($ultimos as $u) {
    if ($u["ultimo"] === null) { $vencimientos[] = $u + ["texto" => "Sin pagos registrados", "orden" => -9999]; continue; }
    $restan = 30 - (int)floor((strtotime(date("Y-m-d")) - strtotime($u["ultimo"])) / 86400);
    if ($restan < 0) { $vencimientos[] = $u + ["texto" => "Cuota vencida hace " . abs($restan) . " día(s)", "orden" => $restan]; }
    elseif ($restan <= 5) { $vencimientos[] = $u + ["texto" => $restan === 0 ? "La cuota vence hoy" : "Vence en $restan día(s)", "orden" => $restan]; }
}
usort($vencimientos, fn($a, $b) => $a["orden"] <=> $b["orden"]);
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Panel Admin | Fitness Max</title>

    <link rel="stylesheet" href="css/admin.css">

    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

</head>

<body>

<div class="admin-container">

    <!-- SIDEBAR -->

    <aside class="sidebar" id="sidebar">

        <div class="logo">
            FITNESS <span>MAX</span>
        </div>

        <div class="admin-user">

            <div class="avatar">
                👤
            </div>

            <div>
                <strong>Administrador</strong>
                <small>Panel de control</small>
            </div>

        </div>


        <nav class="menu">

            <a href="index.php" class="active">
                📊 Dashboard
            </a>

            <a href="seccion/socios.php">
                👥 Usuarios
            </a>

            <a href="seccion/planes.php">
                💳 Planes
            </a>

            <a href="seccion/clases.php">
                🏋️ Clases
            </a>

            <a href="seccion/reservas.php">
                📅 Reservas
            </a>

            <a href="seccion/pagos.php">
                💰 Pagos
            </a>

            <a href="seccion/productos.php">
                📦 Productos
            </a>

            <a href="seccion/pedidos.php">
                🛒 Pedidos
            </a>

            <a href="seccion/mensajes.php">
                ✉️ Preguntas
            </a>

            <a href="seccion/configuracion.php">
                ⚙️ Configuración
            </a>

        </nav>


        <div class="sidebar-bottom">

            <a href="../index.html">
                🌐 Ver sitio
            </a>

            <a href="cerrar.php" class="logout">
                🚪 Cerrar sesión
            </a>

        </div>

    </aside>


    <!-- CONTENIDO -->

    <main class="main">

        <!-- HEADER -->

        <header class="topbar">

            <button class="menu-button" onclick="toggleMenu()">
                ☰
            </button>

            <div>
                <span class="top-label">FITNESS MAX</span>
                <h1>Panel de administración</h1>
            </div>

            <div class="top-user">
                <span>Administrador</span>
                <div class="avatar-small">
                    👤
                </div>
            </div>

        </header>


        <!-- DASHBOARD -->

        <section class="content">

            <div class="welcome">

                <div>
                    <span>ADMINISTRACIÓN</span>

                    <h2>Bienvenido 👋</h2>

                    <p>
                        Desde este panel podés administrar Fitness Max.
                    </p>
                </div>

                <div class="welcome-icon">
                    🏋️
                </div>

            </div>


            <!-- ESTADÍSTICAS -->

            <div class="stats">

                <div class="stat-card">

                    <div class="stat-icon">
                        👥
                    </div>

                    <div>
                        <span>Socios</span>
                        <strong><?php echo $nSocios; ?></strong>
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon">
                        💳
                    </div>

                    <div>
                        <span>Planes</span>
                        <strong><?php echo $nPlanes; ?></strong>
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon">
                        📅
                    </div>

                    <div>
                        <span>Reservas próximas</span>
                        <strong><?php echo $nReservas; ?></strong>
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon">
                        💰
                    </div>

                    <div>
                        <span>Ingresos del mes</span>
                        <strong>$<?php echo number_format($ingresos, 0, ",", "."); ?></strong>
                    </div>

                </div>

            </div>


            <!-- ALERTAS DE MOROSOS Y VENCIMIENTOS -->

            <div class="info-grid">

                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <span>ALERTAS</span>
                            <h2>Morosos (<?php echo count($morosos); ?>)</h2>
                        </div>
                    </div>
                    <?php if (!$morosos): ?>
                        <p class="alerta-ok">✅ No hay socios con pagos pendientes.</p>
                    <?php else: ?>
                        <table class="alerta-tabla">
                            <thead><tr><th>Socio</th><th>Teléfono</th><th>Cuotas</th><th>Deuda</th><th>Desde</th></tr></thead>
                            <tbody>
                            <?php foreach ($morosos as $m): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($m["nombre"]); ?></td>
                                    <td><?php echo htmlspecialchars((string)$m["telefono"]); ?></td>
                                    <td><?php echo (int)$m["cuotas"]; ?></td>
                                    <td>$<?php echo number_format($m["deuda"], 0, ",", "."); ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($m["desde"])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <span>ALERTAS</span>
                            <h2>Vencimientos (<?php echo count($vencimientos); ?>)</h2>
                        </div>
                    </div>
                    <?php if (!$vencimientos): ?>
                        <p class="alerta-ok">✅ No hay cuotas vencidas ni por vencer en los próximos 5 días.</p>
                    <?php else: ?>
                        <table class="alerta-tabla">
                            <thead><tr><th>Socio</th><th>Teléfono</th><th>Situación</th></tr></thead>
                            <tbody>
                            <?php foreach ($vencimientos as $v): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($v["nombre"]); ?></td>
                                    <td><?php echo htmlspecialchars((string)$v["telefono"]); ?></td>
                                    <td class="<?php echo $v["orden"] < 0 ? "alerta-roja" : "alerta-amarilla"; ?>"><?php echo htmlspecialchars($v["texto"]); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            </div>


            <!-- ACCIONES -->

            <div class="section-title">

                <div>
                    <span>GESTIÓN</span>
                    <h2>Acciones rápidas</h2>
                </div>

            </div>


            <div class="actions-grid">

                <button onclick="location.href='seccion/socios.php'">
                    <span>👥</span>
                    <strong>Usuarios</strong>
                    <small>Administrar socios</small>
                </button>


                <button onclick="location.href='seccion/planes.php'">
                    <span>💳</span>
                    <strong>Planes</strong>
                    <small>Administrar membresías</small>
                </button>


                <button onclick="location.href='seccion/clases.php'">
                    <span>🏋️</span>
                    <strong>Clases</strong>
                    <small>Administrar clases</small>
                </button>


                <button onclick="location.href='seccion/reservas.php'">
                    <span>📅</span>
                    <strong>Reservas</strong>
                    <small>Ver reservas</small>
                </button>


                <button onclick="location.href='seccion/pagos.php'">
                    <span>💰</span>
                    <strong>Pagos</strong>
                    <small>Administrar pagos</small>
                </button>


                <button onclick="location.href='seccion/pedidos.php'">
                    <span>🛒</span>
                    <strong>Pedidos</strong>
                    <small>Pedidos de la tienda</small>
                </button>


                <button onclick="location.href='seccion/mensajes.php'">
                    <span>✉️</span>
                    <strong>Preguntas</strong>
                    <small>Preguntas de Nosotros</small>
                </button>


                <button onclick="location.href='seccion/configuracion.php'">
                    <span>⚙️</span>
                    <strong>Configuración</strong>
                    <small>Configurar sistema</small>
                </button>

            </div>


            <!-- INFORMACIÓN -->

            <div class="info-grid">

                <div class="panel">

                    <div class="panel-header">
                        <div>
                            <span>ESTADO</span>
                            <h2>Gimnasio</h2>
                        </div>
                    </div>

                    <div class="gym-status">

                        <div class="status-dot"></div>

                        <div>
                            <strong>Abierto</strong>
                            <p>Horario actual: 06:00 - 23:00</p>
                        </div>

                    </div>

                </div>


                <div class="panel">

                    <div class="panel-header">

                        <div>
                            <span>SISTEMA</span>
                            <h2>Acceso rápido</h2>
                        </div>

                    </div>

                    <a href="../index.html" class="site-button">
                        🌐 Ir al sitio principal
                    </a>

                </div>

            </div>

        </section>


        <!-- FOOTER -->

        <footer>

            <p>
                © 2026 Fitness Max — Panel de administración
            </p>

        </footer>

    </main>

</div>


<!-- JAVASCRIPT -->

<script>

function toggleMenu() {

    document
        .getElementById("sidebar")
        .classList.toggle("open");

}


function noDisponible(event) {

    if (event) {
        event.preventDefault();
    }

    alert("Esta sección todavía no está disponible.");

}

</script>

</body>

</html>
