<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');
$id_articulo = $_GET['id'] ?? null;
if (!$id_articulo) {
    echo "ID de artículo no proporcionado.";
    exit;
}

// Obtener datos actuales del artículo y detalle
$sql = "SELECT a.*, d.existencia, d.costo, d.precio, d.estatus
        FROM articulos a
        JOIN detalle_articulos d ON a.id_detalle_articulo = d.id_detalle_articulo
        WHERE a.id_articulo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id_articulo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Artículo no encontrado.";
    exit;
}

$articulo = $result->fetch_assoc();

// Si se envió el formulario de edición
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $descripcion = $_POST['descripcion'];
    $existencia = $_POST['existencia'];
    $costo = $_POST['costo'];
    $precio = $_POST['precio'];
    $estatus = $_POST['estatus'];

    // Actualizar detalle_articulos
    $sql_update_detalle = "UPDATE detalle_articulos SET existencia=?, costo=?, precio=?, estatus=?
                           WHERE id_detalle_articulo=?";
    $stmt1 = $conn->prepare($sql_update_detalle);
    $stmt1->bind_param("iddsi", $existencia, $costo, $precio, $estatus, $articulo['id_detalle_articulo']);
    $stmt1->execute();

    // Actualizar articulos
    $sql_update_articulo = "UPDATE articulos SET descripcion=? WHERE id_articulo=?";
    $stmt2 = $conn->prepare($sql_update_articulo);
    $stmt2->bind_param("ss", $descripcion, $id_articulo);
    $stmt2->execute();

    echo "<script>alert('Artículo actualizado con éxito.'); window.history.back();</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title class="titulo">Editar Artículo</title>
</head>
<body>
<h1 class="titulo">Editar Artículo</h1>

<form method="POST" class="form_reg_usuario">
    <label>ID Artículo:</label><br>
    <input type="text" value="<?= htmlspecialchars($articulo['id_articulo']) ?>" disabled><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" required><?= htmlspecialchars($articulo['descripcion']) ?></textarea><br>

    <label>Existencia:</label><br>
    <input type="number" name="existencia" value="<?= htmlspecialchars($articulo['existencia']) ?>" required><br>

    <label>Costo:</label><br>
    <input type="number" step="0.01" name="costo" value="<?= htmlspecialchars($articulo['costo']) ?>" required><br>

    <label>Precio:</label><br>
    <input type="number" step="0.01" name="precio" value="<?= htmlspecialchars($articulo['precio']) ?>" required><br>

    <label>Estatus:</label><br>
    <input type="text" name="estatus" value="<?= htmlspecialchars($articulo['estatus']) ?>" required><br><br>

    <input type="submit" value="Guardar Cambios">
</form>
<a href="agregar_articulo.php" class="regresar">Regresar</a>
</body>
<?php include('../Nav/footer.php'); ?>
</html>
