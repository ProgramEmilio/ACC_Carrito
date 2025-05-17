<?php
session_start();
include '../BD/ConexionBD.php';

if (isset($_GET['id_direccion'])) {
    $id_direccion = $_GET['id_direccion'];

    // Verificar que la dirección pertenece al cliente actual
    $id_usuario = $_SESSION['id_usuario'];
    $query = "SELECT d.id_direccion FROM direccion d
              JOIN cliente c ON d.id_cliente = c.id_cliente
              WHERE d.id_direccion = ? AND c.id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id_direccion, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $delete_query = "DELETE FROM direccion WHERE id_direccion = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $id_direccion);
        $delete_stmt->execute();
    }
    header("Location: perfil.php");
    exit();
} else {
    echo "ID de dirección no proporcionado.";
}
?>
