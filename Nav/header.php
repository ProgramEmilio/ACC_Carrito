<?php
include('../BD/ConexionBD.php');
include('../BD/ConexionBDB.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_rol'])) {
    header("Location: ../Login/login.php");
    exit();
}


$id_rol = $_SESSION['id_rol'];

$menus = [
    // Rol 1: Administrador
    1 => [
        "Usuarios" => "../Usuarios\Usuarios.php",

        "Catálogo" => [
            "Incapacidades" => "../Incapacidad/incapacidades.php",
            "Registrar Incapacidad" => "../Incapacidad/Registro/Registro_Incapacidades.php"
        ],
        "Nómina" => [
            "Ver Nómina" => "../Carrito/carrito.php",
            "Registrar Nómina" => "../Nomina/Registro_N/Registro_Nomina.php"
        ],
        "Banco" => [
            "Clientes" => "../Banco_Usuarios\Usuarios_B.php",
            "Bancos" => "../Bancos\Bancos.php"
        ]
    ],

    // Rol 2: CLIENTE
    2 => [
        "Inicio" => "../Home\Home.php",

        "Mis datos" => [
            "Direcciones" => "../Perfil/Perfil.php",
           "Tarjetas" => "../Perfil/Formas_pago.php"
        ],
        
        "Carrito" => "../Carrito\carrito.php",
        
    ],

    // Rol 3: PROVEEDOR
    3 => [
        "Tienda" => [
            "Ver Productos" => "../Catalogo/catalogo.php",
            "Promociones" => "../Catalogo/promociones.php"
        ],
        "Mis Compras" => [
            "Historial" => "../Cliente/historial_compras.php"
        ],
        "Carrito" => [
            "Ver Carrito" => "../Carrito/carrito.php"
        ],
        "Perfil" => [
            "Perfil" => "../Perfil/Perfil.php"
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
    <link rel="stylesheet" href="../CSS/Recursos.css" type="text/css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<header class="cabecera_p">
    <div class="cabecera">

        <!-- Logo + Nombre del sistema -->
        <div class="logo-nombre">
            <a href="../Home/Home.php">
                <img src="../Imagenes/acc_logo.png" class="img-logo" alt="Logo">
            </a>
            <h1 class="nom_sis">Aplica Central Creativa</h1>
        </div>

        <!-- Carrito y botón perfil centrados -->
        <div class="centro-cabecera">
            <div class="carrito-icono">
            <a href="../Carrito\carrito.php">
                <img src="../Imagenes/carrito.png" alt="Carrito" class="carrito-img">
                <span id="contador-carrito">0</span>
            </a>
            </div>
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
