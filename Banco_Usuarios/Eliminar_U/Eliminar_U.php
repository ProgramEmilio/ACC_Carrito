<?php
include('../../BD/ConexionBDB.php');

$id_usuario = $_GET['id'];

$conn->begin_transaction();

try {
    // Obtener el ID del cliente correspondiente
    $query_cliente = "SELECT id_cliente FROM cliente WHERE id_usuario = ?";
    $stmt = $conn->prepare($query_cliente);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $cliente = $result->fetch_assoc();

    if ($cliente) {
        $id_cliente = $cliente['id_cliente'];

        // Eliminar tarjetas asociadas al cliente
        $query_tarjetas = "DELETE FROM tarjeta WHERE titular = ?";
        $stmt = $conn->prepare($query_tarjetas);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();

        // Eliminar cliente
        $query_delete_cliente = "DELETE FROM cliente WHERE id_usuario = ?";
        $stmt = $conn->prepare($query_delete_cliente);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
    }

    // Eliminar usuario
    $query_delete_usuario = "DELETE FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($query_delete_usuario);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $conn->commit();
    header("Location: ../Usuarios_B.php");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    echo "Error al eliminar: " . $e->getMessage();
}
?>

