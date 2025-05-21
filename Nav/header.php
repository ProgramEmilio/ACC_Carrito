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
$id_usuario = $_SESSION['id_usuario']; 

$id_rol = $_SESSION['id_rol'];

$menus = [
    // Rol 1: Administrador
    1 => [
        "Usuarios" => "../Usuarios\Usuarios.php",

        "Catálogo" => "../Home/Home.php",
        
        "Banco" => [
            "Clientes" => "../Banco_Usuarios\Usuarios_B.php",
            "Bancos" => "../Bancos\Bancos.php"
        ],

        "Registros" => "../Ventas/Resumen_detalle.php",
        "Seguimiento Pedidos" => "../Pedido/seguimiento_pedido.php"
    
    ],

    // Rol 2: CLIENTE
    2 => [
        "Mis pedidos" => "#",

        "" => [
            "" => "",
           "" => ""
        ],
        
        "" => "",
        
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

// Obtener cliente
$queryCliente = "SELECT id_cliente 
FROM cliente WHERE id_usuario = ?";
$stmtCliente = $conn->prepare($queryCliente);
$stmtCliente->bind_param('i', $id_usuario);
$stmtCliente->execute();
$resultCliente = $stmtCliente->get_result();
$cliente = $resultCliente->fetch_assoc();
$id_cliente = $cliente['id_cliente'] ?? null;

if (!$id_cliente) {
    echo "Cliente no encontrado.";
    exit;
}

// Obtener ID del carrito activo
$sql_carrito = "SELECT id_carrito FROM carrito WHERE id_cliente = ?";
$stmt = $conn->prepare($sql_carrito);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$id_carrito = $row['id_carrito'] ?? null;

// Obtener ID de detalle_carrito
// Obtener los IDs de detalle_carrito para un carrito dado
$sql_detalle_ids = "SELECT id_detalle_carrito FROM detalle_carrito WHERE id_carrito = ?";
$stmt = $conn->prepare($sql_detalle_ids);
$stmt->bind_param("i", $id_carrito);
$stmt->execute();
$result = $stmt->get_result();

$detalle_ids = [];
while ($row = $result->fetch_assoc()) {
    $detalle_ids[] = $row['id_detalle_carrito'];
}


// Sumar la cantidad total de artículos en el carrito
$sql_sum_cantidad = "SELECT SUM(cantidad) AS total_cantidad FROM detalle_carrito WHERE id_carrito = ?";
$stmt = $conn->prepare($sql_sum_cantidad);
$stmt->bind_param("i", $id_carrito);
$stmt->execute();
$result = $stmt->get_result();

$total_cantidad = 0;
if ($row = $result->fetch_assoc()) {
    $total_cantidad = $row['total_cantidad'] ?? 0;
}


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
    <link rel="stylesheet" href="../CSS/Perfil.css" type="text/css">
    <link rel="stylesheet" href="../CSS/Tarjetas.css" type="text/css">
    <link rel="stylesheet" href="../CSS/Confirmar_pedido.css" type="text/css">
    <link rel="stylesheet" href="../CSS/reporte_ventas.css" type="text/css">

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
<div class="user-carrito-wrapper">
        <!-- Botón cerrar sesión -->
        <!-- Contenedor de usuario con menú desplegable -->
<div class="user-menu-container">
    <div class="user-icon-wrapper" onclick="toggleMenu()">
        <img src="../Imagenes/avatar.png" alt="Usuario" class="user-icon">
    </div>

    <div class="dropdown-menu" id="userDropdown">
        <a href="../Perfil/perfil.php">Perfil</a>
        <form method="POST" style="margin: 0;">
            <button type="submit" name="cerrar_sesion" class="dropdown-logout">Cerrar Sesión</button>
        </form>
    </div>
</div>

<!-- Carrito y botón perfil centrados -->
        <div class="centro-cabecera">
    <div class="carrito-icono">
        <a href="../Carrito/carrito.php">
            <img src="../Imagenes/carrito.png" alt="Carrito" class="carrito-img">
            <span id="contador-carrito"><?php echo intval($total_cantidad); ?></span>
        </a>
    </div>
</div>
    </div>

    </div>
<script>
function toggleMenu() {
    const menu = document.getElementById("userDropdown");
    menu.style.display = menu.style.display === "block" ? "none" : "block";
}

// Cerrar el menú si se hace clic fuera de él
window.addEventListener("click", function(event) {
    const iconWrapper = document.querySelector(".user-icon-wrapper");
    const menu = document.getElementById("userDropdown");

    if (!iconWrapper.contains(event.target)) {
        menu.style.display = "none";
    }
});
</script>
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
