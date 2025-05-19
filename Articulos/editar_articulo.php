<?php
include('../BD/ConexionBD.php');

if (!isset($_GET['id'])) {
    echo "ID de artículo no proporcionado.";
    exit;
}

$id_articulo = $_GET['id'];

// Obtener detalles del artículo
$sql = "SELECT * FROM articulos WHERE id_articulo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id_articulo);
$stmt->execute();
$result = $stmt->get_result();
$articulo = $result->fetch_assoc();
$stmt->close();

if (!$articulo) {
    echo "Artículo no encontrado.";
    exit;
}

// Obtener detalles posibles para el select
$detalles = [];
$sql_detalles = "SELECT id_detalle_articulo, existencia, precio, estatus FROM detalle_articulos";
$result_detalles = $conn->query($sql_detalles);
while ($row = $result_detalles->fetch_assoc()) {
    $detalles[] = $row;
}

// Procesar edición
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_articulo = $_POST['nombre_articulo'];
    $descripcion = $_POST['descripcion'];
    $id_detalle_articulo = $_POST['id_detalle_articulo'];
    $imagen_actual = $_POST['imagen_actual'];

    // Si se seleccionó una nueva imagen
    if (!empty($_FILES['imagen']['name'])) {
        $nombre_imagen = $_FILES['imagen']['name'];
        $ruta_temporal = $_FILES['imagen']['tmp_name'];
        $ruta_destino = "../Imagenes/" . $nombre_imagen;

        if (move_uploaded_file($ruta_temporal, $ruta_destino)) {
            // Borrar imagen anterior
            $ruta_anterior = "../Imagenes/" . $imagen_actual;
            if (file_exists($ruta_anterior)) unlink($ruta_anterior);
        } else {
            echo "Error al subir la nueva imagen.";
            exit;
        }
    } else {
        $nombre_imagen = $imagen_actual;
    }

    $sql_update = "UPDATE articulos SET nombre_articulo = ?, descripcion = ?, id_detalle_articulo = ?, imagen = ? WHERE id_articulo = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssiss", $nombre_articulo, $descripcion, $id_detalle_articulo, $nombre_imagen, $id_articulo);

    if ($stmt_update->execute()) {
        header("Location: agregar_articulo.php?msg=editado");
        exit;
    } else {
        echo "Error al actualizar el artículo: " . $stmt_update->error;
    }

    $stmt_update->close();
    $conn->close();
}
?>

<?php include ('../Nav/header.php'); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Artículo</title>
    <link rel="stylesheet" href="editar.css">
</head>
<body>

<div class="form-container">
    <h2>Editar Artículo</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <label>ID Artículo (no editable):</label><br>
        <input type="text" value="<?= htmlspecialchars($articulo['id_articulo']) ?>" disabled><br><br>

        <label>Nombre del artículo:</label><br>
        <input type="text" name="nombre_articulo" value="<?= htmlspecialchars($articulo['nombre_articulo']) ?>" required><br><br>

        <label>Descripción:</label><br>
        <textarea name="descripcion" required><?= htmlspecialchars($articulo['descripcion']) ?></textarea><br><br>

        <label>Detalle del artículo:</label><br>
        <select name="id_detalle_articulo" required>
            <?php foreach ($detalles as $detalle): ?>
                <option value="<?= $detalle['id_detalle_articulo'] ?>"
                    <?= $detalle['id_detalle_articulo'] == $articulo['id_detalle_articulo'] ? 'selected' : '' ?>>
                    ID <?= $detalle['id_detalle_articulo'] ?> | Existencia: <?= $detalle['existencia'] ?> | Precio: $<?= $detalle['precio'] ?> | <?= $detalle['estatus'] ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Imagen actual:</label><br>
        <img src="../Imagenes/<?= htmlspecialchars($articulo['imagen']) ?>" width="100" alt="Imagen actual"><br>
        <input type="hidden" name="imagen_actual" value="<?= htmlspecialchars($articulo['imagen']) ?>">

        <label>Cambiar imagen (opcional):</label><br>
        <input type="file" name="imagen" accept="image/*"><br><br>

        <input type="submit" value="Guardar Cambios">
        <a href="agregar_articulo.php">Cancelar</a>
    </form>
</div>

</body>
</html>

<?php include ('../Nav/footer.php'); ?>
