<?php
include('../BD/ConexionBD.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_detalle_carrito'])) {
    $id = intval($_POST['id_detalle_carrito']);

    $stmt = $conn->prepare("DELETE FROM detalle_carrito WHERE id_detalle_carrito = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al eliminar']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Solicitud inválida']);
}
?>
