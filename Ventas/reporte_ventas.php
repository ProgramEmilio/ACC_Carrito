<?php
include ('../BD/ConexionBD.php');

$sql = "
SELECT 
    p.id_pedido,
    CONCAT(c.nom_persona, ' ', c.apellido_paterno, ' ', c.apellido_materno) AS nombre_cliente,
    ca.fecha AS fecha_pedido,
    a.descripcion AS producto,
    a.imagen,  -- nombre del archivo imagen desde BD
    dc.cantidad,
    dc.personalizacion,
    dc.importe,
    p.precio_total_pedido,
    fp.forma AS forma_pago,
    fp.estado AS estado_pago,
    pq.nombre_paqueteria
FROM pedido p
JOIN carrito ca ON p.id_carrito = ca.id_carrito
JOIN cliente c ON ca.id_cliente = c.id_cliente
JOIN detalle_carrito dc ON ca.id_carrito = dc.id_carrito
JOIN articulos a ON dc.id_articulo = a.id_articulo
JOIN pago pa ON pa.id_pedido = p.id_pedido
JOIN formas_pago fp ON pa.id_forma_pago = fp.id_forma_pago
JOIN paqueteria pq ON pq.id_paqueteria = p.id_paqueteria
ORDER BY p.id_pedido DESC
";

$result = $conn->query($sql);
?>

<?php include ('../Nav/header.php'); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Ventas</title>
    <link rel="stylesheet" href="reporte_ventas.css">
</head>
<body>

    <h2>Reporte de Ventas</h2>

    <?php
    if ($result && $result->num_rows > 0) {
        $pedidos = [];

        while($row = $result->fetch_assoc()) {
            $id_pedido = $row['id_pedido'];
            if (!isset($pedidos[$id_pedido])) {
                $pedidos[$id_pedido] = [
                    'nombre_cliente' => $row['nombre_cliente'],
                    'fecha_pedido' => $row['fecha_pedido'],
                    'precio_total_pedido' => $row['precio_total_pedido'],
                    'forma_pago' => $row['forma_pago'],
                    'estado_pago' => $row['estado_pago'],
                    'nombre_paqueteria' => $row['nombre_paqueteria'],
                    'productos' => []
                ];
            }

            $pedidos[$id_pedido]['productos'][] = [
                'producto' => $row['producto'],
                'imagen' => $row['imagen'],  // nombre archivo imagen
                'cantidad' => $row['cantidad'],
                'personalizacion' => $row['personalizacion'],
                'importe' => $row['importe']
            ];
        }

        foreach ($pedidos as $id_pedido => $pedido) {
            echo "<div class='pedido-card'>
                    <div class='pedido-header'>
                        <div><span class='etiqueta'>ID Pedido:</span> $id_pedido</div>
                        <div><span class='etiqueta'>Fecha:</span> {$pedido['fecha_pedido']}</div>
                    </div>

                    <div class='pedido-info'>
                        <div><span class='etiqueta'>Cliente:</span> {$pedido['nombre_cliente']}</div>
                        <div><span class='etiqueta'>Paquetería:</span> {$pedido['nombre_paqueteria']}</div>
                    </div>";

            foreach ($pedido['productos'] as $prod) {
                echo "<div class='producto'>
                        <img src='../Imagenes/".htmlspecialchars($prod['imagen'])."' alt='".htmlspecialchars($prod['producto'])."' class='producto-img' style='width:80px; height:80px; object-fit:contain; margin-bottom:5px;'>
                        <div><span class='etiqueta'>Producto:</span> {$prod['producto']}</div>
                        <div><span class='etiqueta'>Cantidad:</span> {$prod['cantidad']}</div>
                        <div><span class='etiqueta'>Personalización:</span> {$prod['personalizacion']}</div>
                        <div><span class='etiqueta'>Importe:</span> $ {$prod['importe']}</div>
                    </div>";
            }

            echo "<div class='pago-info'>
                    <div><span class='etiqueta'>Total Pedido:</span> $ {$pedido['precio_total_pedido']}</div>
                    <div><span class='etiqueta'>Forma de Pago:</span> {$pedido['forma_pago']}</div>
                    <div><span class='etiqueta'>Estado del Pago:</span> {$pedido['estado_pago']}</div>
                </div>
            </div>";
        }

    } else {
        echo "<p>No hay pedidos para mostrar.</p>";
    }
    ?>

</body>
</html>

<?php include ('../Nav/footer.php'); ?>
<?php $conn->close(); ?>
