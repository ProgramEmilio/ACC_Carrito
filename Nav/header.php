<?php
include('../BD/ConexionBD.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_rol'])) {
    header("Location: ../Login/login.php");
    exit();
}


$id_rol = $_SESSION['id_rol'];

$menus = [
    1 => [
        "Usuario" => [
            "Usuario" => "../Usuarios/usuario.php",
            "Registro" => "../Usuarios/Registro/Registro_Usuario.php"
        ],
        "Catalogo" => [
            "Incapacidad" => "../Incapacidad/incapacidades.php",
            "Registro" => "../Incapacidad/Registro/Registro_Incapacidades.php"
        ],
        "Carrito" => [
            "Nomina" => "../Carrito/carrito.php",
            "Registro" => "../Nomina/Registro_N/Registro_Nomina.php"
        ]
    ]
];

include('CerrarSesion.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>

    <!-- Archivos CSS -->
    <link rel="stylesheet" href="../CSS/cabecera.css" type="text/css">
    <link rel="stylesheet" href="../CSS/menu.css" type="text/css">
    <link rel="stylesheet" href="../CSS/pie_pagina.css" type="text/css">
    <link rel="stylesheet" href="../CSS/tablas_boton.css" type="text/css">
    <link rel="stylesheet" href="../CSS/formularios.css" type="text/css">
    <link rel="stylesheet" href="../CSS/departamentos.css" type="text/css">
    <link rel="stylesheet" href="../CSS/cabecera2.css" type="text/css">
    <link rel="stylesheet" href="../CSS/Detalle_Producto.css" type="text/css">
    <link rel="stylesheet" href="../CSS/Carrito.css" type="text/css">
    <link rel="stylesheet" href="../CSS/direccion.css" type="text/css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<header class="cabecera_p">
    <div class="cabecera">

        <!-- Logo + Nombre del sistema -->
        <div class="logo-nombre">
            <a href="../Inicio/inicio.php">
                <img src="../Imagenes/acc_logo.png" class="img-logo" alt="Logo">
            </a>
            <h1 class="nom_sis">Aplica Central Creativa</h1>
        </div>

        <!-- Carrito y botón perfil centrados -->
        <div class="centro-cabecera">
            <div class="carrito-icono" onclick="irAlCarrito()">
                <img src="../Imagenes/carrito.png" alt="Carrito" class="carrito-img">
                <span id="contador-carrito">0</span>
            </div>
             <a href="../Perfil/Perfil.php" class="btn-ver-perfil">Perfil</a>
        </div>

        <!-- Botón cerrar sesión -->
        <div class="logout-container">
            <form method="POST">
                <button type="submit" name="cerrar_sesion" class="btn_logout">Cerrar Sesión</button>
            </form>
        </div>
    </div>

    <!-- Menú de navegación -->
    <div class="header">
        <ul class="nav">
            <?php
            if ($id_rol && isset($menus[$id_rol])) {
                foreach ($menus[$id_rol] as $nombre => $url) {
                    if (is_array($url)) {
                        echo "<li><a href='#'>$nombre</a><ul class='submenu'>";
                        foreach ($url as $subnombre => $suburl) {
                            echo "<li><a href='$suburl'>$subnombre</a></li>";
                        }
                        echo "</ul></li>";
                    } else {
                        echo "<li><a href='$url'>$nombre</a></li>";
                    }
                }
            } else {
                echo "<li><a href='#'>Acceso Denegado</a></li>";
            }
            ?>

            
        </ul>
    </div>
</header>
</body>
</html>
