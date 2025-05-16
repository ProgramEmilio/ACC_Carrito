<?php
include('../BD/ConexionBD.php');

// Recibir datos
$id_usuario = $_POST['id_usuario'];
$nom_persona = $_POST['nom_persona'];
$apellido_paterno = $_POST['apellido_paterno'];
$apellido_materno = $_POST['apellido_materno'];
$telefono = $_POST['telefono'];
$monedero=$_POST['monedero'];

// Insertar en tabla persona
$sql = "INSERT INTO cliente (id_usuario, nom_persona, apellido_paterno, apellido_materno, telefono, monedero)
VALUES ('$id_usuario', '$nom_persona', '$apellido_paterno', '$apellido_materno','$telefono','$monedero')";

if ($conn->query($sql) === TRUE) {
    header("Location: ../Home/Home.php");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
