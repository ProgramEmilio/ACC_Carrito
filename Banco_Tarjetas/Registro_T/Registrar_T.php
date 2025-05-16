
<?php
include('../../BD/ConexionBDB.php');

// Activar errores de mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id_banco = $_POST['id_banco'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $numero = $_POST['numero_tarjeta'];
    $cvv = $_POST['cvv'];
    $vencimiento = $_POST['fecha_vencimiento'];
    $saldo = $_POST['saldo'];
    $tipo = $_POST['tipo_tarjeta'];
    $red = $_POST['red_pago'];
    $titular = $_POST['titular'];

    $query = "INSERT INTO tarjeta (
        numero_tarjeta, cvv, fecha_vencimiento, saldo,
        tipo_tarjeta, red_pago, titular, id_banco
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);

    $stmt->bind_param('sssdssii', $numero, $cvv, $vencimiento, $saldo, $tipo, $red, $titular, $id_banco);

    $stmt->execute();

    header("Location: ../DetalleBanco.php?id=$id_banco");
    exit();
}
?>