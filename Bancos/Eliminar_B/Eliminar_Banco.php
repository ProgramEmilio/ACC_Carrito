<?php
include('../../BD/ConexionBDB.php');
session_start();

$id_banco = $_GET['id'];

$conn->begin_transaction();

try {
    // Eliminar tarjetas relacionadas
    $query_tarjetas = "DELETE FROM tarjeta WHERE id_banco = ?";
    $stmt = $conn->prepare($query_tarjetas);
    $stmt->bind_param("i", $id_banco);
    $stmt->execute();

    // Eliminar el banco
    $query_banco = "DELETE FROM banco WHERE id_banco = ?";
    $stmt = $conn->prepare($query_banco);
    $stmt->bind_param("i", $id_banco);
    $stmt->execute();

    $conn->commit();
    header("Location: ../Bancos.php"); // o donde tengas tu lista de bancos
    exit();
} catch (Exception $e) {
    $conn->rollback();
    echo "Error al eliminar banco: " . $e->getMessage();
}
?>
