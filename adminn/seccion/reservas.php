<?php
$cfg = ["tabla" => "reservas", "titulo" => "Reservas de clases", "campos" => [
    "socio_id" => ["Socio", "select", "SELECT id, nombre FROM socios"],
    "clase_id" => ["Clase", "select", "SELECT id, CONCAT(nombre,' - ',dia,' ',TIME_FORMAT(hora,'%H:%i')) FROM clases"],
    "fecha" => ["Fecha", "date"], "estado" => ["Estado", "select", ["confirmada", "cancelada"]]]];
include("_crud.php");
