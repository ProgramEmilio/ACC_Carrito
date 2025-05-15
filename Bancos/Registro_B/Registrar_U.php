<?php
include('../../BD/ConexionBD.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    date_default_timezone_set('America/Mazatlan');

    $nombre_usuario = $_POST['nombre_usuario'];
    $correo = $_POST['correo'];
    $contraseña = $_POST['contraseña'];
    $id_rol = $_POST['id_rol'];
    $id_departamento = $_POST['id_departamento'];
    $fecha_ingreso = date("Y-m-d H:i:s");

    $nom_persona = $_POST['nom_persona'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $curp = $_POST['curp'];
    $rfc = $_POST['rfc'];
    $telefono = $_POST['telefono'];
    $modo_pago = $_POST['modo_pago'];
    $sueldo = $_POST['sueldo'];

    $codigo_postal = $_POST['codigo_postal'];
    $calle = $_POST['calle'];
    $num_ext = $_POST['num_ext'];
    $colonia = $_POST['colonia'];
    $ciudad = $_POST['ciudad'];


    // Insertar en la tabla usuario
    $sql_usuario = "INSERT INTO usuario (nombre_usuario, correo, contraseña, id_rol, id_departamento, fecha_ingreso)
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_usuario = $conn->prepare($sql_usuario);
    $stmt_usuario->bind_param("sssiss", $nombre_usuario, $correo, $contraseña, $id_rol, $id_departamento, $fecha_ingreso);

    if ($stmt_usuario->execute()) {
        $id_usuario = $stmt_usuario->insert_id;

        // Insertar en la tabla persona
        $sql_persona = "INSERT INTO persona (
            id_usuario, nom_persona, apellido_paterno, apellido_materno, curp, rfc,
            telefono, sueldo, modo_Pago
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_persona = $conn->prepare($sql_persona);
        $stmt_persona->bind_param("issssssds", $id_usuario, $nom_persona, $apellido_paterno, $apellido_materno, $curp, $rfc, $telefono, $sueldo, $modo_pago);

        $sql_persona = "INSERT INTO persona (
            id_usuario, nom_persona, apellido_paterno, apellido_materno, curp, rfc,
            codigo_postal, calle, num_ext, colonia, ciudad,
            telefono, sueldo, modo_Pago
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_persona = $conn->prepare($sql_persona);
        $stmt_persona->bind_param("isssssssisssds", $id_usuario, $nom_persona, $apellido_paterno, $apellido_materno, $curp, $rfc, $codigo_postal,
        $calle, $num_ext, $colonia, $ciudad,
            $telefono, $sueldo, $modo_pago);
        

        if ($stmt_persona->execute()) {
            echo "<script>
                alert('Usuario registrado correctamente.');
                window.location.href = '../Usuario.php';
            </script>";
        } else {
            echo "Error al insertar en persona: " . $stmt_persona->error;
        }

        $stmt_persona->close();
    } else {
        echo "Error al insertar en usuario: " . $stmt_usuario->error;
    }

    $stmt_usuario->close();
    $conn->close();
}
?>
