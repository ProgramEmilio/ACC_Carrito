<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');

$id_articulo = $_GET['id'] ?? null;
if (!$id_articulo) {
    echo "ID de artículo no proporcionado.";
    exit;
}

// Obtener datos del artículo
$sql = "SELECT a.*, d.existencia, d.costo, d.precio, d.estatus
        FROM articulos a
        JOIN detalle_articulos d ON a.id_detalle_articulo = d.id_detalle_articulo
        WHERE a.id_articulo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id_articulo);
$stmt->execute();
$articulo = $stmt->get_result()->fetch_assoc();

// Obtener atributos existentes
$sql_atributos = "SELECT ac.id_articulo_completo, ac.id_atributo, ac.valor, at.nombre 
                  FROM articulo_completo ac
                  JOIN atributos at ON ac.id_atributo = at.id_atributo
                  WHERE ac.id_articulo = ?";
$stmt_attr = $conn->prepare($sql_atributos);
$stmt_attr->bind_param("s", $id_articulo);
$stmt_attr->execute();
$result_attr = $stmt_attr->get_result();

$atributos = [];
while ($row = $result_attr->fetch_assoc()) {
    $atributos[] = $row;
}
if (!empty($_POST['eliminar_atributos'])) {
    foreach ($_POST['eliminar_atributos'] as $id_articulo_completo) {
        $sql_delete_attr = "DELETE FROM articulo_completo WHERE id_articulo_completo = ?";
        $stmt_del = $conn->prepare($sql_delete_attr);
        $stmt_del->bind_param("i", $id_articulo_completo);
        $stmt_del->execute();
    }
}
// Al enviar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $descripcion = $_POST['descripcion'];
    $existencia = $_POST['existencia'];
    $costo = $_POST['costo'];
    $precio = $_POST['precio'];
    $estatus = $_POST['estatus'];

    // Actualizar detalle_articulos
    $sql_update_detalle = "UPDATE detalle_articulos SET existencia=?, costo=?, precio=?, estatus=? WHERE id_detalle_articulo=?";
    $stmt1 = $conn->prepare($sql_update_detalle);
    $stmt1->bind_param("iddsi", $existencia, $costo, $precio, $estatus, $articulo['id_detalle_articulo']);
    $stmt1->execute();

    // Actualizar articulos
    $sql_update_articulo = "UPDATE articulos SET descripcion=? WHERE id_articulo=?";
    $stmt2 = $conn->prepare($sql_update_articulo);
    $stmt2->bind_param("ss", $descripcion, $id_articulo);
    $stmt2->execute();

    // Actualizar atributos existentes
    if (!empty($_POST['atributos'])) {
        foreach ($_POST['atributos'] as $id_articulo_completo => $valor) {
            $sql_update_atributo = "UPDATE articulo_completo SET valor=? WHERE id_articulo_completo=?";
            $stmt3 = $conn->prepare($sql_update_atributo);
            $stmt3->bind_param("si", $valor, $id_articulo_completo);
            $stmt3->execute();
        }
    }

    // Insertar nuevos atributos
    if (!empty($_POST['nuevo_id_atributo']) && !empty($_POST['nuevo_valor_atributo'])) {
        foreach ($_POST['nuevo_id_atributo'] as $index => $id_atributo) {
            $valor_atributo = $_POST['nuevo_valor_atributo'][$index];

            // Validar que venga un ID válido
            if (!empty($id_atributo) && !empty($valor_atributo)) {
                $sql_insert_ac = "INSERT INTO articulo_completo(id_articulo, id_atributo, valor) VALUES (?, ?, ?)";
                $stmt6 = $conn->prepare($sql_insert_ac);
                $stmt6->bind_param("sis", $id_articulo, $id_atributo, $valor_atributo);
                $stmt6->execute();
            }
        }
    }

    echo "<script>alert('Artículo actualizado con éxito.'); window.location.href='agregar_articulo.php';</script>";
    exit;
}

// Obtener lista de atributos disponibles
$sql_lista_atributos = "SELECT id_atributo, nombre FROM atributos ORDER BY nombre";
$result_lista = $conn->query($sql_lista_atributos);
$lista_atributos = $result_lista->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Artículo</title>
    <script>
    const atributosDisponibles = <?= json_encode($lista_atributos) ?>;

    function agregarCampoAtributo() {
        const contenedor = document.getElementById("nuevos_atributos");
        const div = document.createElement("div");

        let options = '<option value="">Selecciona un atributo</option>';
        atributosDisponibles.forEach(attr => {
            options += `<option value="${attr.id_atributo}">${attr.nombre}</option>`;
        });

        div.innerHTML = `
            <select name="nuevo_id_atributo[]" required>${options}</select>
            <input type="text" name="nuevo_valor_atributo[]" placeholder="Valor del atributo" required><br>
        `;
        contenedor.appendChild(div);
    }
    </script>
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
    <select name="estatus" required>
        <option value="Disponible" <?= $articulo['estatus'] == 'Disponible' ? 'selected' : '' ?>>Disponible</option>
        <option value="No Disponible" <?= $articulo['estatus'] == 'No Disponible' ? 'selected' : '' ?>>No Disponible</option>
        <option value="Descontinuado" <?= $articulo['estatus'] == 'Descontinuado' ? 'selected' : '' ?>>Descontinuado</option>
    </select><br><br>

    <h3>Atributos actuales:</h3>
    <?php foreach ($atributos as $atributo): ?>
    <label><?= htmlspecialchars($atributo['nombre']) ?>:</label><br>
    <input type="text" name="atributos[<?= $atributo['id_articulo_completo'] ?>]" 
           value="<?= htmlspecialchars($atributo['valor']) ?>" required>
    <label><input type="radio" name="eliminar_atributos[]" value="<?= $atributo['id_articulo_completo'] ?>"> Eliminar</label><br>
<?php endforeach; ?>

    <h3>Agregar nuevos atributos:</h3>
    <div id="nuevos_atributos"></div>
    <button type="button" onclick="agregarCampoAtributo()">Agregar Atributo</button><br><br>

    <input type="submit" value="Guardar Cambios" class="regresar">
</form>

<a href="agregar_articulo.php" class="regresar">Regresar</a>

</body>
<?php include('../Nav/footer.php'); ?>
</html>
