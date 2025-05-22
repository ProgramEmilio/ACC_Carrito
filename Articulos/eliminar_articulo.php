<?php
include('../BD/ConexionBD.php');

if (isset($_GET['id'])) {
    $id_articulo = $_GET['id'];

    // Eliminar relaciones en otras tablas primero
    $conn->query("DELETE FROM articulo_completo WHERE id_articulo = '$id_articulo'");

    // Obtener el id_detalle_articulo relacionado para eliminarlo después
    $result = $conn->query("SELECT id_detalle_articulo FROM articulos WHERE id_articulo = '$id_articulo'");
    if ($result && $row = $result->fetch_assoc()) {
        $id_detalle = $row['id_detalle_articulo'];

        // Primero eliminar el artículo (que depende del detalle)
        $conn->query("DELETE FROM articulos WHERE id_articulo = '$id_articulo'");

        // Luego eliminar el detalle ya que ya no está referenciado
        $conn->query("DELETE FROM detalle_articulos WHERE id_detalle_articulo = $id_detalle");
    }

    header("Location: agregar_articulo.php");
    exit;
}
?>
