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
    $sql = "SELECT id_usuario, nombre_usuario, contraseña, id_rol FROM usuario WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result(); // Almacenar resultado para verificar si hay datos
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id_usuario, $nombre_usuario, $hash_password, $id_rol);
            $stmt->fetch();
            
            // DEBUG: Ver valores en consola del navegador
            echo "<script>console.log('Usuario: ID = $id_usuario, Nombre = $nombre_usuario, Rol = $id_rol');</script>";

            // Verificar si la contraseña es correcta
            if (password_verify($password, $hash_password) || $password === $hash_password) {
                // Guardar datos en sesión
                $_SESSION['id_usuario'] = $id_usuario;
                $_SESSION['nombre_usuario'] = $nombre_usuario;
                $_SESSION['id_rol'] = $id_rol;

                // Redirigir según el rol del usuario
                switch ($id_rol) {
                    case 1: // Administrador
                        header("Location: ../Nomina/Nomina.php");
                        break;
                    case 2: // Cliente
                        header("Location: ../Nomina/Nomina.php");
                        break;
                    case 3: // Proveedor
                        header("Location: ../Compras/Cotizacion/Cotizar.php");
                        break;
                    case 4: // Comprador
                        header("Location: ../Compras/Aprobar/Aprobar_Compra.php");
                        break;
                    case 5: // Vendedor
                        header("Location: ../Venta/Detalle_venta.php");
                        break;
                    case 6: // Producción
                        header("Location: ../Produccion/pro.php");
                        break;
                    case 7: // Distribuidor
                        header("Location: ../Distribucion/Distribucion.php");
                        break;
                    case 8: // Responsable stock
                        header("Location: ../Inventario/Inventario.php");
                        break;
                    default:
                        header("Location: ../Nomina/Nomina.php"); // Si el rol no existe, ir a una página por defecto
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
