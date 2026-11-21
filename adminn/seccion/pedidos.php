<?php
// Pedidos de la tienda (registrados desde el carrito del sitio público).
include("../config/bacap.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["usuario"]) || ($_SESSION["rol"] ?? "") !== "admin") { header("Location: ../login.php"); exit(); }

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8");
$accion = $_POST["accion"] ?? ""; $id = (int)($_POST["id"] ?? 0);

if ($_SERVER["REQUEST_METHOD"] === "POST" && $id) {
    if ($accion === "Estado" && in_array($_POST["estado"] ?? "", ["nuevo", "entregado", "cancelado"], true)) {
        $conexion->prepare("UPDATE pedidos SET estado=:e WHERE id=:i")->execute([":e" => $_POST["estado"], ":i" => $id]);
    }
    if ($accion === "Borrar") {
        $conexion->prepare("DELETE FROM pedidos WHERE id=:i")->execute([":i" => $id]);
    }
    header("Location: pedidos.php"); exit();
}

$pedidos = $conexion->query("SELECT * FROM pedidos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$itemsPorPedido = [];
foreach ($conexion->query("SELECT pedido_id, item, precio, cantidad FROM pedido_items ORDER BY id")->fetchAll(PDO::FETCH_ASSOC) as $it) {
    $itemsPorPedido[$it["pedido_id"]][] = $it;
}

include("../template/cabecera.php");
?>
<div class="container my-4">
    <h2 class="mb-3">Pedidos de la tienda</h2>
    <div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead class="thead-dark"><tr><th>#</th><th>Fecha</th><th>Cliente</th><th>Contacto</th><th>Pago</th><th>Detalle</th><th>Total</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($pedidos as $p): ?>
            <tr>
                <td><?php echo (int)$p["id"]; ?></td>
                <td class="text-nowrap"><?php echo $h(date("d/m/Y H:i", strtotime($p["fecha"]))); ?></td>
                <td><?php echo $h($p["nombre"]); ?></td>
                <td><?php echo $h($p["telefono"]); ?><br><small><?php echo $h($p["email"] ?? ""); ?></small></td>
                <td><?php echo ($p["metodo_pago"] ?? "") === "tarjeta" ? "💳 Tarjeta" . (!empty($p["tarjeta_ultimos4"]) ? " •••• " . $h($p["tarjeta_ultimos4"]) : "") : "💵 Efectivo"; ?></td>
                <td>
                    <?php foreach ($itemsPorPedido[$p["id"]] ?? [] as $it): ?>
                        <div><?php echo (int)$it["cantidad"]; ?> × <?php echo $h($it["item"]); ?> <small class="text-muted">($<?php echo number_format($it["precio"] * $it["cantidad"], 0, ",", "."); ?>)</small></div>
                    <?php endforeach; ?>
                </td>
                <td class="text-nowrap"><strong>$<?php echo number_format($p["total"], 0, ",", "."); ?></strong></td>
                <td>
                    <form method="POST" class="form-inline">
                        <input type="hidden" name="id" value="<?php echo (int)$p["id"]; ?>">
                        <select name="estado" class="form-control form-control-sm mr-1">
                            <?php foreach (["nuevo", "entregado", "cancelado"] as $e): ?>
                                <option value="<?php echo $e; ?>" <?php echo $p["estado"] === $e ? "selected" : ""; ?>><?php echo $e; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm btn-primary" name="accion" value="Estado">Guardar</button>
                    </form>
                </td>
                <td>
                    <form method="POST" onsubmit="return confirm('¿Borrar este pedido?');">
                        <input type="hidden" name="id" value="<?php echo (int)$p["id"]; ?>">
                        <button class="btn btn-sm btn-danger" name="accion" value="Borrar">Borrar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$pedidos): ?><tr><td colspan="9" class="text-center text-muted">Todavía no hay pedidos.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php include("../template/pie.php"); ?>
