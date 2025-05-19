<?php
include('../../BD/ConexionBD.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
        // Insertar en tabla usuario
        $query = "INSERT INTO usuario (nombre_usuario, correo, contraseña, id_rol) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $nombre_usuario, $correo, $clave, $id_rol);
        $stmt->execute();
        $id_usuario = $stmt->insert_id;

        // Insertar en tabla cliente
        $query_cliente = "INSERT INTO cliente (id_usuario, nom_persona, apellido_paterno, apellido_materno, telefono, monedero) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query_cliente);
        $stmt->bind_param("issssd", $id_usuario, $nom_persona, $apellido_paterno, $apellido_materno, $telefono, $monedero);
        $stmt->execute();

        $conn->commit();
        header("Location: ../Usuarios.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error al insertar: " . $e->getMessage();
    }
}
?>
