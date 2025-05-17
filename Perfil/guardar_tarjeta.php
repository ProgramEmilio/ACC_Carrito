<?php
include '../BD/ConexionBD.php';
include '../BD/ConexionBD.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = $_POST['numero_tarjeta'];
    $cvv = $_POST['cvv'];
    $fecha = $_POST['fecha_vencimiento'] . '-01'; // convierte "YYYY-MM" en "YYYY-MM-01"
    $saldo = $_POST['saldo'];
    $tipo = $_POST['tipo_tarjeta'];
    $red = $_POST['red_pago'];
    $titular = $_POST['titular'];
    $banco = $_POST['id_banco'];

    $query = "INSERT INTO tarjeta (numero_tarjeta, cvv, fecha_vencimiento, saldo, tipo_tarjeta, red_pago, titular, id_banco) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssdsiii", $numero, $cvv, $fecha, $saldo, $tipo, $red, $titular, $banco);

    if ($stmt->execute()) {
        header("Location: Perfil.php");
        exit();
    } else {
        echo "Error al guardar la tarjeta: " . $stmt->error;
    }
}
?>
