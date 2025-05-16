<?php
include('../../BD/ConexionBDB.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_banco = $_POST['nombre_banco'];

    $query = "INSERT INTO banco (nombre_banco) VALUES (?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nombre_banco);
    $stmt->execute();

    header("Location: ../Bancos.php");
    exit();
}
?>
