<?php
include('../../BD/ConexionBDB.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = $_POST['id_usuario']; // <-- ESTE ES EL FALTANTE

    $nombre_usuario = $_POST['nombre_usuario'];
    $correo = $_POST['correo'];
    $clave = $_POST['contraseña'];

    $nombre_cliente = $_POST['nombre_cliente'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $codigo_postal = $_POST['codigo_postal'];
    $calle = $_POST['calle'];
    $num_ext = $_POST['num_ext'];
    $colonia = $_POST['colonia'];
    $ciudad = $_POST['ciudad'];
    $telefono = $_POST['telefono'];

    $conn->begin_transaction();

    try {
        // Actualizar tabla usuario
        $update_usuario = "UPDATE usuario SET nombre_usuario = ?, correo = ?, contraseña = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($update_usuario);
        $stmt->bind_param("sssi", $nombre_usuario, $correo, $clave, $id_usuario);
        $stmt->execute();

        // Actualizar tabla cliente
        $update_cliente = "UPDATE cliente SET nombre_cliente = ?, apellido_paterno = ?, apellido_materno = ?, codigo_postal = ?, calle = ?, num_ext = ?, colonia = ?, ciudad = ?, telefono = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($update_cliente);
        $stmt->bind_param("sssssisssi", $nombre_cliente, $apellido_paterno, $apellido_materno, $codigo_postal, $calle, $num_ext, $colonia, $ciudad, $telefono, $id_usuario);
        $stmt->execute();

        $conn->commit();
        header("Location: ../Usuarios_B.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error al actualizar: " . $e->getMessage();
    }
} else {
    echo "Acceso no permitido.";
}
?>
