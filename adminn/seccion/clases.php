<?php
$cfg = ["tabla" => "clases", "titulo" => "Clases y horarios", "campos" => [
    "nombre" => ["Actividad"], "dia" => ["Día", "select", ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"]],
    "hora" => ["Hora", "time"], "cupo" => ["Cupo", "number"], "instructor" => ["Instructor"],
    "plan_id" => ["Plan mínimo", "select", "SELECT id, nombre FROM planes"]]];
include("_crud.php");
