<?php
include('../../BD/ConexionBDB.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_banco = $_POST['id_banco'];
    $nombre_banco = $_POST['nombre_banco'];

    $query = "UPDATE banco SET nombre_banco = ? WHERE id_banco = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $nombre_banco, $id_banco);
    $stmt->execute();

    header("Location: ../Bancos.php");
    exit();
}
?>
