<?php
include('../BD/ConexionBD.php');

// Obtener detalles del artículo para select (aunque ahora ya no usaremos select para detalle, te lo dejo por si lo necesitas)
$detalles = [];
$sql_detalles = "SELECT id_detalle_articulo, existencia, precio, estatus FROM detalle_articulos";
$result_detalles = $conn->query($sql_detalles);
if ($result_detalles && $result_detalles->num_rows > 0) {
    while ($row = $result_detalles->fetch_assoc()) {
        $detalles[] = $row;
    }
}

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Datos artículo
    $id_articulo = $_POST['id_articulo'];
    $nombre_articulo = $_POST['nombre_articulo'];
    $descripcion = $_POST['descripcion'];

    // Datos detalle artículo
    $existencia = $_POST['existencia'];
    $costo = $_POST['costo'];
    $precio = $_POST['precio'];
    $id_proveedor = $_POST['id_proveedor'];
    $estatus = $_POST['estatus'];
    $iva = $_POST['iva'];

    // Insertar detalle artículo primero
    $sql_detalle = "INSERT INTO detalle_articulos 
        (existencia, costo, precio, id_proveedor, estatus, iva) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_detalle = $conn->prepare($sql_detalle);
    $stmt_detalle->bind_param("iddiss", $existencia, $costo, $precio, $id_proveedor, $estatus, $iva);

    if ($stmt_detalle->execute()) {
        $id_detalle_articulo = $stmt_detalle->insert_id;

        // Insertar artículo con el id_detalle_articulo generado
        $sql_articulo = "INSERT INTO articulos (id_articulo, descripcion, id_detalle_articulo, nombre_articulo) 
                         VALUES (?, ?, ?, ?)";
        $stmt_articulo = $conn->prepare($sql_articulo);
        $stmt_articulo->bind_param("ssis", $id_articulo, $descripcion, $id_detalle_articulo, $nombre_articulo);

        if ($stmt_articulo->execute()) {
            // Subir y guardar múltiples imágenes
            foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
                $nombre_imagen = $_FILES['imagenes']['name'][$key];
                $ruta_temporal = $_FILES['imagenes']['tmp_name'][$key];
                $ruta_destino = "../Imagenes/" . basename($nombre_imagen);

                if (move_uploaded_file($ruta_temporal, $ruta_destino)) {
                    $sql_img = "INSERT INTO imagenes_articulo (id_articulo, nombre_imagen) VALUES (?, ?)";
                    $stmt_img = $conn->prepare($sql_img);
                    $stmt_img->bind_param("ss", $id_articulo, $nombre_imagen);
                    $stmt_img->execute();
                    $stmt_img->close();
                }
            }

            echo "<p style='color:green; text-align:center;'>Artículo agregado correctamente con imágenes.</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>Error al agregar el artículo: " . $stmt_articulo->error . "</p>";
        }

        $stmt_articulo->close();
    } else {
        echo "<p style='color:red; text-align:center;'>Error al agregar detalle del artículo: " . $stmt_detalle->error . "</p>";
    }

    $stmt_detalle->close();
}

// Obtener artículos con existencia y precio
$articulos = [];
$sql_articulos = "SELECT a.*, d.existencia, d.precio FROM articulos a 
                  LEFT JOIN detalle_articulos d ON a.id_detalle_articulo = d.id_detalle_articulo";
$result_articulos = $conn->query($sql_articulos);
if ($result_articulos && $result_articulos->num_rows > 0) {
    while ($row = $result_articulos->fetch_assoc()) {
        $articulos[] = $row;
    }
}

$conn->close();
?>

<?php include ('../Nav/header.php'); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Artículo</title>
    <link rel="stylesheet" href="agregar.css">
</head>
<body>

<div class="form-container">
    <h2>Agregar nuevo artículo</h2>
    <form action="agregar_articulo.php" method="POST" enctype="multipart/form-data">
        <label>ID Artículo:</label>
        <input type="text" name="id_articulo" required>

        <label>Nombre del artículo:</label>
        <input type="text" name="nombre_articulo" required>

        <label>Descripción:</label>
        <textarea name="descripcion" rows="3" required></textarea>

        <fieldset style="border:1px solid #ccc; padding:10px; margin-bottom:15px;">
            <legend><strong>Detalle del artículo</strong></legend>

            <label>Existencia:</label>
            <input type="number" name="existencia" min="0" required>

            <label>Costo:</label>
            <input type="number" step="0.01" name="costo" min="0" required>

            <label>Precio:</label>
            <input type="number" step="0.01" name="precio" min="0" required>

            <label>Proveedor:</label>
            <select name="id_proveedor" required>
                <option value="">Seleccione un proveedor</option>
                <?php
                include('../BD/ConexionBD.php');
                $sql_prov = "SELECT id_usuario, nombre_usuario FROM usuario WHERE id_rol = 3";
                $result_prov = $conn->query($sql_prov);
                if ($result_prov && $result_prov->num_rows > 0) {
                    while ($prov = $result_prov->fetch_assoc()) {
                        echo "<option value='" . $prov['id_usuario'] . "'>" . htmlspecialchars($prov['nombre_usuario']) . "</option>";
                    }
                }
                ?>
            </select>

            <label>Estatus:</label>
            <select name="estatus" required>
                <option value="Disponible">Disponible</option>
                <option value="No Disponible">No Disponible</option>
                <option value="Descontinuado">Descontinuado</option>
            </select>

            <label>IVA (%):</label>
            <input type="number" step="0.01" name="iva" value="16" readonly>
        </fieldset>

        <label>Imágenes:</label>
        <input type="file" name="imagenes[]" accept="image/*" multiple required>

        <input type="submit" value="Agregar Artículo">
    </form>
</div>

<hr><br>

<h2 style="text-align:center;">Artículos existentes</h2>
<table border="1" cellpadding="8" class="tabla-articulos" style="width: 90%; margin: auto;">
    <thead>
        <tr>
            <th>ID Artículo</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Existencia</th>
            <th>Precio</th>
            <th>Imágenes</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($articulos)): ?>
            <?php
            include('../BD/ConexionBD.php'); // Para obtener imágenes
            foreach ($articulos as $articulo):
            ?>
                <tr>
                    <td><?= htmlspecialchars($articulo['id_articulo']) ?></td>
                    <td><?= htmlspecialchars($articulo['nombre_articulo']) ?></td>
                    <td><?= htmlspecialchars($articulo['descripcion']) ?></td>
                    <td><?= htmlspecialchars($articulo['existencia']) ?></td>
                    <td>$<?= number_format($articulo['precio'], 2) ?></td>
                    <td>
                        <?php
                        $id = $articulo['id_articulo'];
                        $sql_imgs = "SELECT nombre_imagen FROM imagenes_articulo WHERE id_articulo = '$id'";
                        $result_imgs = $conn->query($sql_imgs);
                        if ($result_imgs && $result_imgs->num_rows > 0) {
                            while ($img = $result_imgs->fetch_assoc()) {
                                echo '<img src="../Imagenes/' . htmlspecialchars($img['nombre_imagen']) . '" alt="Imagen" width="80" style="margin:5px;">';
                            }
                        } else {
                            echo "Sin imágenes";
                        }
                        ?>
                    </td>
                    <td>
                        <a href="editar_articulo.php?id=<?= htmlspecialchars($articulo['id_articulo']) ?>">🛠 Editar</a> |
                        <a href="eliminar_articulo.php?id=<?= htmlspecialchars($articulo['id_articulo']) ?>" onclick="return confirm('¿Estás seguro de eliminar este artículo?');">🗑 Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php $conn->close(); ?>
        <?php else: ?>
            <tr><td colspan="7" style="text-align:center;">No hay artículos registrados.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include ('../Nav/footer.php'); ?>

</body>
</html>
