<?php
include '../BD/ConexionBD.php'; // Tu archivo de conexión

$id_direccion = $_POST['id_direccion'];
$calle = $_POST['calle'];
$num_ext = $_POST['num_ext'];
$colonia = $_POST['colonia'];
$ciudad = $_POST['ciudad'];
$codigo_postal = $_POST['codigo_postal'];

$query = "UPDATE direccion SET codigo_postal = ?, calle = ?, num_ext = ?, colonia = ?, ciudad = ?
          WHERE id_direccion = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssissi", $codigo_postal, $calle, $num_ext, $colonia, $ciudad, $id_direccion);

if ($stmt->execute()) {
    echo "Dirección actualizada correctamente.";
    header("Location: perfil.php");
} else {
    echo "Error al actualizar: " . $conn->error;
}
?>
