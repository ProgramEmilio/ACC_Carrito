<?php
session_start();
include '../Nav.php';
include '../BD/ConexionBD.php';

$id_usuario = $_SESSION['id_usuario'];

// Obtener el id_cliente
$query_cliente = "SELECT id_cliente FROM cliente WHERE id_usuario = ?";
$stmt = $conn->prepare($query_cliente);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$id_cliente = $row['id_cliente'];

// Obtener los datos de la dirección
$query_direccion = "SELECT * FROM direccion WHERE id_cliente = ?";
$stmt = $conn->prepare($query_direccion);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$direccion_result = $stmt->get_result();
$direccion = $direccion_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Dirección</title>
    <link rel="stylesheet" href="Perfil.css">
</head>
<body>

<div class="direccion-container">
    <h2>Editar Dirección</h2>

    <form method="POST" action="actualizar_direccion.php">
        <input type="hidden" name="id_direccion" value="<?= $direccion['id_direccion'] ?>">

        <label>Calle:
            <input type="text" name="calle" value="<?= htmlspecialchars($direccion['calle']) ?>" required>
        </label>
        <label>Número ext:
            <input type="number" name="num_ext" value="<?= $direccion['num_ext'] ?>" required>
        </label>
        <label>Colonia:
            <input type="text" name="colonia" value="<?= htmlspecialchars($direccion['colonia']) ?>" required>
        </label>
        <label>Ciudad:
            <input type="text" name="ciudad" value="<?= htmlspecialchars($direccion['ciudad']) ?>" required>
        </label>
        <label>Código Postal:
            <input type="text" name="codigo_postal" maxlength="5" value="<?= $direccion['codigo_postal'] ?>" required>
        </label>

        <input class="btn" type="submit" value="Actualizar Dirección">
    </form>
</div>

</body>
</html>
