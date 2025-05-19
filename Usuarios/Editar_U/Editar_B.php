<?php
include('../../BD/ConexionBD.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = $_POST['id_usuario'];

    $nombre_usuario = $_POST['nombre_usuario'];
    $correo = $_POST['correo'];
    $clave = $_POST['contraseña'];
    $id_rol = $_POST['id_rol'];

    $nom_persona = $_POST['nom_persona'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $telefono = $_POST['telefono'];
    $monedero = $_POST['monedero'];

    $conn->begin_transaction();

    try {
        // Actualizar tabla usuario
        $update_usuario = "UPDATE usuario SET nombre_usuario = ?, correo = ?, contraseña = ?, id_rol = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($update_usuario);
        $stmt->bind_param("sssii", $nombre_usuario, $correo, $clave, $id_rol, $id_usuario);
        $stmt->execute();

        // Actualizar tabla cliente
        $update_cliente = "UPDATE cliente SET nom_persona = ?, apellido_paterno = ?, apellido_materno = ?, telefono = ?, monedero = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($update_cliente);
        $stmt->bind_param("ssssdi", $nom_persona, $apellido_paterno, $apellido_materno, $telefono, $monedero, $id_usuario);
        $stmt->execute();

        $conn->commit();
        header("Location: ../Usuarios.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error al actualizar: " . $e->getMessage();
    }
} else {
    echo "Acceso no permitido.";
}
?>
