<?php
include('../BD/ConexionBD.php');

$id_envio = $_POST['id_envio'] ?? null;
$id_paqueteria = $_POST['id_paqueteria'] ?? null;
$id_carrito = $_POST['id_carrito'] ?? null;
$total = $_POST['total'] ?? 0;

if ($id_carrito && $total) {
    $stmt_insert = $conn->prepare("INSERT INTO pedido (id_envio, id_paqueteria, id_carrito, precio_total_pedido) VALUES (?, ?, ?, ?)");
    $stmt_insert->bind_param("iiid", $id_envio, $id_paqueteria, $id_carrito, $total);
    if ($stmt_insert->execute()) {
        header("Location: ../Pago/pagos.php");
    } else {
        echo "Error al insertar pedido: " . $stmt_insert->error;
    }
} else {
    echo "Datos incompletos.";
}
?>
