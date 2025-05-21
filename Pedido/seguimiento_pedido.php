<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Pedidos</title>
    <script>
        function toggleDetalle(id) {
            var detalle = document.getElementById("detalle_" + id);
            detalle.style.display = (detalle.style.display === "none") ? "block" : "none";
        }
    </script>
</head>
<body>
    <h1 class="titulo">Reporte de Pedidos</h1>
<h3 class="sub_titulo">Filtrado por fechas</h3>
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
<h3 class="sub_titulo">Reportes</h3>
<?php
$sql = "SELECT * FROM tabla_reporte";

$where_clauses = [];

if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin = $_GET['fecha_fin'];
    $where_clauses[] = "fecha_pedido BETWEEN '$fecha_inicio' AND '$fecha_fin'";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY id_pedido";

$resultado = mysqli_query($conn, $sql);

$pedidos = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $id = $fila['id_pedido'];
    if (!isset($pedidos[$id])) {
        $pedidos[$id] = [
            'cliente' => "{$fila['nom_persona']} {$fila['apellido_paterno']} {$fila['apellido_materno']}",
            'total' => $fila['precio_total_pedido'],
            'iva' => $fila['iva'],
            'ieps' => $fila['ieps'],
            'tipo_envio' => $fila['tipo_envio'],
            'fecha_pedido' => $fila['fecha_pedido'],
            'detalles' => [],
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
        echo "<span><span class='etiqueta'>Envío:</span> {$pedido['tipo_envio']}</span><br>";
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
