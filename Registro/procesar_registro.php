<?php
include('../BD/ConexionBD.php');


// Recibir datos del formulario
$nombre_usuario = $_POST['nombre_usuario'];
$correo = $_POST['correo'];
$contraseña = $_POST['contraseña'];
$id_rol = 2; // Valor por default

// Insertar en tabla usuario
$sql = "INSERT INTO usuario (nombre_usuario, correo, contraseña, id_rol)
        VALUES ('$nombre_usuario', '$correo', '$contraseña', $id_rol)";

if ($conn->query($sql) === TRUE) {
    // Obtener el id_usuario recién insertado
    $id_usuario = $conn->insert_id;

    // Redirigir al formulario persona y enviar el id_usuario
    header("Location: persona.php?id_usuario=$id_usuario");
    exit();
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
