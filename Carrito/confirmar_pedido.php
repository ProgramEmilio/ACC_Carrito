<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');

$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    echo "Usuario no autenticado.";
    exit;
}

// Obtener ID del cliente
$queryCliente = "SELECT id_cliente, nom_persona, apellido_paterno, apellido_materno, telefono FROM cliente WHERE id_usuario = ?";
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

// Mostrar nombre del cliente
echo "<h2>Bienvenido, " . htmlspecialchars($cliente['nom_persona']) . " " . htmlspecialchars($cliente['apellido_paterno']) . " " . htmlspecialchars($cliente['apellido_materno']) . "</h2>";
echo "<p><strong>Teléfono:</strong> " . htmlspecialchars($cliente['telefono']) . "</p>";

// Mostrar direcciones
$sql_direcciones = "SELECT * FROM direccion WHERE id_cliente = ?";
$stmt_dir = $conn->prepare($sql_direcciones);
$stmt_dir->bind_param("i", $id_cliente);
$stmt_dir->execute();
$resultado_dir = $stmt_dir->get_result();

echo "<h3>Elige la forma de entrega</h3>";

if ($resultado_dir->num_rows > 0) {
    while ($direccion = $resultado_dir->fetch_assoc()) {
        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px 0;'>";
        echo "<strong>Dirección:</strong><br>";
        echo htmlspecialchars($direccion['calle']) . " #" . htmlspecialchars($direccion['num_ext']) . ", ";
        echo htmlspecialchars($direccion['colonia']) . ", CP " . htmlspecialchars($direccion['codigo_postal']) . "<br>";
        echo htmlspecialchars($direccion['ciudad']) . ", " . htmlspecialchars($direccion['estado']) . "<br><br>";
        echo "<button onclick=\"alert('Dirección elegida: ID " . $direccion['id_direccion'] . "')\">Elegir esta dirección</button> ";
        echo "<button onclick=\"window.location.href='editar_direccion.php?id=" . $direccion['id_direccion'] . "'\">Editar</button>";
        echo "</div>";
    }
} else {
    echo "<p>No tienes direcciones registradas.</p>";
    echo "<a href='agregar_direccion.php'>Agregar una nueva dirección</a>";
}

// Mostrar artículos del carrito
echo "<h2>Artículos en el carrito</h2>";

$sql_carrito = "
SELECT 
  a.id_articulo,
  a.descripcion,
  ac.valor AS imagen,
  dc.cantidad,
  dc.precio,
  dc.importe,
  dc.personalizacion
FROM carrito ca
JOIN detalle_carrito dc ON ca.id_carrito = dc.id_carrito
JOIN articulos a ON dc.id_articulo = a.id_articulo
LEFT JOIN articulo_completo ac ON a.id_articulo = ac.id_articulo AND ac.id_atributo = 3
WHERE ca.id_cliente = ?
";

$stmt_carrito = $conn->prepare($sql_carrito);
$stmt_carrito->bind_param("i", $id_cliente);
$stmt_carrito->execute();
$resultado_carrito = $stmt_carrito->get_result();

if ($resultado_carrito->num_rows > 0) {
    while ($row = $resultado_carrito->fetch_assoc()) {
        echo "<hr>";
        echo "<p><strong>ID:</strong> " . htmlspecialchars($row["id_articulo"]) . "</p>";
        echo "<p><strong>Descripción:</strong> " . htmlspecialchars($row["descripcion"]) . "</p>";
        echo "<p><strong>Cantidad:</strong> " . htmlspecialchars($row["cantidad"]) . "</p>";
        echo "<p><strong>Precio unitario:</strong> $" . htmlspecialchars(number_format($row["precio"], 2)) . "</p>";
        echo "<p><strong>Importe:</strong> $" . htmlspecialchars(number_format($row["importe"], 2)) . "</p>";
        echo "<p><strong>Personalización:</strong> " . htmlspecialchars($row["personalizacion"]) . "</p>";
        echo "<p><strong>Imagen:</strong> <img src='ruta/imagenes/" . htmlspecialchars($row["imagen"]) . "' width='100'></p>";
    }
} else {
    echo "<p>No hay artículos en el carrito.</p>";
}

// Cierre
$stmtCliente->close();
$stmt_dir->close();
$stmt_carrito->close();
$conn->close();
?>
