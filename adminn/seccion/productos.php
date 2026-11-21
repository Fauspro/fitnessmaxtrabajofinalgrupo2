<?php
include("../config/bacap.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["usuario"]) || ($_SESSION["rol"] ?? "") !== "admin") { header("Location: ../login.php"); exit(); }

$txtID = isset($_POST['txtId']) ? $_POST['txtId'] : "";
$txtNombre = isset($_POST['txtNombre']) ? $_POST['txtNombre'] : "";
$txtPrecio = isset($_POST['txtPrecio']) ? $_POST['txtPrecio'] : "";
$txtDescripcion = isset($_POST['txtDescripcion']) ? trim($_POST['txtDescripcion']) : "";
$txtImagen = isset($_FILES['txtImagen']['name']) ? $_FILES['txtImagen']['name'] : "";
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8");

// Nombre de archivo seguro: solo imágenes, sin rutas ni caracteres raros
function nombreImagenSeguro($nombre) {
    $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)) { return null; }
    $base = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($nombre, PATHINFO_FILENAME));
    return (new DateTime())->getTimestamp() . "_" . $base . "." . $ext;
}
$accion = isset($_POST['accion']) ? $_POST['accion'] : "";


/* ==========================
   AGREGAR PRODUCTO
========================== */

switch ($accion) {

    case 'Agregar':

        $nombreArchivo = ($_FILES["txtImagen"]["name"] != "")
            ? (nombreImagenSeguro($_FILES["txtImagen"]["name"]) ?? "imagen.jpg")
            : "imagen.jpg";

        $tmpImagen = $_FILES["txtImagen"]["tmp_name"];

        if ($tmpImagen != "" && $nombreArchivo != "imagen.jpg") {
            move_uploaded_file(
                $tmpImagen,
                "../../assets/img/productos/" . $nombreArchivo
            );
        }

        $sentenciaSQL = $conexion->prepare(
            "INSERT INTO productos (nombre, precio, descripcion, imagen)
             VALUES (:nombre, :precio, :descripcion, :imagen)"
        );

        $sentenciaSQL->bindParam(':descripcion', $txtDescripcion);
        $sentenciaSQL->bindParam(':nombre', $txtNombre);
        $sentenciaSQL->bindParam(':precio', $txtPrecio);
        $sentenciaSQL->bindParam(':imagen', $nombreArchivo);

        $sentenciaSQL->execute();

        header("Location: productos.php");
        break;


/* ==========================
   MODIFICAR PRODUCTO
========================== */

    case 'Modificar':

        if ($txtID != "") {

            $sentenciaSQL = $conexion->prepare(
                "SELECT imagen FROM productos WHERE id=:id"
            );

            $sentenciaSQL->bindParam(':id', $txtID);
            $sentenciaSQL->execute();

            $productoActual = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);

            $nombreArchivo = ($productoActual)
                ? $productoActual['imagen']
                : 'imagen.jpg';


            if (
                isset($_FILES["txtImagen"]) &&
                $_FILES["txtImagen"]["tmp_name"] != ""
            ) {

                $nuevoNombre = nombreImagenSeguro($_FILES["txtImagen"]["name"]);

                if ($nuevoNombre !== null) {
                    $nombreArchivo = $nuevoNombre;

                    move_uploaded_file(
                        $_FILES["txtImagen"]["tmp_name"],
                        "../../assets/img/productos/" . $nombreArchivo
                    );
                }
            }


            $sentenciaSQL = $conexion->prepare(
                "UPDATE productos
                 SET nombre=:nombre,
                     precio=:precio,
                     descripcion=:descripcion,
                     imagen=:imagen
                 WHERE id=:id"
            );

            $sentenciaSQL->bindParam(':descripcion', $txtDescripcion);
            $sentenciaSQL->bindParam(':nombre', $txtNombre);
            $sentenciaSQL->bindParam(':precio', $txtPrecio);
            $sentenciaSQL->bindParam(':imagen', $nombreArchivo);
            $sentenciaSQL->bindParam(':id', $txtID);

            $sentenciaSQL->execute();
        }

        header("Location: productos.php");
        break;


/* ==========================
   CANCELAR
========================== */

    case 'Cancelar':

        header("Location: productos.php");
        break;


/* ==========================
   SELECCIONAR
========================== */

    case 'Seleccionar':

        $sentenciaSQL = $conexion->prepare(
            "SELECT * FROM productos WHERE id=:id"
        );

        $sentenciaSQL->bindParam(':id', $txtID);
        $sentenciaSQL->execute();

        $producto = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);

        if ($producto) {

            $txtNombre = $producto['nombre'];
            $txtPrecio = $producto['precio'];
            $txtDescripcion = $producto['descripcion'] ?? "";
            $txtImagen = $producto['imagen'];
        }

        break;


/* ==========================
   BORRAR
========================== */

    case 'Borrar':

        if ($txtID != "") {

            $sentenciaSQL = $conexion->prepare(
                "SELECT imagen FROM productos WHERE id=:id"
            );

            $sentenciaSQL->bindParam(':id', $txtID);
            $sentenciaSQL->execute();

            $producto = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);


            if (
                isset($producto["imagen"]) &&
                $producto["imagen"] != "imagen.jpg"
            ) {

                if (
                    file_exists(
                        "../../assets/img/productos/" . basename($producto["imagen"])
                    )
                ) {

                    unlink(
                        "../../assets/img/productos/" . basename($producto["imagen"])
                    );
                }
            }


            $sentenciaSQL = $conexion->prepare(
                "DELETE FROM productos WHERE id=:id"
            );

            $sentenciaSQL->bindParam(':id', $txtID);
            $sentenciaSQL->execute();
        }

        header("Location: productos.php");
        break;
}


/* ==========================
   LISTAR PRODUCTOS
========================== */

$sentenciaSQL = $conexion->prepare(
    "SELECT * FROM productos"
);

$sentenciaSQL->execute();

$listaProductos = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);

?>
<?php include("../template/cabecera.php"); ?>

<!-- ==========================
     FORMULARIO
========================== -->

<div class="row">

    <!-- FORMULARIO -->

    <div class="col-md-5">

        <div class="card">

            <div class="card-header">
                <strong>Productos Fitness Max</strong>
            </div>

            <div class="card-body">

                <form
                    method="POST"
                    enctype="multipart/form-data"
                >

                    <!-- ID -->

                    <div class="form-group">

                        <label for="txtId">
                            ID:
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="txtId"
                            id="txtId"
                            value="<?php echo $h($txtID); ?>"
                            readonly
                        >

                    </div>


                    <!-- NOMBRE -->

                    <div class="form-group">

                        <label for="txtNombre">
                            Nombre del producto:
                        </label>

                        <input
                            type="text"
                            required
                            class="form-control"
                            name="txtNombre"
                            id="txtNombre"
                            placeholder="Ej: Proteína Whey"
                            value="<?php echo $h($txtNombre); ?>"
                        >

                    </div>


                    <!-- PRECIO -->

                    <div class="form-group">

                        <label for="txtPrecio">
                            Precio:
                        </label>

                        <input
                            type="number"
                            required
                            class="form-control"
                            name="txtPrecio"
                            id="txtPrecio"
                            placeholder="Ej: 35000"
                            value="<?php echo $h($txtPrecio); ?>"
                        >

                    </div>


                    <!-- DESCRIPCIÓN -->

                    <div class="form-group">

                        <label for="txtDescripcion">
                            Descripción:
                        </label>

                        <input
                            type="text"
                            maxlength="255"
                            class="form-control"
                            name="txtDescripcion"
                            id="txtDescripcion"
                            placeholder="Ej: Ideal para recuperar masa muscular"
                            value="<?php echo $h($txtDescripcion); ?>"
                        >

                    </div>


                    <!-- IMAGEN -->

                    <div class="form-group">

                        <label for="txtImagen">
                            Imagen:
                        </label>

                        <?php if ($txtImagen != "") { ?>

                            <p>
                                Imagen actual:
                                <?php echo $h($txtImagen); ?>
                            </p>

                        <?php } ?>

                        <input
                            type="file"
                            class="form-control"
                            name="txtImagen"
                            id="txtImagen"
                        >

                    </div>


                    <!-- BOTONES -->

                    <div class="btn-group">

                        <button
                            type="submit"
                            name="accion"
                            value="Agregar"
                            class="btn btn-success"
                            <?php echo ($accion == "Seleccionar") ? "disabled" : ""; ?>
                        >
                            Agregar
                        </button>


                        <button
                            type="submit"
                            name="accion"
                            value="Modificar"
                            class="btn btn-warning"
                            <?php echo ($accion != "Seleccionar") ? "disabled" : ""; ?>
                        >
                            Modificar
                        </button>


                        <button
                            type="submit"
                            name="accion"
                            value="Cancelar"
                            class="btn btn-info"
                            <?php echo ($accion != "Seleccionar") ? "disabled" : ""; ?>
                        >
                            Cancelar
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    <!-- TABLA -->

    <div class="col-md-7">

        <div class="table-responsive">

            <table class="table table-bordered">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Imagen</th>
                        <th>Acciones</th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($listaProductos as $producto) { ?>

                        <tr>

                            <td>
                                <?php echo (int)$producto['id']; ?>
                            </td>

                            <td>
                                <?php echo $h($producto['nombre']); ?>
                            </td>

                            <td>
                                $<?php echo $h($producto['precio']); ?>
                            </td>

                            <td>

                                <img
                                    src="../../assets/img/productos/<?php echo $h(basename((string)$producto['imagen'])); ?>"
                                    onerror="this.onerror=null;this.src='../../assets/img/producto.svg'"
                                    width="60"
                                    alt="Producto"
                                >

                            </td>

                            <td>

                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="txtId"
                                        value="<?php echo (int)$producto['id']; ?>"
                                    >

                                    <input
                                        type="submit"
                                        name="accion"
                                        value="Seleccionar"
                                        class="btn btn-primary"
                                    >

                                    <input
                                        type="submit"
                                        name="accion"
                                        value="Borrar"
                                        class="btn btn-danger"
                                    >

                                </form>

                            </td>

                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<?php include("../template/pie.php"); ?>
