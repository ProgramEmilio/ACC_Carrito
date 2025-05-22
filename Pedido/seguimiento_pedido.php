<?php 
include('../BD/ConexionBD.php');
include('../Nav/header.php');

$id_rol = $_SESSION['id_rol']; // 1=Administrador, 2=Cliente, 3=Proveedor
$id_usuario = $_SESSION['id_usuario']; // Solo si es cliente

$queryCliente = "SELECT id_cliente, nom_persona, apellido_paterno, apellido_materno, telefono 
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

// Manejo de actualización de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_seguimiento']) && isset($_POST['nuevo_estado'])) {
    $id_seguimiento = $conn->real_escape_string($_POST['id_seguimiento']);
    $nuevo_estado = $conn->real_escape_string($_POST['nuevo_estado']);
    $estados_validos = ['Enviado', 'En camino', 'Entregado', 'Otro'];

    if (in_array($nuevo_estado, $estados_validos)) {
        $sql_update = "UPDATE seguimiento_pedido SET Estado = '$nuevo_estado' WHERE id_seguimiento_pedido = '$id_seguimiento'";
        if ($conn->query($sql_update) === TRUE) {
            
        } else {
            
        }
    } else {
        echo "<p style='color:red;'>Estado no válido.</p>";
    }
}
// Consulta principal
$sql = "SELECT 
    sp.id_seguimiento_pedido,
    sp.id_pedido,
    sp.Estado,
    CONCAT(c.nom_persona, ' ', c.apellido_paterno, ' ', c.apellido_materno) AS cliente,
    p.fecha_pedido,
    p.precio_total_pedido,
    p.id_carrito,
    paq.nombre_paqueteria,
    d.calle,
    d.num_ext,
    d.colonia,
    d.ciudad,
    d.estado
FROM seguimiento_pedido sp
INNER JOIN cliente c ON sp.id_cliente = c.id_cliente
INNER JOIN pedido p ON sp.id_pedido = p.id_pedido
LEFT JOIN paqueteria paq ON p.id_paqueteria = paq.id_paqueteria
LEFT JOIN direccion d ON p.id_direccion = d.id_direccion";

// Si es cliente, solo mostrar sus pedidos
if ($id_rol == 2) {
    $sql .= " WHERE sp.id_cliente = '$id_cliente'";
}

$sql .= " ORDER BY sp.id_seguimiento_pedido DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seguimiento de Pedidos</title>
</head>
<body>

<h1 class="titulo">Seguimiento de Pedidos</h1>

<?php
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='card'>";
echo "<h3>Pedido #{$row['id_pedido']} - Estado: {$row['Estado']}</h3>";
echo "<h3>Folio seguimiento: {$row['id_seguimiento_pedido']}</h3>";

// Aquí mantenemos el nombre completo del cliente unido
echo "<p><strong>Cliente:</strong> {$row['cliente']}</p>";

// Mostrar la información separada
echo "<p><strong>Fecha del pedido:</strong> {$row['fecha_pedido']}</p>";
echo "<p><strong>Total:</strong> $ {$row['precio_total_pedido']}</p>";

if (!empty($row['nombre_paqueteria'])) {
    echo "<p><strong>Paquetería:</strong> {$row['nombre_paqueteria']}</p>";
} else {
    echo "<p><strong>Envío a domicilio:</strong> {$row['calle']} {$row['num_ext']}, {$row['colonia']}, {$row['ciudad']}, {$row['estado']}</p>";

}

// Mostrar los artículos
$id_carrito = (int)$row['id_carrito'];
$sql_articulos = "SELECT dc.cantidad, dc.precio, dc.personalizacion, a.descripcion 
                  FROM detalle_carrito dc
                  INNER JOIN articulos a ON dc.id_articulo = a.id_articulo
                  WHERE dc.id_carrito = $id_carrito";

$res_articulos = $conn->query($sql_articulos);

echo "<p><strong>Artículos del pedido:</strong></p>";
if ($res_articulos && $res_articulos->num_rows > 0) {
    echo "<table class='tabla_forma_pago'>";
    echo "<tr>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Precio Unitario</th>
            <th>Personalización</th>
          </tr>";
   while ($art = $res_articulos->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$art['descripcion']}</td>";
    echo "<td>{$art['cantidad']}</td>";
    echo "<td>$ {$art['precio']}</td>";
    echo "<td>" . (!empty($art['personalizacion']) ? $art['personalizacion'] : 'N/A') . "</td>";
    echo "</tr>";
}
    echo "</table>";

    if ($id_rol == 2): // Solo los clientes pueden subir personalización ?>
    <form action="subir_personalizacion.php" method="POST" enctype="multipart/form-data" class="form_edu_usuario">
  <input type="hidden" name="id_pedido" value="<?= $row['id_pedido'] ?>">
  <label>Sube tu diseño personalizado:</label><br>
  <input type="file" name="personalizacion" accept="image/*" required>
  <button type="submit" class="regresar">Subir imagen</button>
</form>
<?php endif;
} else {
    echo "<p>No hay artículos para este pedido.</p>";
}
if (isset($_GET['success'], $_GET['id_pedido']) 
    && $_GET['success'] == 1 
    && intval($_GET['id_pedido']) === intval($row['id_pedido'])): ?>
    <p style="color:green; font-weight: bold;">Imagen subida exitosamente.</p>
<?php endif;
// Botones de cambio de estado
if ($id_rol != 2) {
    echo "<form method='POST' class='form_s'>";
    echo "<input type='hidden' name='id_seguimiento' value='{$row['id_seguimiento_pedido']}'>";
    echo "<button type='submit' name='nuevo_estado' value='Enviado' class='btn-estado btn-enviado'>Enviado</button>";
    echo "<button type='submit' name='nuevo_estado' value='En camino' class='btn-estado btn-en-camino'>En camino</button>";
    echo "<button type='submit' name='nuevo_estado' value='Entregado' class='btn-estado btn-entregado'>Entregado</button>";
    echo "<button type='submit' name='nuevo_estado' value='Otro' class='btn-estado btn-otro'>Otro</button>";
    echo "</form>";
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_seguimiento'], $_POST['nuevo_estado']) && $id_rol != 2) {
    $id_seguimiento = $conn->real_escape_string($_POST['id_seguimiento']);
    $nuevo_estado = $conn->real_escape_string($_POST['nuevo_estado']);
    $estados_validos = ['Enviado', 'En camino', 'Entregado', 'Otro'];

    if (in_array($nuevo_estado, $estados_validos)) {
        $sql_update = "UPDATE seguimiento_pedido SET Estado = '$nuevo_estado' WHERE id_seguimiento_pedido = '$id_seguimiento'";
        if ($conn->query($sql_update) !== TRUE) {
            echo "<p style='color:red;'>Error al actualizar el estado.</p>";
        }
    } else {
        echo "<p style='color:red;'>Estado no válido.</p>";
    }
}
echo "</div>";
    }
} else {
    echo "<p>No hay datos de seguimiento disponibles.</p>";
}
?>

<?php include('../Nav/footer.php'); ?>
</body>
</html>
