<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');


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

// Artículos y cantidades enviados por POST
$articulosSeleccionados = $_POST['articulos'] ?? [];
$cantidadesSeleccionadas = $_POST['cantidades'] ?? [];

if (count($articulosSeleccionados) === 0) {
    echo "No se seleccionaron artículos.";
    exit;
}

// Preparar placeholders para IN
$placeholders = implode(',', array_fill(0, count($articulosSeleccionados), '?'));
$tipos = str_repeat('i', count($articulosSeleccionados));

// Consulta resumen del carrito con los artículos seleccionados
$queryResumen = "
    SELECT a.id_articulo, a.descripcion, dc.precio
    FROM carrito c
    JOIN detalle_carrito dc ON c.id_carrito = dc.id_carrito
    JOIN articulos a ON dc.id_articulo = a.id_articulo
    WHERE c.id_cliente = ? AND a.id_articulo IN ($placeholders)
";

$stmtResumen = $conn->prepare($queryResumen);
$params = array_merge([$id_cliente], $articulosSeleccionados);
$stmtResumen->bind_param('i' . $tipos, ...$params);
$stmtResumen->execute();
$resumen = $stmtResumen->get_result();

$itemsResumen = [];
$total_precios = 0;
while ($row = $resumen->fetch_assoc()) {
    $id_articulo = $row['id_articulo'];
    $cantidad = intval($cantidadesSeleccionadas[$id_articulo] ?? 1);
    $importe = $cantidad * $row['precio'];

    $itemsResumen[] = [
        'id_articulo' => $id_articulo,
        'descripcion' => $row['descripcion'],
        'cantidad' => $cantidad,
        'precio' => $row['precio'],
        'importe' => $importe
    ];
    $total_precios += $importe;
}

// Opciones de envío
$queryEnvios = "SELECT id_envio, tipo_envio, costo FROM envio";
$envios = $conn->query($queryEnvios);

$opciones_envio = [];
while ($row = $envios->fetch_assoc()) {
    $opciones_envio[$row['tipo_envio']] = [
        'id_envio' => $row['id_envio'],
        'costo' => $row['costo']
    ];
}

$costo_envio_domicilio = $opciones_envio['Domicilio']['costo'] ?? 0.00;
$costo_retiro = $opciones_envio['Punto de Entrega']['costo'] ?? 0.00;

// Paqueterías
$queryPaqueterias = "SELECT id_paqueteria, nombre_paqueteria FROM paqueteria";
$paqueterias = $conn->query($queryPaqueterias);

$lista_paqueterias = [];
while ($row = $paqueterias->fetch_assoc()) {
    $lista_paqueterias[] = $row;
}

// Calcular IVA 16%
$iva = $total_precios * 0.16;
$total_general_default = $total_precios + $iva + $costo_envio_domicilio;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dirección de Envío</title>
    <link rel="stylesheet" href="direccion.css">
    <style>
        .container {
            display: flex;
            gap: 40px;
            margin: 20px;
        }
        .left-section {
            flex: 2;
        }
        .right-section {
            flex: 1;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .item-resumen {
            margin-bottom: 15px;
        }
        .item-resumen strong {
            display: block;
        }
        .resumen-total {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 20px;
        }
        .direccion {
            margin-bottom: 10px;
        }
        .boton {
            display: inline-block;
            background: #007BFF;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .boton:hover {
            background: #0056b3;
        }
        label {
            display: block;
            margin-bottom: 8px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h1>Elige la forma de entrega</h1>

<div class="container">

    <div class="left-section">

        <form action="../Carrito/confirmar_pedido.php" method="POST">

            <!-- Artículos y cantidades como inputs ocultos -->
            <?php foreach ($itemsResumen as $item): ?>
                <input type="hidden" name="articulos[]" value="<?= htmlspecialchars($item['id_articulo']) ?>">
                <input type="hidden" name="cantidades[<?= htmlspecialchars($item['id_articulo']) ?>]" 
                value="<?= htmlspecialchars($item['cantidad']) ?>">
            <?php endforeach; ?>

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
                            <a href="editar_domicilio.php?id=<?= $dir['id_direccion'] ?>" class="boton btn-editar">Editar</a>
                            <label>
                                <input type="radio" name="domicilio_seleccionado" value="<?= $dir['id_direccion'] ?>" required>
                                Elegir esta dirección
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No tienes direcciones registradas. <a href="nuevo_domicilio.php" class="boton">Agregar nuevo</a></p>
                <?php endif; ?>
            </div>

            <label style="margin-top:20px;">
                <input type="radio" name="forma_entrega" value="Punto de Entrega">
                Retiro en punto de entrega (costo $<?= number_format($costo_retiro, 2) ?>)
            </label>

            <div id="paqueteria-container" style="display:none; margin-left:20px; margin-top:10px;">
                <h4>Opciones de paquetería</h4>
                <?php foreach ($lista_paqueterias as $paq): ?>
                    <label>
                        <input type="radio" name="paqueteria" value="<?= $paq['id_paqueteria'] ?>" required>
                        <?= htmlspecialchars($paq['nombre_paqueteria']) ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div style="margin-top:20px;">
                  <button type="submit" id="confirmarPedido">Confirmar pedido</button>

                <a href="../Carrito/carrito.php" class="boton" style="background:#6c757d;">Regresar</a>
            </div>

        </form>

    </div>

    <div class="right-section">
        <h3>Resumen de la compra</h3>
        <?php foreach ($itemsResumen as $item): ?>
            <div class="item-resumen">
                <strong><?= htmlspecialchars($item['descripcion']) ?></strong>
                Cantidad: <?= htmlspecialchars($item['cantidad']) ?><br>
                Precio unitario: $<?= number_format($item['precio'], 2) ?><br>
                Importe: $<?= number_format($item['importe'], 2) ?>
            </div>
        <?php endforeach; ?>

        <div class="resumen-total">
            Subtotal: $<span id="subtotal"><?= number_format($total_precios, 2) ?></span><br>
            IVA (16%): $<span id="iva"><?= number_format($iva, 2) ?></span><br>
            Costo envío: $<span id="costo-envio"><?= number_format($costo_envio_domicilio, 2) ?></span><br>
            <hr>
            Total: $<span id="total"><?= number_format($total_general_default, 2) ?></span>
        </div>
    </div>

</div>
<script>
    const radioFormas = document.querySelectorAll('input[name="forma_entrega"]');
    const containerDirecciones = document.getElementById('direcciones-container');
    const containerPaqueterias = document.getElementById('paqueteria-container');

    const costoEnvioDomicilio = <?= $costo_envio_domicilio ?>;
    const costoRetiro = <?= $costo_retiro ?>;
    const subtotal = <?= $total_precios ?>;
    const iva = <?= $iva ?>;

    const spanCostoEnvio = document.getElementById('costo-envio');
    const spanTotal = document.getElementById('total');

    function actualizarResumen(costoEnvio) {
        spanCostoEnvio.textContent = costoEnvio.toFixed(2);
        const total = subtotal + iva + costoEnvio;
        spanTotal.textContent = total.toFixed(2);
    }

    radioFormas.forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.value === 'Domicilio') {
                containerDirecciones.style.display = 'block';
                containerPaqueterias.style.display = 'none';
                actualizarResumen(costoEnvioDomicilio);
            } else {
                containerDirecciones.style.display = 'none';
                containerPaqueterias.style.display = 'block';
                actualizarResumen(costoRetiro);
            }
        });
    });

    // Inicializa vista
    window.addEventListener('DOMContentLoaded', () => {
        actualizarResumen(costoEnvioDomicilio);
    });
</script>

<script>
    const btnConfirmar = document.getElementById('confirmarPedido');

    btnConfirmar.addEventListener('click', function () {
        const formaSeleccionada = document.querySelector('input[name="forma_entrega"]:checked');
        
        if (!formaSeleccionada) {
            alert("Por favor selecciona una forma de entrega.");
            return;
        }

        const formaEntrega = formaSeleccionada.value;
        let idSeleccionado = null;

        if (formaEntrega === 'Domicilio') {
            const direccionSeleccionada = document.querySelector('input[name="domicilio_seleccionado"]:checked');
            if (!direccionSeleccionada) {
                alert("Por favor selecciona una dirección de entrega.");
                return;
            }
            idSeleccionado = direccionSeleccionada.value;
        } else if (formaEntrega === 'Punto de Entrega') {
            const puntoSeleccionado = document.querySelector('input[name="paqueteria"]:checked');
            if (!puntoSeleccionado) {
                alert("Por favor selecciona un punto de entrega.");
                return;
            }
            idSeleccionado = puntoSeleccionado.value;
        }

        // Aquí puedes hacer un submit de formulario o redirigir
        // Por ejemplo, podrías usar fetch para enviar a un PHP
        // O redirigir con location.href si ya tienes una ruta definida

        // Ejemplo de redirección:
        const url = ../Carrito/confirmar_pedido.php?forma=${formaEntrega}&id=${idSeleccionado};
        window.location.href = url;
    });
</script>
<script>
    const formaEntregaRadios = document.querySelectorAll('input[name="forma_entrega"]');
    const direccionesContainer = document.getElementById('direcciones-container');
    const paqueteriaContainer = document.getElementById('paqueteria-container');
    const confirmarPedido = document.getElementById('confirmarPedido');
    const form = document.querySelector('form');

    // Mostrar u ocultar según opción seleccionada
    formaEntregaRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'Domicilio') {
                direccionesContainer.style.display = 'block';
                paqueteriaContainer.style.display = 'none';
            } else {
                direccionesContainer.style.display = 'none';
                paqueteriaContainer.style.display = 'block';
            }
        });
    });

    // Validación al enviar el formulario
    form.addEventListener('submit', function (e) {
        const formaEntrega = document.querySelector('input[name="forma_entrega"]:checked').value;

        if (formaEntrega === 'Domicilio') {
            const domicilioSeleccionado = document.querySelector('input[name="domicilio_seleccionado"]:checked');
            if (!domicilioSeleccionado) {
                alert("Por favor selecciona una dirección para envío a domicilio.");
                e.preventDefault();
                return;
            }
        } else if (formaEntrega === 'Punto de Entrega') {
            const paqueteriaSeleccionada = document.querySelector('input[name="paqueteria"]:checked');
            if (!paqueteriaSeleccionada) {
                alert("Por favor selecciona una paquetería para retiro en punto de entrega.");
                e.preventDefault();
                return;
            }
        }
    });
</script>
</body> 
</html>