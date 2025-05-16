<?php
include('../BD/ConexionBD.php');
session_start(); // Iniciar sesión para almacenar los datos del usuario

// Verificar conexión a la base de datos
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Verificar si se enviaron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);

    // Consulta para verificar el usuario (incluyendo id_rol)
    $sql = "SELECT u.id_usuario, u.nombre_usuario, u.contraseña, u.id_rol, c.id_cliente 
        FROM usuario u 
        LEFT JOIN cliente c ON u.id_usuario = c.id_usuario 
        WHERE u.correo = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id_usuario, $nombre_usuario, $hash_password, $id_rol, $id_cliente);
        $stmt->fetch();

        // Verificar la contraseña
        if (password_verify($password, $hash_password) || $password === $hash_password) {
            $_SESSION['id_usuario'] = $id_usuario;
            $_SESSION['nombre_usuario'] = $nombre_usuario;
            $_SESSION['id_rol'] = $id_rol;
            $_SESSION['id_cliente'] = $id_cliente;

            // Redirección según el rol
            switch ($id_rol) {
                case 1:
                case 2:
                    header("Location: ../Carrito/carrito.php");
                    break;
                default:
                    header("Location: ../Carrito/carrito.php");
                    break;
            }
            exit();
        } else {
            echo "<script>
                    alert('Contraseña incorrecta.');
                    window.location.href='login.php';
                  </script>";
        }
    } else {
        echo "<script>
                alert('No se encontró el usuario con el correo ingresado.');
                window.location.href='login.php';
              </script>";
    }

    $stmt->close();
} else {
        echo "<script>alert('Error en la consulta SQL.');</script>";
    }
}
?>
