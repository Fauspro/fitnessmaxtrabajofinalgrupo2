<?php
$cfg = ["tabla" => "pagos", "titulo" => "Pagos y cuotas", "campos" => [
    "socio_id" => ["Socio", "select", "SELECT id, nombre FROM socios"], "concepto" => ["Concepto (ej: Cuota octubre)"],
    "monto" => ["Monto", "number"], "fecha" => ["Fecha", "date"], "estado" => ["Estado", "select", ["pagado", "pendiente"]]]];
include("_crud.php");
