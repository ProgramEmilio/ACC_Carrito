<?php
include('../BD/ConexionBD.php');
session_start();

$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    echo "Usuario no autenticado.";
    exit;
}

// Obtener datos del cliente
$sqlCliente = "SELECT id_cliente, nom_persona, apellido_paterno, apellido_materno FROM cliente WHERE id_usuario = ?";
$stmt = $conn->prepare($sqlCliente);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$res = $stmt->get_result();
$cliente = $res->fetch_assoc();
$id_cliente = $cliente['id_cliente'] ?? null;

if (!$id_cliente) {
    echo "Cliente no encontrado.";
    exit;
}

$nom_persona = $cliente['nom_persona'];
$apellido_paterno = $cliente['apellido_paterno'];
$apellido_materno = $cliente['apellido_materno'];

// Datos del formulario
$id_carrito = $_POST['id_carrito'] ?? null;
$id_direccion = $_POST['id_direccion'] ?? null;
$id_paqueteria = $_POST['id_paqueteria'] ?? null;
$total = floatval($_POST['total'] ?? 0);
$iva = floatval($_POST['iva'] ?? 0);
$id_envio = intval($_POST['id_envio'] ?? 0);
$articulos = $_POST['articulos'] ?? [];
$detalles = $_POST['detalles'] ?? [];

$direccion_final = ($id_envio === 1) ? $id_direccion : null;
$paqueteria_final = ($id_envio === 2) ? $id_paqueteria : null;

// Insertar pedido
$queryPedido = "INSERT INTO pedido (id_envio, id_paqueteria, id_direccion, id_carrito, precio_total_pedido, iva, fecha_pedido) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
$stmtPedido = $conn->prepare($queryPedido);
$stmtPedido->bind_param('iiiidd', $id_envio, $paqueteria_final, $direccion_final, $id_carrito, $total, $iva);
$stmtPedido->execute();

if ($stmtPedido->affected_rows <= 0) {
    echo "❌ Error al insertar pedido: " . $stmtPedido->error;
    exit;
}

$id_pedido = $conn->insert_id;
$fecha_pedido = date('Y-m-d');
$id_seguimiento_pedido = 'SP' . str_pad($id_pedido, 8, '0', STR_PAD_LEFT);
$tipo_envio = ($id_envio === 1) ? 'Domicilio' : 'Punto de Entrega';
$ieps = null; // Ajusta si tu sistema usa IEPS

// Obtener detalles del carrito
$sqlDetalles = "SELECT dc.id_articulo, a.descripcion, dc.cantidad, dc.precio, dc.personalizacion 
                FROM detalle_carrito dc
                JOIN articulos a ON dc.id_articulo = a.id_articulo
                WHERE dc.id_carrito = ?";
$stmtDetalles = $conn->prepare($sqlDetalles);
$stmtDetalles->bind_param('i', $id_carrito);
$stmtDetalles->execute();
$resDetalles = $stmtDetalles->get_result();

// Insertar en tabla_reporte por cada artículo
$queryInsertReporte = "INSERT INTO tabla_reporte (
    id_seguimiento_pedido, nom_persona, apellido_paterno, apellido_materno,
    id_envio, tipo_envio, id_articulo, descripcion, cantidad, precio,
    importe, personalizacion, id_pedido, iva, ieps, precio_total_pedido, fecha_pedido
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmtInsertReporte = $conn->prepare($queryInsertReporte);

while ($detalle = $resDetalles->fetch_assoc()) {
    $id_articulo = $detalle['id_articulo'];
    $descripcion = $detalle['descripcion'];
    $cantidad = floatval($detalle['cantidad']);
    $precio = floatval($detalle['precio']);
    $importe = $cantidad * $precio;
    $personalizacion = $detalle['personalizacion'] ?? null;

    $stmtInsertReporte = $conn->prepare($queryInsertReporte);
    $stmtInsertReporte->bind_param(
        'sssssisssddsdidd',
        $id_seguimiento_pedido,
        $nom_persona,
        $apellido_paterno,
        $apellido_materno,
        $id_envio,
        $tipo_envio,
        $id_articulo,
        $descripcion,
        $cantidad,
        $precio,
        $importe,
        $personalizacion,
        $id_pedido,
        $iva,
        $ieps,
        $total
    );

    //$stmtInsertReporte->execute();
}

//echo "✅ Pedido y reporte guardados correctamente.";

// Redirigir a pagos
//header("Location: ../Pago/pagos.php?id_pedido=$id_pedido&precio_total_pedido=$total");

?>

<form id="formBC" action="../Pago/pagos.php" method="post">
    <div id="inputsOcultos">
        <?php foreach ($articulos as $index => $id): ?>
            <input type="hidden" name="articulos[]" value="<?= htmlspecialchars($id) ?>">
            <input type="hidden" name="detalles[<?= $id ?>]" value="<?= htmlspecialchars($detalles[$id]) ?>">
        <?php endforeach; ?>
        <input type="hidden" name="id_pedido" value="<?= htmlspecialchars($id_pedido) ?>">
        <input type="hidden" name="precio_total_pedido" value="<?= htmlspecialchars($total) ?>">
    </div>
</form>

<script>
    document.getElementById('formBC').submit(); // También se envía automáticamente
</script>
