<?php
include '../BD/ConexionBD.php';
include '../Nav/header.php';


if (!isset($_GET['id_direccion'])) {
    die("No se especificó la dirección.");
}

$id_direccion = $_GET['id_direccion'];

// Obtener datos actuales de la dirección
$query = "SELECT * FROM direccion WHERE id_direccion = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_direccion);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Dirección no encontrada.");
}

$direccion = $resultado->fetch_assoc();

// Verifica que la dirección pertenezca al cliente actual
$id_usuario = $_SESSION['id_usuario'];
$query_cliente = "SELECT id_cliente FROM cliente WHERE id_usuario = ?";
$stmt2 = $conn->prepare($query_cliente);
$stmt2->bind_param("i", $id_usuario);
$stmt2->execute();
$res = $stmt2->get_result();
$cliente = $res->fetch_assoc();

if ($cliente['id_cliente'] != $direccion['id_cliente']) {
    die("No tienes permiso para editar esta dirección.");
}

// Actualizar datos si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $calle = $_POST['calle'];
    $num_ext = $_POST['num_ext'];
    $colonia = $_POST['colonia'];
    $ciudad = $_POST['ciudad'];
    $codigo_postal = $_POST['codigo_postal'];

    $update_query = "UPDATE direccion SET calle = ?, num_ext = ?, colonia = ?, ciudad = ?, codigo_postal = ? WHERE id_direccion = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sisssi", $calle, $num_ext, $colonia, $ciudad, $codigo_postal, $id_direccion);


    if ($stmt->execute()) {
        header("Location: perfil.php");
        exit();
    } else {
        echo "Error al actualizar la dirección: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Dirección</title>
    <link rel="stylesheet" href="Perfil.css"> <!-- Asegúrate que este archivo contiene tu estilo .direccion-container -->
</head>
<body>

<div class="direccion-container">
    <h2>Editar Dirección</h2>

    <form method="POST">
        <label>Calle:
            <input type="text" name="calle" value="<?= htmlspecialchars($direccion['calle']) ?>" required>
        </label>
        <label>Número ext:
            <input type="number" name="num_ext" value="<?= htmlspecialchars($direccion['num_ext']) ?>" required>
        </label>
        <label>Colonia:
            <input type="text" name="colonia" value="<?= htmlspecialchars($direccion['colonia']) ?>" required>
        </label>
        <label>Ciudad:
            <input type="text" name="ciudad" value="<?= htmlspecialchars($direccion['ciudad']) ?>" required>
        </label>
        <label>Código Postal:
            <input type="text" name="codigo_postal" maxlength="5" value="<?= htmlspecialchars($direccion['codigo_postal']) ?>" required>
        </label>
        <input class="btn" type="submit" value="Actualizar Dirección">
        <a href="perfil.php" class="edit-link">Cancelar</a>
    </form>
</div>

</body>
<?php
include ('../Nav/footer.php');
?>
</html>
