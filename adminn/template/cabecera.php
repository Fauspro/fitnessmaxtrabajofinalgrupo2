<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["usuario"]) || ($_SESSION["rol"] ?? "") !== "admin") { header("Location: ../login.php"); exit(); }
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Fitness Max - Administrador</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Iconos -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>

<body>

<!-- ==========================================
     CABECERA DEL ADMIN
========================================== -->

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">

    <div class="container">

        <!-- LOGO -->

        <a class="navbar-brand" href="../index.php">

            <i class="fas fa-dumbbell"></i>

            <strong>FITNESS MAX</strong>

        </a>


        <!-- BOTÓN RESPONSIVE -->

        <button
            class="navbar-toggler"
            type="button"
            data-toggle="collapse"
            data-target="#menuAdmin"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <!-- MENÚ -->

        <div
            class="collapse navbar-collapse"
            id="menuAdmin"
        >

            <ul class="navbar-nav mr-auto">


                <!-- INICIO -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="../index.php"
                    >

                        <i class="fas fa-home"></i>

                        Inicio

                    </a>

                </li>


                <!-- PRODUCTOS -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="productos.php"
                    >

                        <i class="fas fa-box"></i>

                        Productos

                    </a>

                </li>


                <!-- SOCIOS -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="socios.php"
                    >

                        <i class="fas fa-users"></i>

                        Socios

                    </a>

                </li>


                <!-- PLANES -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="planes.php"
                    >

                        <i class="fas fa-id-card"></i>

                        Planes

                    </a>

                </li>
<li class="nav-item">

                    <a
                        class="nav-link"
                        href="clases.php"
                    >

                        <i class="fas fa-dumbbell"></i>

                        Clases

                    </a>

                </li>
<li class="nav-item">

                    <a
                        class="nav-link"
                        href="reservas.php"
                    >

                        <i class="fas fa-calendar-check"></i>

                        Reservas

                    </a>

                </li>
<li class="nav-item">

                    <a
                        class="nav-link"
                        href="pagos.php"
                    >

                        <i class="fas fa-money-bill"></i>

                        Pagos

                    </a>

                </li>


                <!-- PEDIDOS -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="pedidos.php"
                    >

                        <i class="fas fa-shopping-cart"></i>

                        Pedidos

                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="mensajes.php"
                    >

                        <i class="fas fa-envelope"></i>

                        Preguntas

                    </a>

                </li>

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="configuracion.php"
                    >

                        <i class="fas fa-cog"></i>

                        Configuración

                    </a>

                </li>
            </ul>


            <!-- CERRAR SESIÓN -->

            <a
                href="../cerrar.php"
                class="btn btn-danger"
            >

                <i class="fas fa-sign-out-alt"></i>

                Cerrar sesión

            </a>

        </div>

    </div>

</nav>


<!-- ==========================================
     CONTENIDO
========================================== -->

<div class="container mt-4">
