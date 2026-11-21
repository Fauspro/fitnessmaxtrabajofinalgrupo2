<?php
// Preguntas y opiniones publicadas por los visitantes en la página Nosotros (acá se pueden borrar).
include("../config/bacap.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["usuario"]) || ($_SESSION["rol"] ?? "") !== "admin") { header("Location: ../login.php"); exit(); }
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8");

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["accion"] ?? "") === "Borrar" && (int)($_POST["id"] ?? 0)) {
    $conexion->prepare("DELETE FROM preguntas WHERE id=:i")->execute([":i" => (int)$_POST["id"]]);
    header("Location: mensajes.php"); exit();
}
$mensajes = $conexion->query("SELECT * FROM preguntas ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
include("../template/cabecera.php");
?>
<div class="container my-4">
    <h2 class="mb-3">Preguntas y opiniones (Nosotros)</h2>
    <div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead class="thead-dark"><tr><th>Fecha</th><th>Nombre</th><th>Pregunta / opinión</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($mensajes as $m): ?>
            <tr>
                <td class="text-nowrap"><?php echo $h(date("d/m/Y H:i", strtotime($m["fecha"]))); ?></td>
                <td><?php echo $h($m["nombre"]); ?></td>
                <td><?php echo nl2br($h($m["mensaje"])); ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('¿Borrar esta publicación?');">
                        <input type="hidden" name="id" value="<?php echo (int)$m["id"]; ?>">
                        <button class="btn btn-sm btn-danger" name="accion" value="Borrar">Borrar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$mensajes): ?><tr><td colspan="4" class="text-center text-muted">Todavía no hay preguntas publicadas.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php include("../template/pie.php"); ?>
