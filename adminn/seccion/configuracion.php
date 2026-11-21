<?php
// Configuración: cambio de contraseña del administrador.
include("../config/bacap.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["usuario"]) || ($_SESSION["rol"] ?? "") !== "admin") { header("Location: ../login.php"); exit(); }
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8");
$msg = ""; $ok = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $actual = $_POST["actual"] ?? ""; $nueva = $_POST["nueva"] ?? ""; $rep = $_POST["repetir"] ?? "";
    $st = $conexion->prepare("SELECT id, clave FROM usuarios WHERE usuario = :u LIMIT 1");
    $st->execute([":u" => $_SESSION["usuario"]]);
    $u = $st->fetch(PDO::FETCH_ASSOC);
    if (!$u || !password_verify($actual, $u["clave"])) { $msg = "La contraseña actual no es correcta."; }
    elseif (strlen($nueva) < 6) { $msg = "La nueva contraseña debe tener al menos 6 caracteres."; }
    elseif ($nueva !== $rep) { $msg = "Las contraseñas nuevas no coinciden."; }
    else {
        $conexion->prepare("UPDATE usuarios SET clave = :c WHERE id = :i")->execute([":c" => password_hash($nueva, PASSWORD_DEFAULT), ":i" => $u["id"]]);
        $ok = true; $msg = "Contraseña actualizada correctamente.";
    }
}
include("../template/cabecera.php");
?>
<div class="container my-4" style="max-width:560px">
    <h2 class="mb-3">Configuración</h2>
    <?php if ($msg): ?><div class="alert alert-<?php echo $ok ? "success" : "danger"; ?>"><?php echo $h($msg); ?></div><?php endif; ?>
    <form method="POST" class="card card-body">
        <h5>Cambiar contraseña de <?php echo $h($_SESSION["usuario"]); ?></h5>
        <div class="form-group"><label>Contraseña actual</label><input type="password" name="actual" class="form-control" required autocomplete="current-password"></div>
        <div class="form-group"><label>Nueva contraseña (mínimo 6 caracteres)</label><input type="password" name="nueva" class="form-control" required minlength="6" autocomplete="new-password"></div>
        <div class="form-group"><label>Repetir nueva contraseña</label><input type="password" name="repetir" class="form-control" required minlength="6" autocomplete="new-password"></div>
        <div><button class="btn btn-success">Guardar</button></div>
    </form>
</div>
<?php include("../template/pie.php"); ?>
