<?php
include('../../BD/ConexionBDB.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
        $query = "INSERT INTO usuario (nombre_usuario, correo, contraseña) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $nombre_usuario, $correo, $clave);
        $stmt->execute();
        $id_usuario = $stmt->insert_id;

        $query_cliente = "INSERT INTO cliente (id_usuario, nombre_cliente, apellido_paterno, apellido_materno, codigo_postal, calle, num_ext, colonia, ciudad, telefono) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query_cliente);
        $stmt->bind_param("isssssssss", $id_usuario, $nombre_cliente, $apellido_paterno, $apellido_materno, $codigo_postal, $calle, $num_ext, $colonia, $ciudad, $telefono);
        $stmt->execute();

        $conn->commit();
        header("Location: ../Usuarios_B.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error al insertar: " . $e->getMessage();
    }
}
?>
