<?php
include('../../BD/ConexionBDB.php');

$id_usuario = $_GET['id'];
$conn->begin_transaction();

try {
    $query_cliente = "DELETE FROM cliente WHERE id_usuario = ?";
    $stmt = $conn->prepare($query_cliente);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $query_usuario = "DELETE FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($query_usuario);
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
