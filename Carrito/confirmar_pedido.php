<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');

$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    echo "Usuario no autenticado.";
    exit;
}

// Obtener cliente
$queryCliente = "SELECT id_cliente,
nom_persona,
apellido_paterno,
apellido_materno,
telefono
FROM cliente WHERE id_usuario = ?";
$stmtCliente = $conn->prepare($queryCliente);
$stmtCliente->bind_param('i', $id_usuario);
$stmtCliente->execute();
$resultCliente = $stmtCliente->get_result();
$cliente = $resultCliente->fetch_assoc();
$id_cliente = $cliente['id_cliente'] ?? null;

if (!$id_cliente) {
    echo "Cliente no encontrado.";
    exit;
}

// Recibir datos del formulario
$forma_entrega = $_POST['forma_entrega'] ?? '';
$direccion_id = $_POST['domicilio_seleccionado'] ?? null;
$paqueteria_id = $_POST['paqueteria'] ?? null;
$articulos = $_POST['articulos'] ?? [];
$cantidades = $_POST['cantidades'] ?? [];

// Mostrar info según forma de entrega

if ($forma_entrega === 'Punto de Entrega') {
    if (isset($paqueteria_id) && is_numeric($paqueteria_id)) {
        $paqueteria_id = (int) $paqueteria_id;

        $sqlPaq = "
          SELECT 
            nombre_paqueteria, descripcion, fecha
          FROM paqueteria
          WHERE id_paqueteria = ?
          LIMIT 1
        ";
        $stmtPaq = $conn->prepare($sqlPaq);
        $stmtPaq->bind_param('i', $paqueteria_id);
        $stmtPaq->execute();
        $resPaq = $stmtPaq->get_result();
        $paqueteria_info = $resPaq->fetch_assoc();

        if ($paqueteria_info) {
            echo "<h2>Datos de la Paquetería</h2>";
            echo "<p><strong>Nombre:</strong> " . htmlspecialchars($paqueteria_info['nombre_paqueteria']) . "</p>";
            echo "<p><strong>Descripción:</strong> " . htmlspecialchars($paqueteria_info['descripcion']) . "</p>";
            echo "<p><strong>Fecha:</strong> " . htmlspecialchars($paqueteria_info['fecha']) . "</p>";
        } else {
            echo "<p style='color:red;'>Paquetería no encontrada.</p>";
        }
    } else {
        echo "<p style='color:red;'>No seleccionaste ninguna paquetería.</p>";
    }
} elseif ($forma_entrega === 'Domicilio') {
    if (isset($direccion_id) && is_numeric($direccion_id)) {
        $direccion_id = (int) $direccion_id;

        $sqlDir = "
          SELECT 
            id_direccion, codigo_postal, calle, num_ext, colonia, ciudad, estado
          FROM direccion
          WHERE id_direccion = ?
          LIMIT 1
        ";
        $stmtDir = $conn->prepare($sqlDir);
        $stmtDir->bind_param('i', $direccion_id);
        $stmtDir->execute();
        $resDir = $stmtDir->get_result();
        $direccion_info = $resDir->fetch_assoc();

        if ($direccion_info) {
            echo "<h2>Dirección seleccionada</h2>";
            echo "<p>"
               . htmlspecialchars($direccion_info['calle']) . " #"
               . htmlspecialchars($direccion_info['num_ext']) . ", "
               . htmlspecialchars($direccion_info['colonia']) . ", "
               . htmlspecialchars($direccion_info['ciudad']) . ", "
               . htmlspecialchars($direccion_info['estado']) . " CP "
               . htmlspecialchars($direccion_info['codigo_postal'])
               . "</p>";
        } else {
            echo "<p style='color:red;'>Dirección no encontrada.</p>";
        }
    } else {
        echo "<p style='color:red;'>No seleccionaste ninguna dirección.</p>";
    }
} else {
    echo "<p style='color:red;'>Forma de entrega no válida o no seleccionada.</p>";
}

// Calcular subtotal y demás montos
$subtotal = 0;

if (is_array($articulos) && is_array($cantidades)) {
    foreach ($articulos as $articulo_id) {
        if (!isset($cantidades[$articulo_id])) continue;

        $cantidad = floatval($cantidades[$articulo_id]);

        $stmtArt = $conn->prepare("
            SELECT dc.precio 
            FROM detalle_carrito dc
            WHERE dc.id_articulo = ?
            LIMIT 1
        ");
        $stmtArt->bind_param('s', $articulo_id);
        $stmtArt->execute();
        $resultArt = $stmtArt->get_result();
        $row = $resultArt->fetch_assoc();

        if ($row) {
            $precio = floatval($row['precio']);
            $subtotal += $precio * $cantidad;
        }
    }
}

$iva = $subtotal * 0.16;
$costo_envio = 0;

// Obtener costo de envío según forma_entrega
$stmtEnvio = $conn->prepare("SELECT costo FROM envio WHERE tipo_envio = ? LIMIT 1");
$stmtEnvio->bind_param("s", $forma_entrega);
$stmtEnvio->execute();
$resultEnvio = $stmtEnvio->get_result();

if ($rowEnvio = $resultEnvio->fetch_assoc()) {
    $costo_envio = floatval($rowEnvio['costo']);
}

$total = $subtotal + $iva + $costo_envio;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Confirmar Pedido</title>
</head>
<body>

<h1>Confirmar Pedido</h1>

<h2>Datos del Cliente</h2>
<p><strong>Nombre:</strong> <?= htmlspecialchars($cliente['nom_persona'] . ' ' . $cliente['apellido_paterno'] . ' ' . $cliente['apellido_materno']) ?></p>
<p><strong>Teléfono:</strong> <?= htmlspecialchars($cliente['telefono']) ?></p>

<p><strong>Forma de entrega:</strong> <?= htmlspecialchars($forma_entrega) ?></p>

<h2>Resumen de compra</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr>
            <th>Artículo ID</th>
            <th>Descripción</th>
            <th>Precio Unitario</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if (is_array($articulos) && is_array($cantidades)) {
        foreach ($articulos as $articulo_id) {
            if (!isset($cantidades[$articulo_id])) continue;

            $cantidad = $cantidades[$articulo_id];

            $stmtArt = $conn->prepare("
                SELECT 
                    a.id_articulo, 
                    a.descripcion, 
                    dc.cantidad, 
                    dc.precio, 
                    dc.importe, 
                    dc.personalizacion,
                    (
                        SELECT valor 
                        FROM articulo_completo ac 
                        WHERE ac.id_articulo = a.id_articulo 
                        AND ac.id_atributo = 3 
                        LIMIT 1
                    ) AS imagen
                FROM detalle_carrito dc
                JOIN articulos a ON a.id_articulo = dc.id_articulo
                WHERE dc.id_articulo = ?
                LIMIT 1
            ");
            $stmtArt->bind_param('s', $articulo_id);
            $stmtArt->execute();
            $resultArt = $stmtArt->get_result();
            $articulo = $resultArt->fetch_assoc();

            if ($articulo) {
                $precio = floatval($articulo['precio']);
                $subtotal_art = $precio * $cantidad;

                echo "<tr>";
                echo "<td>" . htmlspecialchars($articulo['id_articulo']) . "</td>";
                echo "<td>" . htmlspecialchars($articulo['descripcion']) . "</td>";
                echo "<td>$" . number_format($precio, 2) . "</td>";
                echo "<td>" . htmlspecialchars($cantidad) . "</td>";
                echo "<td>$" . number_format($subtotal_art, 2) . "</td>";
                echo "</tr>";
            } else {
                echo "<tr><td colspan='5'>Artículo ID {$articulo_id} no encontrado.</td></tr>";
            }
        }
    }
    ?>
    </tbody>
</table>

<p><strong>Subtotal:</strong> $<?= number_format($subtotal, 2) ?></p>
<p><strong>IVA (16%):</strong> $<?= number_format($iva, 2) ?></p>
<p><strong>Costo de envío:</strong> $<?= number_format($costo_envio, 2) ?></p>
<p><strong>Total:</strong> $<?= number_format($total, 2) ?></p>

<a href="../Carrito/carrito.php">Regresar al carrito</a>
<a href="../Pago/pagos.php">Continuar Pago</a>

</body>
</html>
