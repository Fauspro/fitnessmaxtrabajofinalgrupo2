<?php
$cfg = ["tabla" => "socios", "titulo" => "Socios", "campos" => [
    "nombre" => ["Nombre y apellido"], "dni" => ["DNI"], "email" => ["Email", "email"], "telefono" => ["Teléfono"],
    "plan_id" => ["Plan", "select", "SELECT id, nombre FROM planes"],
    "estado" => ["Estado", "select", ["activo", "inactivo"]]]];
include("_crud.php");
