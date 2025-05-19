<?php
include('../../BD/ConexionBDB.php');

// Activar errores de mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $id_tarjeta = $_POST['id_tarjeta'];
    $id_banco = $_POST['id_banco'];
    $numero = $_POST['numero_tarjeta'];
    $cvv = $_POST['cvv'];
    $vencimiento = $_POST['fecha_vencimiento'];
    $saldo = $_POST['saldo'];
    $tipo = $_POST['tipo_tarjeta'];
    $red = $_POST['red_pago'];
    $titular = $_POST['titular'];

    // Preparar la consulta SQL para actualizar
    $query = "UPDATE tarjeta SET 
                numero_tarjeta = ?,
                cvv = ?,
                fecha_vencimiento = ?,
                saldo = ?,
                tipo_tarjeta = ?,
                red_pago = ?,
                titular = ?
              WHERE id_tarjeta = ?";

    $stmt = $conn2->prepare($query);
    
    // Vincular parámetros
    $stmt->bind_param('sssdssii', $numero, $cvv, $vencimiento, $saldo, $tipo, $red, $titular, $id_tarjeta);
    
    // Ejecutar la consulta
    $stmt->execute();
    
    // Redireccionar de vuelta a la página de detalles del banco
    header("Location: ../DetalleBanco.php?id=$id_banco");
    exit();
} else {
    // Si se accede directamente a este archivo sin enviar datos por POST
    echo "Acceso no autorizado";
    exit();
}
?>