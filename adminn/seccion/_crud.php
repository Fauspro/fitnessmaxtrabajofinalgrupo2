<?php
// Gestor genérico de módulos. Cada página define $cfg y hace include("_crud.php").
include("../config/bacap.php");
include("../config/validaciones.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["usuario"]) || ($_SESSION["rol"] ?? "") !== "admin") { header("Location: ../login.php"); exit(); }

$t = $cfg["tabla"]; $campos = $cfg["campos"]; $cols = array_keys($campos);
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8");
$self = basename($_SERVER["PHP_SELF"]); $error = "";

$opc = [];
foreach ($campos as $c => $d) {
    if (($d[1] ?? "") === "select") {
        $opc[$c] = is_array($d[2]) ? array_combine($d[2], $d[2]) : $conexion->query($d[2])->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}

$accion = $_POST["accion"] ?? ""; $id = (int)($_POST["id"] ?? 0);
$datos = [];
foreach ($cols as $c) { $v = trim($_POST[$c] ?? ""); $datos[$c] = ($v === "") ? null : $v; }

// Validación de datos según el nombre de la columna (el servidor siempre revisa)
function validarFila($datos, $tabla) {
    foreach ($datos as $c => $v) {
        if ($v === null) continue;
        if ($c === "email" && ($e = v_email($v))) return $e;
        if ($c === "telefono" && ($e = v_telefono($v))) return $e;
        if ($c === "dni" && ($e = v_dni($v))) return $e;
        if (($c === "instructor" || ($c === "nombre" && $tabla === "socios")) && ($e = v_nombre($v, $c === "nombre" ? "El nombre" : "El instructor"))) return $e;
        if (in_array($c, ["nombre", "concepto", "descripcion", "instructor"], true) && mb_strlen($v) > 100) return "El campo " . $c . " no puede superar los 100 caracteres.";
    }
    return "";
}
$errorValidacion = ($accion === "Agregar" || $accion === "Modificar") ? validarFila($datos, $t) : "";
if ($errorValidacion) { $error = $errorValidacion; $accion = ""; }

try {
    if ($accion === "Agregar") {
        $conexion->prepare("INSERT INTO $t (" . implode(",", $cols) . ") VALUES (" . implode(",", array_map(fn($c) => ":$c", $cols)) . ")")->execute($datos);
        header("Location: $self"); exit();
    }
    if ($accion === "Modificar" && $id) {
        $d = $datos; $d["id"] = $id;
        $conexion->prepare("UPDATE $t SET " . implode(",", array_map(fn($c) => "$c=:$c", $cols)) . " WHERE id=:id")->execute($d);
        header("Location: $self"); exit();
    }
    if ($accion === "Borrar" && $id) {
        $conexion->prepare("DELETE FROM $t WHERE id=:id")->execute([":id" => $id]);
        header("Location: $self"); exit();
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "No se pudo guardar. Revisá que los datos sean válidos (por ejemplo, DNI repetido).";
}

$edit = null;
if (isset($_GET["editar"])) {
    $s = $conexion->prepare("SELECT * FROM $t WHERE id=:id"); $s->execute([":id" => (int)$_GET["editar"]]);
    $edit = $s->fetch(PDO::FETCH_ASSOC);
}
$filas = $conexion->query("SELECT * FROM $t ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

include("../template/cabecera.php");
?>
<div class="container my-4">
    <h2 class="mb-3"><?php echo $h($cfg["titulo"]); ?></h2>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo $h($error); ?></div><?php endif; ?>

    <form method="POST" class="card card-body mb-4">
        <input type="hidden" name="id" value="<?php echo $h($edit["id"] ?? ""); ?>">
        <div class="form-row">
        <?php foreach ($campos as $c => $d): $val = $edit[$c] ?? ""; ?>
            <div class="form-group col-md-4">
                <label><?php echo $h($d[0]); ?></label>
                <?php if (($d[1] ?? "") === "select"): ?>
                    <select name="<?php echo $c; ?>" class="form-control">
                        <?php if (!is_array($d[2])): ?><option value="">—</option><?php endif; ?>
                        <?php foreach ($opc[$c] as $k => $lab): ?>
                            <option value="<?php echo $h($k); ?>" <?php echo ((string)$val === (string)$k) ? "selected" : ""; ?>><?php echo $h($lab); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="<?php echo $h($d[1] ?? "text"); ?>" name="<?php echo $c; ?>" class="form-control" maxlength="<?php echo in_array($c, ["telefono"]) ? 15 : (in_array($c, ["dni"]) ? 9 : 100); ?>" <?php echo in_array($c, ["telefono", "dni"]) ? 'inputmode="numeric" pattern="[0-9]*" title="Solo números"' : ""; ?> value="<?php echo $h($val); ?>" <?php echo ($d[1] ?? "") === "number" ? 'step="any"' : ""; ?>>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
        <div>
            <button class="btn btn-success" name="accion" value="<?php echo $edit ? "Modificar" : "Agregar"; ?>"><?php echo $edit ? "Guardar cambios" : "Agregar"; ?></button>
            <?php if ($edit): ?><a href="<?php echo $self; ?>" class="btn btn-secondary">Cancelar</a><?php endif; ?>
        </div>
    </form>

    <div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead class="thead-dark"><tr>
            <?php foreach ($campos as $d): ?><th><?php echo $h($d[0]); ?></th><?php endforeach; ?><th></th>
        </tr></thead>
        <tbody>
        <?php foreach ($filas as $f): ?>
            <tr>
                <?php foreach ($cols as $c): ?>
                    <td><?php echo $h(isset($opc[$c]) ? ($opc[$c][$f[$c]] ?? $f[$c]) : $f[$c]); ?></td>
                <?php endforeach; ?>
                <td class="text-nowrap">
                    <a class="btn btn-sm btn-primary" href="<?php echo $self; ?>?editar=<?php echo (int)$f["id"]; ?>">Editar</a>
                    <form method="POST" class="d-inline" onsubmit="return confirm('¿Borrar este registro?');">
                        <input type="hidden" name="id" value="<?php echo (int)$f["id"]; ?>">
                        <button class="btn btn-sm btn-danger" name="accion" value="Borrar">Borrar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$filas): ?><tr><td colspan="<?php echo count($cols) + 1; ?>" class="text-center text-muted">Sin registros todavía.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php include("../template/pie.php"); ?>
