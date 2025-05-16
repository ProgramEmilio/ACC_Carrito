<?php
include('../../BD/ConexionBDB.php');

// Activar errores de mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Verificar que se han recibido los parámetros necesarios
if (isset($_GET['id_tarjeta']) && isset($_GET['id_banco'])) {
    $id_tarjeta = $_GET['id_tarjeta'];
    $id_banco = $_GET['id_banco'];
    
    // Preparar consulta para eliminar la tarjeta
    $query = "DELETE FROM tarjeta WHERE id_tarjeta = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_tarjeta);
    
    // Ejecutar la consulta
    $stmt->execute();
    
    // Verificar si se eliminó correctamente
    if ($stmt->affected_rows > 0) {
        // Éxito - Redireccionar a la página de detalles del banco
        header("Location: ../DetalleBanco.php?id=$id_banco&eliminado=1");
        exit();
    } else {
        // Error - La tarjeta no existe o ya fue eliminada
        header("Location: ../DetalleBanco.php?id=$id_banco&error=1");
        exit();
    }
} else {
    // No se proporcionaron los parámetros necesarios
    echo "<script>
        alert('Faltan parámetros requeridos.');
        window.history.back();
    </script>";
    exit();
}

/**/
?>
