<?php
session_start();
include("config/bacap.php");

// Si ya hay una sesión abierta, ir directo a la zona que corresponde
if (($_SESSION["rol"] ?? "") === "admin") { header("Location: index.php"); exit(); }
if (($_SESSION["rol"] ?? "") === "socio") { header("Location: ../cuenta.html"); exit(); }

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"] ?? "");
    $clave = $_POST["contraseña"] ?? "";

    // 1) ¿Es el administrador?
    $st = $conexion->prepare("SELECT usuario, clave, rol FROM usuarios WHERE usuario = :u LIMIT 1");
    $st->execute([":u" => $usuario]);
    $u = $st->fetch(PDO::FETCH_ASSOC);

    // Acceso inicial del administrador: usuario "admin" con clave "admin".
    // (Reemplaza las claves de ejemplo "password" o "1234" que traía la base de datos.)
    if ($usuario === "admin" && $clave === "admin") {
        $nueva = password_hash("admin", PASSWORD_DEFAULT);
        if (!$u) {
            $conexion->prepare("INSERT INTO usuarios (usuario, clave, rol) VALUES ('admin', :c, 'admin')")->execute([":c" => $nueva]);
            $u = ["usuario" => "admin", "clave" => $nueva, "rol" => "admin"];
        } elseif (password_verify("password", $u["clave"]) || password_verify("1234", $u["clave"])) {
            $conexion->prepare("UPDATE usuarios SET clave = :c WHERE usuario = 'admin'")->execute([":c" => $nueva]);
            $u["clave"] = $nueva;
        }
    }

    if ($u && password_verify($clave, $u["clave"])) {
        session_regenerate_id(true);
        $_SESSION["usuario"] = $u["usuario"];
        $_SESSION["rol"] = $u["rol"];
        header("Location: index.php");
        exit();
    }

    // 2) ¿Es un socio? (ingresa con su DNI)
    $dni = preg_replace('/\D/', '', $usuario);
    if ($dni !== "") {
        $st = $conexion->prepare("SELECT id, nombre, clave, estado FROM socios WHERE dni = :d LIMIT 1");
        $st->execute([":d" => $dni]);
        $s = $st->fetch(PDO::FETCH_ASSOC);
        if ($s && $s["clave"] && password_verify($clave, $s["clave"])) {
            if ($s["estado"] !== "activo") {
                $error = "Tu cuenta de socio está inactiva. Consultá en recepción.";
            } else {
                session_regenerate_id(true);
                $_SESSION["usuario"] = $s["nombre"];
                $_SESSION["rol"] = "socio";
                $_SESSION["socio_id"] = (int)$s["id"];
                header("Location: ../cuenta.html");
                exit();
            }
        }
    }
    if ($error === "") { $error = "Usuario/DNI o contraseña incorrectos"; }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión - Fitness Max</title>

    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/index.css">
    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            background: #111;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding-top: 80px;
        }

        .login-container {
            width: 400px;
            max-width: 90%;
            background: #1b1b1b;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.6);
        }

        .logo {
            text-align: center;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 45px;
            letter-spacing: 2px;
            color: #ff2020;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            color: #aaa;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: white;
            font-size: 14px;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 14px;
            border: 1px solid #333;
            border-radius: 8px;
            background: #262626;
            color: white;
            font-size: 15px;
            outline: none;
        }

        input:focus {
            border-color: #ff2020;
        }

        input::placeholder {
            color: #777;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #ff2020;
            color: white;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #d91616;
            transform: translateY(-2px);
        }

        .error {
            background: #3a1515;
            border: 1px solid #ff2020;
            color: #ff6b6b;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #aaa;
            text-decoration: none;
            font-size: 13px;
        }

        .back:hover {
            color: white;
        }

        .footer {
            text-align: center;
            color: #555;
            font-size: 11px;
            margin-top: 25px;
        }

    </style>

</head>

<body>
<header class="site-header">
    <div class="header-inner">
        <a href="../index.html" class="logo">FITNESS MAX</a>
        <nav class="nav">
            <a href="../index.html">Inicio</a>
            <a href="../index.html#planes">Planes</a>
            <a href="../actividades.html">Actividades</a>
            <a href="../nosotros.html">Nosotros</a>
            <a href="../productos.html">Productos</a>
            <a href="../carrito.html">🛒 Carrito</a>
        </nav>
        <div class="actions">
            <a href="login.php" class="login">👤 Mi Cuenta</a>
            <button class="menu-btn" aria-label="Menú">☰</button>
        </div>
    </div>
</header>

    <div class="login-container">

        <div class="logo">
            FITNESS MAX
        </div>

        <div class="subtitle">
            Ingresá con tu usuario (administración) o con tu DNI (socios)
        </div>

        <?php if ($error !== ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">

                <label for="usuario">
                    Usuario o DNI
                </label>

                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    placeholder="Usuario o DNI"
                    autocomplete="username"
                    required
                >

            </div>

            <div class="form-group">

                <label for="contraseña">
                    Contraseña
                </label>

                <input
                    type="password"
                    id="contraseña"
                    name="contraseña"
                    placeholder="Ingresá tu contraseña"
                    autocomplete="current-password"
                    required
                >

            </div>

            <button type="submit" class="btn-login">
                INICIAR SESIÓN
            </button>

        </form>

        <a href="../activar.html" class="back">
            ¿Sos socio y es tu primera vez? Activá tu cuenta
        </a>

        <a href="../index.html" class="back" style="margin-top:10px">
            ← Volver al inicio
        </a>

        <div class="footer">
            © 2026 Fitness Max
        </div>

    </div>

</body>

</html>
