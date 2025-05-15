<?php
include('../../BD/ConexionBD.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_POST['id_usuario'];
    $id_persona = $_POST['id_persona'];

    // Usuario
    $nombre_usuario = $_POST['nombre_usuario'];
    $correo = $_POST['correo'];
    $contraseña = $_POST['contraseña'];
    $id_rol = $_POST['id_rol'];
    $id_departamento = $_POST['id_departamento'];

    // Persona
    $nom_persona = $_POST['nom_persona'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $curp = $_POST['curp'];
    $rfc = $_POST['rfc'];
    $telefono = $_POST['telefono'];
    $codigo_postal = $_POST['codigo_postal'];
    $calle = $_POST['calle'];
    $num_ext = $_POST['num_ext'];
    $colonia = $_POST['colonia'];
    $ciudad = $_POST['ciudad'];
    $modo_pago = $_POST['modo_pago'];
    $sueldo = $_POST['sueldo'];

    // Actualizar usuario
    $sql_usuario = "UPDATE usuario 
                    SET nombre_usuario=?, correo=?, contraseña=?, id_rol=?, id_departamento=? 
                    WHERE id_usuario=?";
    $stmt_usuario = $conn->prepare($sql_usuario);
    $stmt_usuario->bind_param("sssiii", $nombre_usuario, $correo, $contraseña, $id_rol, $id_departamento, $id_usuario);
    $stmt_usuario->execute();

    // Actualizar persona
    $sql_persona = "UPDATE persona 
                    SET nom_persona=?, apellido_paterno=?, apellido_materno=?, curp=?, rfc=?, telefono=?, 
                        codigo_postal=?, calle=?, num_ext=?, colonia=?, ciudad=?, modo_Pago=?, sueldo=? 
                    WHERE id_persona=?";
    $stmt_persona = $conn->prepare($sql_persona);
    $stmt_persona->bind_param("ssssssssisssdi", 
        $nom_persona, $apellido_paterno, $apellido_materno, $curp, $rfc, $telefono, 
        $codigo_postal, $calle, $num_ext, $colonia, $ciudad, $modo_pago, $sueldo, $id_persona
    );
    $stmt_persona->execute();

    header("Location: ../Usuario.php?modificado=ok");
    exit();
} else {
    echo "Acceso no permitido.";
}
?>
