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

// Direcciones
$queryDireccion = "SELECT id_direccion, calle, num_ext, colonia, ciudad, estado, codigo_postal FROM direccion WHERE id_cliente = ?";
$stmtDireccion = $conn->prepare($queryDireccion);
$stmtDireccion->bind_param('i', $id_cliente);
$stmtDireccion->execute();
$direcciones = $stmtDireccion->get_result();

// Resumen de carrito
$queryResumen = "SELECT a.descripcion, dc.cantidad, dc.precio, (dc.cantidad * dc.precio) AS importe FROM carrito c JOIN detalle_carrito dc ON c.id_carrito = dc.id_carrito JOIN articulos a ON dc.id_articulo = a.id_articulo WHERE c.id_cliente = ?";
$stmtResumen = $conn->prepare($queryResumen);
$stmtResumen->bind_param('i', $id_cliente);
$stmtResumen->execute();
$resumen = $stmtResumen->get_result();

$total = 0;
while ($row = $resumen->fetch_assoc()) {
    $total += $row['importe'];
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
$queryPaqueterias = "SELECT id_paqueteria, nombre_paqueteria, costo FROM paqueteria";
$paqueterias = $conn->query($queryPaqueterias);

$lista_paqueterias = [];
while ($row = $paqueterias->fetch_assoc()) {
    $lista_paqueterias[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dirección de Envío</title>
    <link rel="stylesheet" href="direccion.css">
</head>
<body>

<h1>Elige la forma de entrega</h1>

<div class="container">
    <div class="left-section">
        <h3>Formas de entrega</h3>

        <label>
            <input type="radio" name="forma_entrega" value="Domicilio" checked>
            Envío a domicilio (costo $<?= number_format($costo_envio_domicilio, 2) ?>)
        </label>

        <div id="direcciones-container" style="margin-left:20px; margin-top:10px; min-height: 200px;">
            <h4>Direcciones registradas</h4>
            <?php if ($direcciones->num_rows > 0): ?>
                <?php while($row = $direcciones->fetch_assoc()): ?>
                    <div class="direccion">
                        <?= htmlspecialchars($row['calle']) ?> #<?= htmlspecialchars($row['num_ext']) ?>, 
                        <?= htmlspecialchars($row['colonia']) ?>, <?= htmlspecialchars($row['ciudad']) ?>, 
                        <?= htmlspecialchars($row['estado']) ?>, CP <?= htmlspecialchars($row['codigo_postal']) ?>
                        <br>
                        <a href="editar_domicilio.php?id=<?= $row['id_direccion'] ?>" class="boton btn-editar">Editar</a>
                        <input type="radio" name="domicilio_seleccionado" value="<?= $row['id_direccion'] ?>"> Elegir
                    </div>
                <?php endwhile; ?>
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
            <?php foreach($lista_paqueterias as $paq): ?>
                <label>
                    <input type="radio" name="paqueteria" value="<?= $paq['id_paqueteria'] ?>" data-costo="<?= $paq['costo'] ?>">
                    <?= htmlspecialchars($paq['nombre_paqueteria']) ?> (costo $<?= number_format($paq['costo'], 2) ?>)
                </label><br>
            <?php endforeach; ?>
        </div>

        <div class="botones" style="margin-top:20px;">
            <a href="../Carrito/carrito.php" class="boton">⬅ Volver al carrito</a>
            <button class="boton" id="btnConfirmar" type="button">Confirmar pedido</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const domicilioContainer = document.getElementById('direcciones-container');
    const paqueteriaContainer = document.getElementById('paqueteria-container');
    const btnConfirmar = document.getElementById('btnConfirmar');

    const costosEnvio = {
        'Domicilio': <?= json_encode($costo_envio_domicilio) ?>,
        'Punto de Entrega': <?= json_encode($costo_retiro) ?>
    };

    const paqueterias = <?= json_encode($lista_paqueterias) ?>;

    let totalCarrito = <?= json_encode($total) ?>;

    function actualizarCosto() {
        let formaEntrega = document.querySelector('input[name="forma_entrega"]:checked').value;
        let costoEnvio = 0;

        if (formaEntrega === 'Domicilio') {
            domicilioContainer.style.display = 'block';
            paqueteriaContainer.style.display = 'none';

            let domicilioSeleccionado = document.querySelector('input[name="domicilio_seleccionado"]:checked');
            btnConfirmar.disabled = !domicilioSeleccionado;

            costoEnvio = costosEnvio['Domicilio'];
        } else {
            domicilioContainer.style.display = 'none';
            paqueteriaContainer.style.display = 'block';

            let paqueteriaSeleccionada = document.querySelector('input[name="paqueteria"]:checked');
            if (paqueteriaSeleccionada) {
                costoEnvio = parseFloat(paqueteriaSeleccionada.dataset.costo);
                btnConfirmar.disabled = false;
            } else {
                costoEnvio = costosEnvio['Punto de Entrega'];
                btnConfirmar.disabled = true;
            }
        }

        console.log('Costo Envío:', costoEnvio, 'Total:', totalCarrito + costoEnvio);
    }

    document.querySelectorAll('input[name="forma_entrega"]').forEach(el => {
        el.addEventListener('change', actualizarCosto);
    });

    document.addEventListener('change', (e) => {
        if (['domicilio_seleccionado', 'paqueteria'].includes(e.target.name)) {
            actualizarCosto();
        }
    });

    // Guardar selección en localStorage y redirigir
    btnConfirmar.addEventListener('click', (e) => {
        e.preventDefault();

        const formaEntrega = document.querySelector('input[name="forma_entrega"]:checked').value;
        let datosGuardados = { formaEntrega };

        if (formaEntrega === 'Domicilio') {
            const domicilioSeleccionado = document.querySelector('input[name="domicilio_seleccionado"]:checked');
            if (!domicilioSeleccionado) {
                alert('Por favor selecciona una dirección.');
                return;
            }
            datosGuardados.domicilioSeleccionado = domicilioSeleccionado.value;
        } else {
            const paqueteriaSeleccionada = document.querySelector('input[name="paqueteria"]:checked');
            if (!paqueteriaSeleccionada) {
                alert('Por favor selecciona una paquetería.');
                return;
            }
            datosGuardados.paqueteriaSeleccionada = paqueteriaSeleccionada.value;
        }

        localStorage.setItem('datosEntrega', JSON.stringify(datosGuardados));

        // Redirigir a confirmar pedido
        window.location.href = '../Carrito/confirmar_pedido.php';
    });

    actualizarCosto(); // inicial
});
</script>

</body>
</html>

<?php
include('../Nav/footer.php');
$conn->close();
?>
