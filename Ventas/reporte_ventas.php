<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');

// Manejar fechas
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$modo = $_GET['modo'] ?? 'detalle';

// Filtro de fechas
$filtro_fecha = '';
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $fecha_inicio_esc = $conn->real_escape_string($fecha_inicio);
    $fecha_fin_esc = $conn->real_escape_string($fecha_fin);
    $filtro_fecha = " AND p.fecha BETWEEN '$fecha_inicio_esc 00:00:00' AND '$fecha_fin_esc 23:59:59'";
}

$sql1 = "
SELECT * FROM pedido";

$sql = "
SELECT 
    p.id_pedido,
    CONCAT(c.nom_persona, ' ', c.apellido_paterno, ' ', c.apellido_materno) AS nombre_cliente,
    p.fecha AS fecha_pedido,
    a.descripcion AS producto,
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
JOIN detalle_carrito dc ON dc.id_carrito = p.id_carrito
JOIN articulos a ON dc.id_articulo = a.id_articulo
JOIN pago pa ON pa.id_pedido = p.id_pedido
JOIN formas_pago fp ON pa.id_forma_pago = fp.id_forma_pago
JOIN paqueteria pq ON pq.id_paqueteria = p.id_paqueteria
WHERE 1=1 $filtro_fecha
ORDER BY p.id_pedido DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Ventas</title>
    <link rel="stylesheet" href="reporte_ventas.css">
</head>
<body>

<h2>Reporte de Ventas</h2>

<form method="GET" style="margin-bottom: 20px;">
    <label>Desde: <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>"></label>
    <label>Hasta: <input type="date" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>"></label>
    <input type="hidden" name="modo" value="<?= htmlspecialchars($modo) ?>">
    <button type="submit">Filtrar</button>
</form>

<div style="margin-bottom: 20px;">
    <a href="?modo=detalle&fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>"><button>Ver Detalle</button></a>
    <a href="?modo=resumen&fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>"><button>Ver Resumen</button></a>
</div>

<?php
if ($result && $result->num_rows > 0) {
    $pedidos = [];

    while ($row = $result->fetch_assoc()) {
        $id_pedido = $row['id_pedido'];
        $cliente = $row['nombre_cliente'];

        if (!isset($pedidos[$id_pedido])) {
            $pedidos[$id_pedido] = [
                'nombre_cliente' => $cliente,
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
            'cantidad' => $row['cantidad'],
            'personalizacion' => $row['personalizacion'],
            'importe' => $row['importe']
        ];
    }

    if ($modo === 'resumen') {
        $resumen = [];
        foreach ($pedidos as $pedido) {
            $cliente = $pedido['nombre_cliente'];
            if (!isset($resumen[$cliente])) {
                $resumen[$cliente] = 0;
            }
            $resumen[$cliente] += $pedido['precio_total_pedido'];
        }

        echo "<h3>Resumen de Ventas</h3>";
        echo "<table border='1' cellpadding='5'><tr><th>Cliente</th><th>Monto Total</th></tr>";
        foreach ($resumen as $cliente => $monto) {
            echo "<tr style='cursor:pointer;' onclick=\"window.location.href='?modo=detalle&fecha_inicio=" . urlencode($fecha_inicio) . "&fecha_fin=" . urlencode($fecha_fin) . "#cliente-" . urlencode($cliente) . "'\">";
            echo "<td>" . htmlspecialchars($cliente) . "</td><td>$ " . number_format($monto, 2) . "</td></tr>";
        }
        echo "</table>";
    } else {
        foreach ($pedidos as $id_pedido => $pedido) {
            echo "<div class='pedido-card' id='cliente-" . urlencode($pedido['nombre_cliente']) . "'>
                    <div class='pedido-header'>
                        <div><span class='etiqueta'>ID Pedido:</span> " . htmlspecialchars($id_pedido) . "</div>
                        <div><span class='etiqueta'>Fecha:</span> " . htmlspecialchars($pedido['fecha_pedido']) . "</div>
                    </div>

                    <div class='pedido-info'>
                        <div><span class='etiqueta'>Cliente:</span> " . htmlspecialchars($pedido['nombre_cliente']) . "</div>
                        <div><span class='etiqueta'>Paquetería:</span> " . htmlspecialchars($pedido['nombre_paqueteria']) . "</div>
                    </div>";

            foreach ($pedido['productos'] as $prod) {
                echo "<div class='producto'>
                        <div><span class='etiqueta'>Producto:</span> " . htmlspecialchars($prod['producto']) . "</div>
                        <div><span class='etiqueta'>Cantidad:</span> " . htmlspecialchars($prod['cantidad']) . "</div>
                        <div><span class='etiqueta'>Personalización:</span> " . htmlspecialchars($prod['personalizacion']) . "</div>
                        <div><span class='etiqueta'>Importe:</span> $ " . number_format($prod['importe'], 2) . "</div>
                    </div>";
            }

            echo "<div class='pago-info'>
                    <div><span class='etiqueta'>Total Pedido:</span> $ " . number_format($pedido['precio_total_pedido'], 2) . "</div>
                    <div><span class='etiqueta'>Forma de Pago:</span> " . htmlspecialchars($pedido['forma_pago']) . "</div>
                    <div><span class='etiqueta'>Estado del Pago:</span> " . htmlspecialchars($pedido['estado_pago']) . "</div>
                </div>
            </div>";
        }
    }

    echo "<p>Total de pedidos encontrados: " . count($pedidos) . "</p>";
} else {
    echo "<p>No hay pedidos para mostrar en ese rango de fechas.</p>";
}
?>

</body>
</html>

<?php 
include('../Nav/footer.php'); 
$conn->close(); 
?>
