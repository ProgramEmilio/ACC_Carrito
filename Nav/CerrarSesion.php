<?php
class CerrarSesion {
    public static function cerrar() {
        session_start(); // Iniciar la sesión si no está iniciada
        session_unset(); // Eliminar todas las variables de sesión
        session_destroy(); // Destruir la sesión
        
        // Redirigir al usuario a la página de inicio de sesión
        header("Location: ../Login/login.php");
        exit();
    }
}

// Si se presiona el botón, se ejecuta la función
if (isset($_POST['cerrar_sesion'])) {
    CerrarSesion::cerrar();
}
?>
