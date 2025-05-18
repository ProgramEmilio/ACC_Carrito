<?php
include('../BD/ConexionBD.php');
session_start();

$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    echo "Usuario no autenticado.";
    exit;
}

// Obtener id_cliente
$sqlCliente = "SELECT id_cliente FROM cliente WHERE id_usuario = ?";
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

// Variables del formulario
$forma_entrega = $_POST['id_envio'] ?? ''; 
$id_carrito = $_POST['id_carrito'] ?? null;
$direccion_id = $_POST['id_direccion'] ?? null; 
$paqueteria_id = $_POST['id_paqueteria'] ?? null; 
$total = $_POST['total'] ?? 0.0;
$iva = $_POST['iva'] ?? null; 
$id_envio = (int) ($_POST['id_envio'] ?? 0);


// Ajustar valores nulos correctamente
$direccion_final = ($forma_entrega === 'Domicilio') ? (int)$direccion_id : null;
$paqueteria_final = ($forma_entrega === 'Punto de Entrega') ? (int)$paqueteria_id : null;

// Preparar e insertar pedido
$queryPedido = "INSERT INTO pedido (id_envio, id_paqueteria, id_direccion, id_carrito, precio_total_pedido,iva) 
                VALUES (?, ?, ?, ?, ?,?)";
$stmtPedido = $conn->prepare($queryPedido);


// Asegurar que valores null se pasen como tipo esperado
$stmtPedido->bind_param(
    "iiiidd",
    $id_envio,
    $paqueteria_final,
    $direccion_final,
    $id_carrito,
    $total,
    $iva
);

$success = $stmtPedido->execute();

if ($success) {
    // Obtener el ID del pedido recién insertado
    $id_pedido = $conn->insert_id;

    // Redirigir a la página de pagos con los parámetros necesarios
    header("Location: ../Pago/pagos.php?id_pedido=$id_pedido&precio_total_pedido=$total");
    exit;
} else {
    echo "❌ Error al guardar el pedido: " . $stmtPedido->error;
    echo "<br><br><a href='javascript:history.back()'>Volver e intentar de nuevo</a>";
}
?>