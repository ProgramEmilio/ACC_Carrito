<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');

$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    echo "Usuario no autenticado.";
    exit;
}

// Obtener cliente
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

// Recibir datos del formulario anterior
$forma_entrega = $_POST['forma_entrega'] ?? '';
$direccion_id = $_POST['domicilio_seleccionado'] ?? null;
$paqueteria_id = $_POST['paqueteria'] ?? null;
$articulos = $_POST['articulos'] ?? [];
$cantidades = $_POST['cantidades'] ?? [];
$id_paqueteria = $_POST['id_paqueteria'] ?? null;
$id_direccion = $_POST['id_direccion'] ?? null;
$subtotal = 0;
if (is_array($articulos) && is_array($cantidades)) {
    foreach ($articulos as $articulo_id) {
        if (!isset($cantidades[$articulo_id])) continue;
        $cantidad = floatval($cantidades[$articulo_id]);

        $stmtArt = $conn->prepare("SELECT precio FROM detalle_carrito WHERE id_articulo = ? LIMIT 1");
        $stmtArt->bind_param('s', $articulo_id);
        $stmtArt->execute();
        $resultArt = $stmtArt->get_result();
        if ($row = $resultArt->fetch_assoc()) {
            $precio = floatval($row['precio']);
            $subtotal += $precio * $cantidad;
        }
    }
}

$iva = $subtotal * 0.16;
$costo_envio = 0;

// Obtener costo de envío
$stmtEnvio = $conn->prepare("SELECT costo FROM envio WHERE tipo_envio = ? LIMIT 1");
$stmtEnvio->bind_param("s", $forma_entrega);
$stmtEnvio->execute();
$resultEnvio = $stmtEnvio->get_result();
if ($rowEnvio = $resultEnvio->fetch_assoc()) {
    $costo_envio = floatval($rowEnvio['costo']);
}

$total = $subtotal + $iva + $costo_envio;

// Obtener ID del carrito activo
$sql_carrito = "SELECT id_carrito FROM carrito WHERE id_cliente = ?";
$stmt = $conn->prepare($sql_carrito);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$id_carrito = $row['id_carrito'] ?? null;

if (is_array($articulos) && is_array($cantidades)) {
    foreach ($articulos as $articulo_id) {
        if (!isset($cantidades[$articulo_id])) continue;

        $cantidad = intval($cantidades[$articulo_id]);

        // Actualizar cantidad en detalle_carrito
        $updateStmt = $conn->prepare("UPDATE detalle_carrito SET cantidad = ? WHERE id_carrito = ? AND id_articulo = ?");
        $updateStmt->bind_param('iis', $cantidad, $id_carrito, $articulo_id);
        $updateStmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Confirmar Pedido</title>
</head>
<body>

<h1 class="titulo">Confirmar Pedido</h1>

<h2 class="sub_titulo">Datos del Cliente</h2>
<p ><strong class="parrafo_pedi">Nombre:</strong> <?= htmlspecialchars($cliente['nom_persona'] . ' ' . $cliente['apellido_paterno'] . ' ' . $cliente['apellido_materno']) ?></p>
<p class="parrafo_pedi"><strong>Teléfono:</strong> <?= htmlspecialchars($cliente['telefono']) ?></p>
<h2 class="sub_titulo">Forma de entrega</h2>
<p class="parrafo_pedi"><strong>Forma de entrega:</strong> <?= htmlspecialchars($forma_entrega) ?></p>

<!-- Dirección o punto de entrega -->
<?php
if ($forma_entrega === 'Punto de Entrega' && $id_paqueteria) {
    $sqlPaq = "SELECT nombre_paqueteria, descripcion, fecha FROM paqueteria WHERE id_paqueteria = ?";
    $stmtPaq = $conn->prepare($sqlPaq);
    $stmtPaq->bind_param('i', $id_paqueteria);
    $stmtPaq->execute();
    $resPaq = $stmtPaq->get_result();
    if ($paqueteria_info = $resPaq->fetch_assoc()) {
        echo "<h2 class='parrafo_pedi'>Datos de la Paquetería</h2>";
        echo "<p class='parrafo_pedi'><strong>Nombre:</strong> " . htmlspecialchars($paqueteria_info['nombre_paqueteria']) . "</p>";
        echo "<p class='parrafo_pedi'><strong>Descripción:</strong> " . htmlspecialchars($paqueteria_info['descripcion']) . "</p>";
        echo "<p class='parrafo_pedi'><strong>Fecha:</strong> " . htmlspecialchars($paqueteria_info['fecha']) . "</p>";
    }
} elseif ($forma_entrega === 'Domicilio' && $id_direccion) {
    $sqlDir = "SELECT calle, num_ext, colonia, ciudad, estado, codigo_postal FROM direccion WHERE id_direccion = ?";
    $stmtDir = $conn->prepare($sqlDir);
    $stmtDir->bind_param('i', $id_direccion);
    $stmtDir->execute();
    $resDir = $stmtDir->get_result();
    if ($direccion_info = $resDir->fetch_assoc()) {
        echo "<h2>Dirección seleccionada</h2>";
        echo "<p class='parrafo_pedi'>" . htmlspecialchars($direccion_info['calle']) . " #" . htmlspecialchars($direccion_info['num_ext']) . ", " .
            htmlspecialchars($direccion_info['colonia']) . ", " . htmlspecialchars($direccion_info['ciudad']) . ", " .
            htmlspecialchars($direccion_info['estado']) . " CP " . htmlspecialchars($direccion_info['codigo_postal']) . "</p>";
    }
}
?>

<h2 class="sub_titulo">Resumen de compra</h2>
<table border="1" cellpadding="6" cellspacing="0" class="table_resumen">
    <thead>
        <tr>
            <th>ID Artículo</th>
            <th>Descripción</th>
            <th>Precio Unitario</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach ($articulos as $articulo_id) {
        $cantidad = $cantidades[$articulo_id] ?? 0;

        $stmtDet = $conn->prepare("
            SELECT a.descripcion, dc.precio 
            FROM detalle_carrito dc 
            JOIN articulos a ON a.id_articulo = dc.id_articulo 
            WHERE dc.id_articulo = ? 
            LIMIT 1
        ");
        $stmtDet->bind_param('s', $articulo_id);
        $stmtDet->execute();
        $resultDet = $stmtDet->get_result();

        if ($row = $resultDet->fetch_assoc()) {
            $descripcion = $row['descripcion'];
            $precio = $row['precio'];
            $sub = $precio * $cantidad;

            echo "<tr>
                    <td>{$articulo_id}</td>
                    <td>" . htmlspecialchars($descripcion) . "</td>
                    <td>$" . number_format($precio, 2) . "</td>
                    <td>{$cantidad}</td>
                    <td>$" . number_format($sub, 2) . "</td>
                  </tr>";
        }
    }
    ?>
    </tbody>
</table>

<h3 class="parr">Subtotal: $<?= number_format($subtotal, 2) ?></h3>
<h3 class="parr">IVA (16%): $<?= number_format($iva, 2) ?></h3>
<h3 class="parr">Costo de Envío: $<?= number_format($costo_envio, 2) ?></h3>
<h2 class="total_pago">Total a Pagar: $<?= number_format($total, 2) ?></h2>

<!-- FORMULARIO PARA CONFIRMAR PEDIDO -->
<form action="pedido.php" method="POST">
<input type="hidden" name="id_envio" value="<?= ($forma_entrega === 'Domicilio') ? 1 : 2 ?>">
    <input type="hidden" name="id_carrito" value="<?= htmlspecialchars($id_carrito) ?>">
    <input type="hidden" name="total" value="<?= htmlspecialchars($total) ?>">
    <input type="hidden" name="iva" value="<?= htmlspecialchars($iva) ?>">

    <?php if ($forma_entrega === 'Domicilio') : ?>
        <input type="hidden" name="id_direccion" value="<?= htmlspecialchars($id_direccion) ?>">
    <?php elseif ($forma_entrega === 'Punto de Entrega') : ?>
        <input type="hidden" name="id_paqueteria" value="<?= htmlspecialchars($id_paqueteria) ?>">
    <?php endif; ?>
    <div class="contenedor_boton">
    <button type="submit" class="boton_pago">Proceder al pago</button>
    </div>
</form>

</body>
<?php
include('../Nav/footer.php');
?>
</html>
