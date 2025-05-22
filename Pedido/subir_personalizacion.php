<?php
include('../BD/ConexionBD.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['personalizacion']) && isset($_POST['id_pedido'])) {
    $id_pedido = intval($_POST['id_pedido']);
    $carpeta_destino = '../Imagenes/';

    if (!is_dir($carpeta_destino)) {
        mkdir($carpeta_destino, 0777, true);
    }

    $archivo_tmp = $_FILES['personalizacion']['tmp_name'];
    $nombre_original = basename($_FILES['personalizacion']['name']);
    $nombre_final = uniqid('pers_') . "_" . $nombre_original;
    $ruta_final = $carpeta_destino . $nombre_final;

    if (move_uploaded_file($archivo_tmp, $ruta_final)) {
        // Guardar la ruta en la base de datos si tienes una tabla para eso (opcional)
        // Ejemplo de tabla 'personalizaciones' con campos (id, id_pedido, ruta_imagen)
        /*
        $ruta_para_db = 'Imagenes/' . $nombre_final;
        $stmt = $conn->prepare("INSERT INTO personalizaciones (id_pedido, ruta_imagen) VALUES (?, ?)");
        $stmt->bind_param("is", $id_pedido, $ruta_para_db);
        $stmt->execute();
        */

        // Redirigir fijo a seguimiento_pedido.php con success=1
        header("Location: seguimiento_pedido.php?success=1&id_pedido=" . $id_pedido);

        exit;
    } else {
        echo "<p style='color:red;'>Error al subir el archivo.</p>";
    }
} else {
    echo "<p style='color:red;'>No se recibió archivo válido o id de pedido.</p>";
}
?>
