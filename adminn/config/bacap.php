<?php

define("KEY", "tienda");
define("COD", "AES-128-ECB");

$host = "localhost";
$bd = "tienda";
$usuario = "root";
$contrasenia = "";

try {

    $conexion = new PDO(
        "mysql:host=$host;dbname=$bd;charset=utf8mb4",
        $usuario,
        $contrasenia
    );

    // Informa los errores mediante excepciones
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $ex) {

    // Detalle técnico solo en el registro del servidor
    error_log($ex->getMessage());

    // Mensaje general para el usuario
    die("No se pudo conectar con la base de datos.");

}

?>