<?php
include('../BD/ConexionBD.php');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_clientes = $_POST['id_cliente'];
    $nombres = $_POST['nom_persona'];
    $apellidos_p = $_POST['apellido_paterno'];
    $apellidos_m = $_POST['apellido_materno'];
    $telefonos = $_POST['telefono'];

    for ($i = 0; $i < count($id_clientes); $i++) {
        $stmt = $conn->prepare("UPDATE cliente SET nom_persona = ?, apellido_paterno = ?, apellido_materno = ?, telefono = ? WHERE id_cliente = ?");
        $stmt->bind_param("ssssi", $nombres[$i], $apellidos_p[$i], $apellidos_m[$i], $telefonos[$i], $id_clientes[$i]);
        $stmt->execute();
    }

    header("Location: Perfil.php");
    exit; 
}
?>