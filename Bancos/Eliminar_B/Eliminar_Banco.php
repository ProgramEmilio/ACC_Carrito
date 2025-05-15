<?php
include('../../BD/ConexionBD.php');

// Verificar si se recibió el ID del usuario
if (isset($_GET['id_usuario'])) {
    $id_usuario = intval($_GET['id_usuario']); // Asegura que sea número entero

    // Eliminar primero de la tabla 'persona'
    $sql_delete_persona = "DELETE FROM persona WHERE id_usuario = ?";
    $stmt_persona = $conn->prepare($sql_delete_persona);
    $stmt_persona->bind_param("i", $id_usuario);

    // Verificar si se eliminó correctamente en persona
    if ($stmt_persona->execute()) {

        // Ahora eliminar el usuario
        $sql_delete_usuario = "DELETE FROM usuario WHERE id_usuario = ?";
        $stmt_usuario = $conn->prepare($sql_delete_usuario);
        $stmt_usuario->bind_param("i", $id_usuario);

        if ($stmt_usuario->execute()) {
            echo "<script>
                alert('Usuario eliminado correctamente.');
                window.location.href = '../Usuario.php'; // Cambia a tu vista de usuarios
            </script>";
        } else {
            echo "<script>
                alert('Error al eliminar el usuario.');
                window.history.back();
            </script>";
        }

        $stmt_usuario->close();

    } else {
        echo "<script>
            alert('Error al eliminar los datos de persona.');
            window.history.back();
        </script>";
    }

    $stmt_persona->close();
} else {
    echo "<script>
        alert('ID de usuario no recibido.');
        window.history.back();
    </script>";
}

$conn->close();
?>
