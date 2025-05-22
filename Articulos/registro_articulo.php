<?php
include('../BD/ConexionBD.php');
echo '<pre>';
print_r($_POST);
echo '</pre>';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Datos del artículo
    $id_articulo = $_POST['id_articulo'];
    $descripcion = $_POST['descripcion'];

    // Detalle del artículo
    $existencia = $_POST['existencia'];
    $costo = $_POST['costo'];
    $precio = $_POST['precio'];
    $estatus = $_POST['estatus'];
    $iva = 0.16; // Puedes modificar este valor según tu lógica
    $id_proveedor = 1; // Si no estás seleccionando proveedor, pon un ID por defecto

    // 1. Insertar en detalle_articulos
    $sql_detalle = "INSERT INTO detalle_articulos (existencia, costo, precio, id_proveedor, estatus)
                VALUES (?, ?, ?, ?, ?)";
        $stmt_detalle = $conn->prepare($sql_detalle);
        $stmt_detalle->bind_param("iddis", $existencia, $costo, $precio, $id_proveedor, $estatus);


    if ($stmt_detalle->execute()) {
        $id_detalle_articulo = $stmt_detalle->insert_id;

        // 2. Insertar en articulos
        $sql_articulo = "INSERT INTO articulos (id_articulo, descripcion, id_detalle_articulo)
                         VALUES (?, ?, ?)";
        $stmt_articulo = $conn->prepare($sql_articulo);
        $stmt_articulo->bind_param("ssi", $id_articulo, $descripcion, $id_detalle_articulo);

        if ($stmt_articulo->execute()) {

            // 4. Insertar atributos
            if (isset($_POST['atributos_id']) && isset($_POST['atributos_valor'])) {
                $atributos_id = $_POST['atributos_id'];
                $atributos_valor = $_POST['atributos_valor'];

                for ($i = 0; $i < count($atributos_id); $i++) {
                    $id_atributo = $atributos_id[$i];
                    $valor = $atributos_valor[$i];

                    $sql_atr = "INSERT INTO articulo_completo (id_articulo,id_atributo, valor)
                                VALUES (?,?, ?)";
                    $stmt_atr = $conn->prepare($sql_atr);
                    $stmt_atr->bind_param("sis",$id_articulo, $id_atributo, $valor);
                    $stmt_atr->execute();
                    $stmt_atr->close();
                }
            }

            echo "<script>alert('Artículo registrado correctamente.'); window.location.href='agregar_articulo.php';</script>";
        } else {
            echo "<p style='color:red'>Error al insertar el artículo: " . $stmt_articulo->error . "</p>";
        }

        $stmt_articulo->close();
    } else {
        echo "<p style='color:red'>Error al insertar el detalle del artículo: " . $stmt_detalle->error . "</p>";
    }

    $stmt_detalle->close();
}

$conn->close();
?>
