<?php
include '../BD/ConexionBD.php'; // Tu archivo de conexión


$id_cliente = $_POST['id_cliente'];
$calle = $_POST['calle'];
$num_ext = $_POST['num_ext'];
$colonia = $_POST['colonia'];
$ciudad = $_POST['ciudad'];
$estado = $_POST['estado'];
$codigo_postal = $_POST['codigo_postal'];

$query = "INSERT INTO direccion (codigo_postal, calle, num_ext, colonia, ciudad, id_cliente,estado)
          VALUES (?, ?, ?, ?, ?, ?,?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssissis", $codigo_postal, $calle, $num_ext, $colonia, $ciudad, $id_cliente,$estado );

if ($stmt->execute()) {
    echo "Dirección guardada correctamente.";
    header("Location: perfil.php");
} else {
    echo "Error al guardar: " . $conn->error;
}
?>
