<?php 
include('../BD/ConexionBD.php');
session_start();
include('../BD/ConexionBD.php');

// Lógica de actualización del estado (ANTES de cualquier salida)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['nuevo_estado'])) {
    $id_pedido = intval($_POST['id_pedido']);
    $nuevo_estado = $_POST['nuevo_estado'];

    $updateQuery = "UPDATE seguimiento_pedido SET Estado = ? WHERE id_pedido = ?";
    $stmtUpdate = $conn->prepare($updateQuery);
    if ($stmtUpdate) {
        $stmtUpdate->bind_param("si", $nuevo_estado, $id_pedido);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
include('../Nav/header.php');

$id_rol = $_SESSION['id_rol']; // 1=Administrador, 2=Cliente, 3=Proveedor
$id_usuario = $_SESSION['id_usuario'];

// Solo si es cliente, obtener su id_cliente
$id_cliente = null;
if ($id_rol == 2) {
    $queryCliente = "SELECT id_cliente FROM cliente WHERE id_usuario = ?";
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
}

// Consulta principal de seguimiento de pedidos
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
        echo "<p><strong>Cliente:</strong> {$row['cliente']}</p>";
        echo "<p><strong>Fecha del pedido:</strong> {$row['fecha_pedido']}</p>";
        echo "<p><strong>Total:</strong> $ {$row['precio_total_pedido']}</p>";

        if (!empty($row['nombre_paqueteria'])) {
            echo "<p><strong>Paquetería:</strong> {$row['nombre_paqueteria']}</p>";
        } else {
            echo "<p><strong>Envío a domicilio:</strong> {$row['calle']} {$row['num_ext']}, {$row['colonia']}, {$row['ciudad']}, {$row['estado']}</p>";
        }

        // Consulta de artículos
        $sql_articulos = "SELECT descripcion, cantidad, precio, personalizacion 
                          FROM tabla_reporte 
                          WHERE id_pedido = ?";
        $stmt_articulos = $conn->prepare($sql_articulos);
        $stmt_articulos->bind_param("i", $row['id_pedido']);
        $stmt_articulos->execute();
        $res_articulos = $stmt_articulos->get_result();

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
        } else {
            echo "<p>No hay artículos registrados para este pedido.</p>";
        }
        $stmt_articulos->close();

        // Subir imagen personalizada (solo para clientes)
        if ($id_rol == 2): ?>
        <form action="subir_personalizacion.php" method="POST" enctype="multipart/form-data" class="form_edu_usuario">
            <input type="hidden" name="id_pedido" value="<?= $row['id_pedido'] ?>">
            <label>Sube tu diseño personalizado:</label><br>
            <input type="file" name="personalizacion" accept="image/*" required>
            <button type="submit" class="regresar">Subir imagen</button>
        </form>
        <?php endif;

        // Confirmación de imagen subida
        if (isset($_GET['success'], $_GET['id_pedido']) 
            && $_GET['success'] == 1 
            && intval($_GET['id_pedido']) === intval($row['id_pedido'])) {
            echo "<p style='color:green; font-weight: bold;'>Imagen subida exitosamente.</p>";
        }

        // Botones de cambio de estado (no para cliente)
        if ($id_rol != 2) {
            echo "<form method='POST' class='form_s'>";
            echo "<input type='hidden' name='id_pedido' value='{$row['id_pedido']}'>";
            echo "<button type='submit' name='nuevo_estado' value='Enviado' class='btn-estado btn-enviado'>Enviado</button>";
            echo "<button type='submit' name='nuevo_estado' value='En camino' class='btn-estado btn-en-camino'>En camino</button>";
            echo "<button type='submit' name='nuevo_estado' value='Entregado' class='btn-estado btn-entregado'>Entregado</button>";
            echo "<button type='submit' name='nuevo_estado' value='Otro' class='btn-estado btn-otro'>Otro</button>";
            echo "</form>";
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
