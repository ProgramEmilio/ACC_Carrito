<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');
// Obtener detalles del artículo (no utilizado directamente)
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
    $id_articulo = $_POST['id_articulo'];
    $nombre_articulo = $_POST['nombre_articulo'];
    $descripcion = $_POST['descripcion'];

    $existencia = $_POST['existencia'];
    $costo = $_POST['costo'];
    $precio = $_POST['precio'];
    $id_proveedor = $_POST['id_proveedor'] ?? 1; // Asigna un valor por defecto si no se usa proveedor
    $estatus = $_POST['estatus'];
    $iva = $_POST['iva'] ?? 0;

    // Insertar en detalle_articulos
    $sql_detalle = "INSERT INTO detalle_articulos (existencia, costo, precio, id_proveedor, estatus, iva) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_detalle = $conn->prepare($sql_detalle);
    $stmt_detalle->bind_param("iddiss", $existencia, $costo, $precio, $id_proveedor, $estatus, $iva);

    if ($stmt_detalle->execute()) {
        $id_detalle_articulo = $stmt_detalle->insert_id;

        // Insertar en articulos
        $sql_articulo = "INSERT INTO articulos (id_articulo, descripcion, id_detalle_articulo, nombre_articulo) VALUES (?, ?, ?, ?)";
        $stmt_articulo = $conn->prepare($sql_articulo);
        $stmt_articulo->bind_param("ssis", $id_articulo, $descripcion, $id_detalle_articulo, $nombre_articulo);

        if ($stmt_articulo->execute()) {

            // Guardar imágenes
            if (!empty($_FILES['imagenes']['name'][0])) {
                foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
                    $nombre_imagen = basename($_FILES['imagenes']['name'][$key]);
                    $ruta_destino = "../Imagenes/" . $nombre_imagen;

                    if (move_uploaded_file($tmp_name, $ruta_destino)) {
                        $sql_img = "INSERT INTO imagenes_articulo (id_articulo, nombre_imagen) VALUES (?, ?)";
                        $stmt_img = $conn->prepare($sql_img);
                        $stmt_img->bind_param("ss", $id_articulo, $nombre_imagen);
                        $stmt_img->execute();
                        $stmt_img->close();
                    }
                }
            }

            // Guardar atributos
            if (!empty($_POST['atributos_id']) && !empty($_POST['atributos_valor'])) {
                foreach ($_POST['atributos_id'] as $index => $id_atributo) {
                    $valor = $_POST['atributos_valor'][$index];
                    $sql_atributo = "INSERT INTO articulo_completo (id_articulo, id_atributo, valor) VALUES (?, ?, ?)";
                    $stmt_attr = $conn->prepare($sql_atributo);
                    $stmt_attr->bind_param("sis", $id_articulo, $id_atributo, $valor);
                    $stmt_attr->execute();
                    $stmt_attr->close();
                }
            }

            echo "<p style='color:green; text-align:center;'>Artículo agregado correctamente.</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>Error al insertar artículo: " . $stmt_articulo->error . "</p>";
        }

        $stmt_articulo->close();
    } else {
        echo "<p style='color:red; text-align:center;'>Error al insertar detalle del artículo: " . $stmt_detalle->error . "</p>";
    }

    $stmt_detalle->close();
}

// Obtener artículos con detalle
$articulos = [];
$sql_articulos = "SELECT a.*, d.existencia, d.costo, d.precio, d.estatus FROM articulos a 
                  LEFT JOIN detalle_articulos d ON a.id_detalle_articulo = d.id_detalle_articulo";
$result_articulos = $conn->query($sql_articulos);
if ($result_articulos && $result_articulos->num_rows > 0) {
    while ($row = $result_articulos->fetch_assoc()) {
        $articulos[] = $row;
    }
}

// Obtener imágenes por artículo
$imagenes_articulo = [];
$sql_imagenes = "SELECT id_articulo, valor FROM articulo_completo ac 
                 JOIN atributos at ON ac.id_atributo = at.id_atributo
                 WHERE at.nombre = 'Imagen'";
$result_imagenes = $conn->query($sql_imagenes);
if ($result_imagenes && $result_imagenes->num_rows > 0) {
    while ($row = $result_imagenes->fetch_assoc()) {
        $imagenes_articulo[$row['id_articulo']] = $row['valor'];
    }
}

// Obtener atributos para el formulario
$atributos = [];
$sql_atributos = "SELECT id_atributo, nombre FROM atributos";
$result_atributos = $conn->query($sql_atributos);
if ($result_atributos && $result_atributos->num_rows > 0) {
    while ($row = $result_atributos->fetch_assoc()) {
        $atributos[] = $row;
    }
}

// Obtener atributos y valores de cada artículo
$atributos_articulo = [];
$sql_attr_values = "SELECT ac.id_articulo, at.nombre AS atributo, ac.valor 
                    FROM articulo_completo ac
                    JOIN atributos at ON ac.id_atributo = at.id_atributo";
$result_attr_values = $conn->query($sql_attr_values);
if ($result_attr_values && $result_attr_values->num_rows > 0) {
    while ($row = $result_attr_values->fetch_assoc()) {
        $id_articulo = $row['id_articulo'];
        if (!isset($atributos_articulo[$id_articulo])) {
            $atributos_articulo[$id_articulo] = [];
        }
        $atributos_articulo[$id_articulo][] = [
            'atributo' => $row['atributo'],
            'valor' => $row['valor']
        ];
    }
}


$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Artículo</title>
</head>
<body>


<h1 class="titulo">Artículos existentes</h1>
<table border="1" cellpadding="8" class="tabla-articulos" style="width: 90%; margin: auto;">
    <thead>
        <tr>
            <th>ID Artículo</th>
            <th>Imagen</th>
            <th>Descripción</th>
            <th>Existencia</th>
            <th>Costo</th>
            <th>Precio</th>
            <th>Estatus</th>
            <th>Atributos</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($articulos)): ?>
            <?php foreach ($articulos as $articulo): ?>
                <tr>
                    <td><?= htmlspecialchars($articulo['id_articulo']) ?></td>
                    <td>
                        <?php if (!empty($imagenes_articulo[$articulo['id_articulo']])): ?>
                            <img src="../Imagenes/<?= htmlspecialchars($imagenes_articulo[$articulo['id_articulo']]) ?>" width="100" height="100">
                        <?php else: ?>
                            Sin imagen
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($articulo['descripcion']) ?></td>
                    <td><?= htmlspecialchars($articulo['existencia']) ?></td>
                    <td>$<?= number_format($articulo['costo'], 2) ?></td>
                    <td>$<?= number_format($articulo['precio'], 2) ?></td>
                    <td><?= htmlspecialchars($articulo['estatus']) ?></td>
                    <td>
    <?php if (!empty($atributos_articulo[$articulo['id_articulo']])): ?>
        <ul style="list-style: none; padding: 0;">
            <?php foreach ($atributos_articulo[$articulo['id_articulo']] as $attr): ?>
                <li><strong><?= htmlspecialchars($attr['atributo']) ?>:</strong> <?= htmlspecialchars($attr['valor']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        Sin atributos
    <?php endif; ?>
</td>

<td>
    <a href="editar_articulo.php?id=<?= urlencode($articulo['id_articulo']) ?>" >Editar</a> |
    <a href="eliminar_articulo.php?id=<?= urlencode($articulo['id_articulo']) ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar este artículo?');">Eliminar</a>
</td>

                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">No hay artículos registrados.</td></tr>
        <?php endif; ?>
    </tbody>
</table>


<h2 class="titulo">Agregar nuevo artículo</h2>

<form action="registro_articulo.php" method="POST" enctype="multipart/form-data" class="form_reg_usuario">
    <h3>Datos del Artículo</h3>
    <label>ID Artículo:</label><br>
    <input type="text" name="id_articulo" required><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" required></textarea><br>

    <h3>Detalle del Artículo</h3>
    <label>Existencia:</label><br>
    <input type="number" name="existencia" required><br>

    <label>Costo:</label><br>
    <input type="number" step="0.01" name="costo" required><br>

    <label>Precio:</label><br>
    <input type="number" step="0.01" name="precio" required><br>

    <label>Estatus:</label><br>
    <select name="estatus" required>
        <option value="Disponible">Disponible</option>
        <option value="No Disponible">No Disponible</option>
        <option value="Descontinuado">Descontinuado</option>
    </select><br>

    <h3>Atributos</h3>
    <div id="atributos-container">
        <div class="atributo-group">
            <select name="atributos_id[]" required>
                <option value="">-- Selecciona un atributo --</option>
                <?php foreach ($atributos as $atributo): ?>
                    <option value="<?= $atributo['id_atributo'] ?>"><?= htmlspecialchars($atributo['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="atributos_valor[]" placeholder="Valor del atributo" required>
        </div>
    </div>
    <button type="button" onclick="agregarAtributo()">+ Agregar otro atributo</button>

    <br><br>
    <input type="submit" value="Registrar Artículo">
</form>

<script>
    const opcionesAtributos = `<?php
        foreach ($atributos as $atributo) {
            echo '<option value="' . $atributo['id_atributo'] . '">' . htmlspecialchars($atributo['nombre'], ENT_QUOTES) . '</option>';
        }
    ?>`;

    function agregarAtributo() {
        const container = document.getElementById('atributos-container');
        const div = document.createElement('div');
        div.className = 'atributo-group';
        div.innerHTML = `
            <select name="atributos_id[]" required>
                <option value="">-- Selecciona un atributo --</option>
                ${opcionesAtributos}
            </select>
            <input type="text" name="atributos_valor[]" placeholder="Valor del atributo" required>
        `;
        container.appendChild(div);
    }
</script>

<hr><br>
<?php include('../Nav/footer.php'); ?>
</body>
</html>
