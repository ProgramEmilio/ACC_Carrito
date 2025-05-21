<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Pedidos</title>
    <link rel="stylesheet" href="reporte_ventas.css">
    <script>
        function toggleDetalle(id) {
            var detalle = document.getElementById("detalle_" + id);
            detalle.style.display = (detalle.style.display === "none") ? "block" : "none";
        }
    </script>
</head>
<body>

<form method="GET" action="" class="form_reg_usuario">
    <label for="fecha_inicio">Fecha de inicio:</label>
    <input type="date" name="fecha_inicio" required value="<?php echo isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : ''; ?>">


    <label for="fecha_fin">Fecha de fin:</label>
    <input type="date" name="fecha_fin" required value="<?php echo isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : ''; ?>">

    <button type="submit" class="regresar">Filtrar</button>

    
    <?php if (!empty($_GET['fecha_inicio']) || !empty($_GET['fecha_fin'])): ?>
        <a href="resumen_detalle.php" class="regresar" style="margin-left: 10px;">Mostrar todos</a>
    <?php endif; ?>
</form>

<h2>Reporte de Pedidos</h2>

<?php
$sql = "SELECT 
            p.id_pedido,
            p.precio_total_pedido,
            p.iva,
            p.ieps,
            c.id_carrito,
            cli.nom_persona AS nombre_cliente,
            e.tipo_envio,
            e.costo AS costo_envio,
            paq.nombre_paqueteria,
            dc.id_articulo,
            a.descripcion,
            dc.cantidad,
            dc.precio,
            dc.importe,
            dc.personalizacion,
            d.calle,
            d.num_ext,
            d.colonia,
            d.ciudad,
            d.estado,
            d.codigo_postal,
            p.fecha_pedido
        FROM pedido p
        JOIN carrito c ON p.id_carrito = c.id_carrito
        JOIN cliente cli ON c.id_cliente = cli.id_cliente
        JOIN envio e ON p.id_envio = e.id_envio
        LEFT JOIN paqueteria paq ON p.id_paqueteria = paq.id_paqueteria
        JOIN detalle_carrito dc ON c.id_carrito = dc.id_carrito
        JOIN articulos a ON dc.id_articulo = a.id_articulo
        LEFT JOIN direccion d ON p.id_direccion = d.id_direccion";

$where_clauses = [];

if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin = $_GET['fecha_fin'];
    $where_clauses[] = "p.fecha_pedido BETWEEN '$fecha_inicio' AND '$fecha_fin'";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY p.id_pedido";

$resultado = mysqli_query($conn, $sql);

$pedidos = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $id = $fila['id_pedido'];
    if (!isset($pedidos[$id])) {
        $pedidos[$id] = [
            'cliente' => $fila['nombre_cliente'],
            'total' => $fila['precio_total_pedido'],
            'iva' => $fila['iva'],
            'ieps' => $fila['ieps'],
            'tipo_envio' => $fila['tipo_envio'],
            'costo_envio' => $fila['costo_envio'],
            'direccion' => "{$fila['calle']} {$fila['num_ext']}, {$fila['colonia']}, {$fila['ciudad']}, {$fila['estado']} C.P. {$fila['codigo_postal']}",
            'detalles' => [],
            'fecha_pedido' => $fila['fecha_pedido'],
        ];
    }
    $pedidos[$id]['detalles'][] = [
        'id_articulo' => $fila['id_articulo'],
        'descripcion' => $fila['descripcion'],
        'cantidad' => $fila['cantidad'],
        'precio' => $fila['precio'],
        'importe' => $fila['importe'],
        'personalizacion' => $fila['personalizacion']
    ];
}

if (!empty($pedidos)) {
    foreach ($pedidos as $id => $pedido) {
        echo "<div class='pedido-card'>";
        echo "<div class='pedido-header' onclick='toggleDetalle($id)'>";
        echo "<span>Pedido #: $id</span>";
        echo "<span>Cliente: {$pedido['cliente']}</span>";
        echo "<span>Total: $" . number_format($pedido['total'], 2) . "</span>";
        echo "</div>";

        echo "<div class='pedido-detalle' id='detalle_$id'>";
        echo "<div class='pago-info'>";
        echo "<span><span class='etiqueta'>IVA:</span> $" . number_format($pedido['iva'], 2) . "</span><br>";
        echo "<span><span class='etiqueta'>IEPS:</span> $" . number_format($pedido['ieps'], 2) . "</span><br>";
        echo "<span><span class='etiqueta'>Envío:</span> {$pedido['tipo_envio']} - $" . number_format($pedido['costo_envio'], 2) . "</span><br>";
        //echo "<span><span class='etiqueta'>Dirección:</span> {$pedido['direccion']}</span><br>";
        echo "<span><span class='etiqueta'>Fecha pedido:</span> {$pedido['fecha_pedido']}</span><br>";
        echo "</div>";

        echo "<div class='productos'>";
        foreach ($pedido['detalles'] as $detalle) {
            echo "<div class='producto'>";
            echo "<div><span class='etiqueta'>ID Artículo:</span> {$detalle['id_articulo']}</div>";
            echo "<div><span class='etiqueta'>Descripción:</span> {$detalle['descripcion']}</div>";
            echo "<div><span class='etiqueta'>Cantidad:</span> {$detalle['cantidad']}</div>";
            echo "<div><span class='etiqueta'>Precio:</span> $" . number_format($detalle['precio'], 2) . "</div>";
            echo "<div><span class='etiqueta'>Importe:</span> $" . number_format($detalle['importe'], 2) . "</div>";
            echo "<div><span class='etiqueta'>Personalización:</span> {$detalle['personalizacion']}</div>";
            echo "</div>";
        }
        echo "</div>"; // fin productos
        echo "</div>"; // fin detalle
        echo "</div>"; // fin pedido-card
    }
} else {
    echo "<p>No se encontraron pedidos.</p>";
}
?>

<?php include('../Nav/footer.php'); ?>
</body>
</html>
