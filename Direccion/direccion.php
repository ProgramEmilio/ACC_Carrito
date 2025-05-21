<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');

$id_articulo = $_POST['articulos'] ?? [];
$cantidades = $_POST['cantidades'] ?? [];
$detalles = $_POST['detalles'] ?? [];
$total_carrito = $_POST['total'] ?? '';
$id_carrito = $_POST['id_carrito'] ?? '';
$id_usuario = $_SESSION['id_usuario'] ?? null;


if (!$id_usuario) {
    echo "Usuario no autenticado.";
    exit;
}

// Obtener cliente
$queryCliente = "SELECT id_cliente FROM cliente WHERE id_usuario = ?";
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

// Obtener direcciones
$queryDireccion = "SELECT id_direccion, calle, num_ext, colonia, ciudad, estado, codigo_postal 
FROM direccion WHERE id_cliente = ?";
$stmtDireccion = $conn->prepare($queryDireccion);
$stmtDireccion->bind_param('i', $id_cliente);
$stmtDireccion->execute();
$resultDirecciones = $stmtDireccion->get_result();
$direccionesArray = [];
while ($row = $resultDirecciones->fetch_assoc()) {
    $direccionesArray[] = $row;
}

// Costos de envío
$sql_envios = "SELECT tipo_envio, costo FROM envio";
$result_envios = $conn->query($sql_envios);
$costo_envio_domicilio = 0;
$costo_retiro = 0;
while ($row = $result_envios->fetch_assoc()) {
    if ($row['tipo_envio'] === 'Domicilio') {
        $costo_envio_domicilio = floatval($row['costo']);
    } elseif ($row['tipo_envio'] === 'Punto de Entrega') {
        $costo_retiro = floatval($row['costo']);
    }
}

// Paqueterías
$sql_paqueterias = "SELECT id_paqueteria, nombre_paqueteria FROM paqueteria";
$result_paqueterias = $conn->query($sql_paqueterias);
$lista_paqueterias = [];
while ($row = $result_paqueterias->fetch_assoc()) {
    $lista_paqueterias[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Dirección y Resumen de Compra</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
</head>
<body>
<h1 class="titulo">Dirección y Resumen de Compra</h1>
<h3 class="subt">Elige la forma de entrega</h3>

<div class="container_dir">
    <div class="left-section">
        <form action="../Carrito/confirmar_pedido.php" method="POST" id="formEntrega"> 
            <h3>Formas de entrega</h3>

            <label>
                <input type="radio" name="forma_entrega" value="Domicilio" checked>
                Envío a domicilio (costo $<?= number_format($costo_envio_domicilio, 2) ?>)
            </label>

            <div id="direcciones-container" style="margin-left:20px; margin-top:10px;">
                <h4>Direcciones registradas</h4>
                <?php if (count($direccionesArray) > 0): ?>
                    <?php foreach ($direccionesArray as $dir): ?>
                        <div class="direccion">
                            <?= htmlspecialchars($dir['calle']) ?> #<?= htmlspecialchars($dir['num_ext']) ?>, 
                            <?= htmlspecialchars($dir['colonia']) ?>, <?= htmlspecialchars($dir['ciudad']) ?>, 
                            <?= htmlspecialchars($dir['estado']) ?>, CP <?= htmlspecialchars($dir['codigo_postal']) ?>
                            <br>
                            <!-- <a href="../Perfil/Perfil.php?id=<?= $dir['id_direccion'] ?>" class="boton btn-editar">Editar</a>-->
                            <label>
                                <input type="radio" name="id_direccion" value="<?= $dir['id_direccion'] ?>">
                                Elegir esta dirección
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No tienes direcciones registradas. <a href="nuevo_domicilio.php" class="boton">Agregar nuevo</a></p>
                <?php endif; ?>
            </div>

            <label style="margin-top:20px;">
                <input type="radio" name="forma_entrega" value="Punto de Entrega" onclick="mostrarPaqueterias(true)">
                Retiro en punto de entrega (costo $<?= number_format($costo_retiro, 2) ?>)
            </label>

            <div id="paqueteria-container" style="margin-left:20px; display:none;">
                <h4>Selecciona la paquetería:</h4>
                <?php foreach ($lista_paqueterias as $paq): ?>
                    <label>
                        <input type="radio" name="id_paqueteria" value="<?= $paq['id_paqueteria'] ?>">
                        <?= $paq['nombre_paqueteria'] ?>
                    </label><br>
                <?php endforeach; ?>
            </div>

            <div>
                <a href="../Carrito/carrito.php" class="boton">Regresar</a>
                <button type="submit" class="boton_conf">Confirmar pedido</button>
        
            </div>

            <div id="inputsOcultos">
                <input type="hidden" name="id_carrito" value="<?= htmlspecialchars($id_carrito) ?>">
                <?php foreach ($id_articulo as $index => $id): ?>
                    <input type="hidden" name="articulos[]" value="<?= htmlspecialchars($id) ?>">
                    <input type="hidden" name="cantidades[<?= $id ?>]" value="<?= htmlspecialchars($cantidades[$id]) ?>">
                    <input type="hidden" name="detalles[<?= $id ?>]" value="<?= htmlspecialchars($detalles[$id]) ?>">
                <?php endforeach; ?>
            </div>
        </form>
    </div>

    <div class="right-section">
        <h2>Resumen de compra</h2>
        <table class="table_resumen">
            <thead>
                <tr>
                    <th>Artículo</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalSubtotal = 0;
                $totalIVA = 0;
                foreach ($id_articulo as $id) {
                    $cantidad = $cantidades[$id] ?? 1;

                    $query = "
                        SELECT a.descripcion, da.precio
                        FROM articulos a
                        JOIN detalle_articulos da ON a.id_detalle_articulo = da.id_detalle_articulo
                        WHERE a.id_articulo = ?
                    ";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param('s', $id);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $row = $res->fetch_assoc();

                    $subtotal = $row['precio'] * $cantidad;
                    $iva = $subtotal * 0.16;
                    $totalSubtotal += $subtotal;
                    $totalIVA += $iva;

                    echo "<tr>
                        <td>" . htmlspecialchars($row['descripcion']) . "</td>
                        <td>$" . number_format($row['precio'], 2) . "</td>
                        <td>$cantidad</td>
                        <td>$" . number_format($subtotal, 2) . "</td>
                    </tr>";
                }

                $totalFinal = $totalSubtotal + $totalIVA;
                ?>
            </tbody>
        </table>
        <p class="parr"><strong>Subtotal:</strong> $<?= number_format($totalSubtotal, 2) ?></p>
        <p class="parr"><strong>IVA:</strong> $<?= number_format($totalIVA, 2) ?></p>
        <p class="parr"><strong>Total:</strong> $<?= number_format($totalFinal, 2) ?></p>
    </div>
</div>
<script>
function mostrarPaqueterias(show) {
    const container = document.getElementById('paqueteria-container');
    container.style.display = show ? 'block' : 'none';
}

document.querySelectorAll('input[name="forma_entrega"]').forEach((input) => {
    input.addEventListener('change', function () {
        mostrarPaqueterias(this.value === 'Punto de Entrega');
    });
});

document.getElementById('formEntrega').addEventListener('submit', function (e) {
    const formaEntrega = document.querySelector('input[name="forma_entrega"]:checked').value;

    if (formaEntrega === 'Domicilio') {
        const direccionSeleccionada = document.querySelector('input[name="id_direccion"]:checked');
        if (!direccionSeleccionada) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Falta seleccionar dirección',
                text: 'Por favor selecciona una dirección para el envío a domicilio.'
            });
            return;
        }
    } else if (formaEntrega === 'Punto de Entrega') {
        const paqueteriaSeleccionada = document.querySelector('input[name="id_paqueteria"]:checked');
        if (!paqueteriaSeleccionada) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Falta seleccionar paquetería',
                text: 'Por favor selecciona una paquetería para el retiro en punto de entrega.'
            });
            return;
        }
    }
});
</script>
<?php
include('../Nav/footer.php');
?>
</body>
</html>
