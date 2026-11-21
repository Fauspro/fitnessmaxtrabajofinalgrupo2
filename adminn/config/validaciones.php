<?php
// Validaciones compartidas (el servidor siempre revisa, aunque el navegador ya lo haya hecho).
// Cada función devuelve "" si el dato es válido, o el mensaje de error.

function v_nombre($s, $etiqueta = "El nombre") {
    $s = trim(preg_replace('/\s+/u', ' ', (string)$s));
    if (mb_strlen($s) < 2) return "$etiqueta debe tener al menos 2 letras.";
    if (mb_strlen($s) > 50) return "$etiqueta no puede superar los 50 caracteres.";
    if (!preg_match('/^\p{L}+(?:[ \'’\-]\p{L}+)*$/u', $s)) return "$etiqueta solo puede tener letras y espacios (sin números ni símbolos).";
    return "";
}
function v_email($s) {
    $s = trim((string)$s);
    if ($s === "" || strpos($s, "@") === false) return "El email debe incluir @ (ej: nombre@gmail.com).";
    if (mb_strlen($s) > 100) return "El email no puede superar los 100 caracteres.";
    if (!filter_var($s, FILTER_VALIDATE_EMAIL)) return "Ingresá un email válido (ej: nombre@gmail.com).";
    return "";
}
function v_telefono($s) {
    $s = (string)$s;
    if (!preg_match('/^\d+$/', $s)) return "El teléfono solo puede tener números.";
    if (strlen($s) < 8 || strlen($s) > 15) return "El teléfono debe tener entre 8 y 15 dígitos.";
    return "";
}
function v_dni($s) {
    $s = (string)$s;
    if (!preg_match('/^\d+$/', $s)) return "El DNI solo puede tener números.";
    if (strlen($s) < 7 || strlen($s) > 9) return "El DNI debe tener entre 7 y 9 dígitos.";
    return "";
}
function v_texto($s, $min, $max, $etiqueta = "El texto") {
    $s = trim((string)$s); $n = mb_strlen($s);
    if ($n < $min) return "$etiqueta debe tener al menos $min caracteres.";
    if ($n > $max) return "$etiqueta no puede superar los $max caracteres.";
    return "";
}
