<?php
include('../BD/ConexionBD.php');

if (isset($_GET['id'])) {
    $id_articulo = $_GET['id'];

    // Primero obtener el nombre de la imagen para eliminarla del servidor
    $sql_img = "SELECT imagen FROM articulos WHERE id_articulo = ?";
    $stmt_img = $conn->prepare($sql_img);
    $stmt_img->bind_param("s", $id_articulo);
    $stmt_img->execute();
    $stmt_img->bind_result($imagen);
    $stmt_img->fetch();
    $stmt_img->close();

    if (!empty($imagen)) {
        $ruta = "../Imagenes/" . $imagen;
        if (file_exists($ruta)) {
            unlink($ruta); // elimina el archivo de imagen
        }
    }

    // Eliminar de la base de datos
    $sql = "DELETE FROM articulos WHERE id_articulo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id_articulo);

    if ($stmt->execute()) {
        header("Location: ../Articulos/agregar_articulo.php?msg=eliminado");
    } else {
        echo "Error al eliminar el artículo: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "ID de artículo no proporcionado.";
}

$conn->close();
?>
