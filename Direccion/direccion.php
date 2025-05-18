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

// Direcciones del cliente
$queryDireccion = "SELECT id_direccion, calle, num_ext, colonia, ciudad, estado, codigo_postal FROM direccion WHERE id_cliente = ?";
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

// Lista de paqueterías
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
    <style>
        .direccion { margin-bottom: 15px; }
        .boton { padding: 6px 10px; background: #007bff; color: white; text-decoration: none; border-radius: 3px; }
        .btn-editar { background: #28a745; margin-left: 10px; }
        .container { display: flex; gap: 20px; }
        .left-section { flex: 1; }
        .right-section { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    </style>
</head>
<body>

<h1>Elige la forma de entrega</h1>

<div class="container">
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
                            <a href="editar_domicilio.php?id=<?= $dir['id_direccion'] ?>" class="boton btn-editar">Editar</a>
                            <label>
                                <input type="radio" name="domicilio_seleccionado" value="<?= $dir['id_direccion'] ?>">
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

            <div id="paqueteria-container" style="margin-left:20px; display:none;">
                <h4>Selecciona la paquetería:</h4>
                <?php foreach ($lista_paqueterias as $paq): ?>
                    <label>
                        <input type="radio" name="paqueteria" value="<?= htmlspecialchars($paq['id_paqueteria']) ?>">
                        <?= htmlspecialchars($paq['nombre_paqueteria']) ?>
                    </label><br>
                <?php endforeach; ?>
            </div>

            <div style="margin-top:20px;">
                <button type="submit">Confirmar pedido</button>
                <a href="../Carrito/carrito.php" class="boton" style="background:#6c757d;">Regresar</a>
            </div>

            <div id="inputsOcultos"></div>
        </form>
    </div>

    <div class="right-section">
        <h2>Resumen de compra</h2>
        <table id="resumenCompra">
            <thead>
                <tr>
                    <th>Artículo</th>
                    <th>Personalización</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Importe</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr><td colspan="4" style="text-align:right;">Subtotal</td><td id="subtotalCompra">$0.00</td></tr>
                <tr><td colspan="4" style="text-align:right;">IVA (16%)</td><td id="ivaCompra">$0.00</td></tr>
                <tr><td colspan="4" style="text-align:right;">Costo de envío</td><td id="costoEnvioCompra">$0.00</td></tr>
                <tr><td colspan="4" style="text-align:right;"><strong>Total</strong></td><td id="totalCompra"><strong>$0.00</strong></td></tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Obtener carrito del localStorage (debe estar previamente guardado)
    const carrito = JSON.parse(localStorage.getItem('carrito_completo')) || [];
    const tbody = document.querySelector('#resumenCompra tbody');
    const subtotalElem = document.getElementById('subtotalCompra');
    const ivaElem = document.getElementById('ivaCompra');
    const costoEnvioElem = document.getElementById('costoEnvioCompra');
    const totalElem = document.getElementById('totalCompra');
    const inputsOcultos = document.getElementById('inputsOcultos');

    const IVA_RATE = 0.16;
    const costoEnvioDomicilio = <?= json_encode($costo_envio_domicilio) ?>;
    const costoRetiro = <?= json_encode($costo_retiro) ?>;

    let subtotal = 0;
    tbody.innerHTML = '';  // Limpiar tabla

    carrito.forEach(item => {
        const importe = item.cantidad * item.precio;
        subtotal += importe;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.descripcion}</td>
            <td>${item.personalizacion || ''}</td>
            <td>${item.cantidad}</td>
            <td>$${item.precio.toFixed(2)}</td>
            <td>$${importe.toFixed(2)}</td>
        `;
        tbody.appendChild(tr);

        // Inputs ocultos para el formulario
        inputsOcultos.innerHTML += `
            <input type="hidden" name="articulos[]" value="${item.idArticulo}">
            <input type="hidden" name="cantidades[${item.idArticulo}]" value="${item.cantidad}">
        `;
    });

    function actualizarResumen() {
        const formaEntrega = document.querySelector('input[name="forma_entrega"]:checked')?.value;
        const paqSeleccionada = document.querySelector('input[name="paqueteria"]:checked');
        const domicilioSeleccionado =document.querySelector('input[name="domicilio_seleccionado"]:checked');
            let costoEnvio = 0;

    if (formaEntrega === 'Domicilio') {
        costoEnvio = costoEnvioDomicilio;
    } else if (formaEntrega === 'Punto de Entrega') {
        costoEnvio = costoRetiro;
    }

    costoEnvioElem.textContent = `$${costoEnvio.toFixed(2)}`;

    const iva = subtotal * IVA_RATE;
    ivaElem.textContent = `$${iva.toFixed(2)}`;
    subtotalElem.textContent = `$${subtotal.toFixed(2)}`;

    const total = subtotal + iva + costoEnvio;
    totalElem.textContent = `$${total.toFixed(2)}`;
}

// Mostrar u ocultar secciones según forma de entrega
function toggleFormasEntrega() {
    const formaEntrega = document.querySelector('input[name="forma_entrega"]:checked')?.value;
    const direccionesDiv = document.getElementById('direcciones-container');
    const paqueteriaDiv = document.getElementById('paqueteria-container');

    if (formaEntrega === 'Domicilio') {
        direccionesDiv.style.display = 'block';
        paqueteriaDiv.style.display = 'none';
    } else {
        direccionesDiv.style.display = 'none';
        paqueteriaDiv.style.display = 'block';
    }

    actualizarResumen();
}

// Validación al enviar el formulario
document.getElementById('formEntrega').addEventListener('submit', function(e) {
    const formaEntrega = document.querySelector('input[name="forma_entrega"]:checked')?.value;
    const domicilio = document.querySelector('input[name="domicilio_seleccionado"]:checked');
    const paqueteria = document.querySelector('input[name="paqueteria"]:checked');

       if (formaEntrega === 'Domicilio') {
        costoEnvio = costoEnvioDomicilio;

        if (!domicilioSeleccionado) {
            Swal.fire({
                icon: 'warning',
                title: 'Selecciona una dirección',
                text: 'Debes elegir una dirección registrada para el envío a domicilio.',
            });
            return false;
        }
    } else if (formaEntrega === 'Punto de Entrega') {
        costoEnvio = costoRetiro;

        if (!paqSeleccionada) {
            Swal.fire({
                icon: 'warning',
                title: 'Selecciona una paquetería',
                text: 'Debes elegir una paquetería para el retiro.',
            });
            return false;
        }
    }

    const iva = subtotal * IVA_RATE;
    const total = subtotal + iva + costoEnvio;

    subtotalElem.textContent = `$${subtotal.toFixed(2)}`;
    ivaElem.textContent = `$${iva.toFixed(2)}`;
    costoEnvioElem.textContent = `$${costoEnvio.toFixed(2)}`;
    totalElem.innerHTML = `<strong>$${total.toFixed(2)}</strong>`;

    return true;
}

// Evento para actualizar resumen al cambiar forma de entrega o seleccionar dirección/paquetería
document.querySelectorAll('input[name="forma_entrega"]').forEach(radio => {
    radio.addEventListener('change', () => {
        const forma = radio.value;
        document.getElementById('paqueteria-container').style.display = forma === 'Punto de Entrega' ? 'block' : 'none';
        actualizarResumen();
    });
});

document.querySelectorAll('input[name="domicilio_seleccionado"], input[name="paqueteria"]').forEach(input => {
    input.addEventListener('change', actualizarResumen);
});

// Validación final al enviar formulario
document.getElementById('formEntrega').addEventListener('submit', function (e) {
    if (!actualizarResumen()) {
        e.preventDefault(); // Evitar envío si falta algo
    }
});

// Cálculo inicial
actualizarResumen();
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  // Leer carrito desde localStorage (ajusta la clave según guardes)
  const carrito = JSON.parse(localStorage.getItem("carrito_completo")) || [];

  // Actualizar tabla resumen
  const tbody = document.querySelector('#resumenCompra tbody');
  const subtotalElem = document.getElementById('subtotalCompra');
  const ivaElem = document.getElementById('ivaCompra');
  const costoEnvioElem = document.getElementById('costoEnvioCompra');
  const totalElem = document.getElementById('totalCompra');
  const inputsOcultos = document.getElementById('inputsOcultos');

  const IVA_RATE = 0.16;
  const costoEnvioDomicilio = <?= json_encode($costo_envio_domicilio) ?>;
  const costoRetiro = <?= json_encode($costo_retiro) ?>;

  let subtotal = 0;
  tbody.innerHTML = '';
  inputsOcultos.innerHTML = '';

  carrito.forEach(item => {
    const importe = item.cantidad * item.precio;
    subtotal += importe;

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${item.descripcion}</td>
      <td>${item.personalizacion || ''}</td>
      <td>${item.cantidad}</td>
      <td>$${item.precio.toFixed(2)}</td>
      <td>$${importe.toFixed(2)}</td>
    `;
    tbody.appendChild(tr);

    // Inputs ocultos para enviar por POST
    inputsOcultos.innerHTML += `
      <input type="hidden" name="articulos[]" value="${item.idArticulo}">
      <input type="hidden" name="cantidades[${item.idArticulo}]" value="${item.cantidad}">
    `;
  });

  // Función para actualizar totales y mostrar en la tabla
  function actualizarTotales() {
    const formaEntrega = document.querySelector('input[name="forma_entrega"]:checked').value;
    let costoEnvio = 0;
    if (formaEntrega.toLowerCase() === 'domicilio') {
      costoEnvio = costoEnvioDomicilio;
    } else if (formaEntrega.toLowerCase() === 'punto de entrega') {
      costoEnvio = costoRetiro;
    }

    const iva = subtotal * IVA_RATE;
    const total = subtotal + iva + costoEnvio;

    subtotalElem.textContent = `$${subtotal.toFixed(2)}`;
    ivaElem.textContent = `$${iva.toFixed(2)}`;
    costoEnvioElem.textContent = `$${costoEnvio.toFixed(2)}`;
    totalElem.innerHTML = `<strong>$${total.toFixed(2)}</strong>`;
  }

  actualizarTotales();
  

  // Mostrar/ocultar contenedores según forma de entrega
  function toggleEntregaOptions() {
    const formaEntrega = document.querySelector('input[name="forma_entrega"]:checked').value.toLowerCase();
    document.getElementById('direcciones-container').style.display = formaEntrega === 'domicilio' ? 'block' : 'none';
    document.getElementById('paqueteria-container').style.display = formaEntrega === 'punto de entrega' ? 'block' : 'none';
  }
  toggleEntregaOptions();

  // Escuchar cambios en forma de entrega
  document.querySelectorAll('input[name="forma_entrega"]').forEach(radio => {
    radio.addEventListener('change', () => {
      toggleEntregaOptions();
      actualizarTotales();
    });
  });

  // Cargar selección previa desde localStorage si quieres
  const formaEntregaGuardada = localStorage.getItem('forma_entrega');
  if (formaEntregaGuardada) {
    const radio = document.querySelector(`input[name="forma_entrega"][value="${formaEntregaGuardada}"]`);
    if (radio) {
      radio.checked = true;
      toggleEntregaOptions();
      actualizarTotales();
    }
  }

  const domicilioGuardado = localStorage.getItem('domicilio_seleccionado');
  if (domicilioGuardado) {
    const radioDomicilio = document.querySelector(`input[name="domicilio_seleccionado"][value="${domicilioGuardado}"]`);
    if (radioDomicilio) radioDomicilio.checked = true;
  }

  const paqueteriaGuardada = localStorage.getItem('paqueteria_seleccionada');
  if (paqueteriaGuardada) {
    const radioPaq = document.querySelector(`input[name="paqueteria"][value="${paqueteriaGuardada}"]`);
    if (radioPaq) radioPaq.checked = true;
  }

  // Guardar selecciones en localStorage para persistencia
  document.querySelectorAll('input[name="forma_entrega"]').forEach(radio => {
    radio.addEventListener('change', () => {
      localStorage.setItem('forma_entrega', radio.value);
    });
  });
  document.querySelectorAll('input[name="domicilio_seleccionado"]').forEach(radio => {
    radio.addEventListener('change', () => {
      localStorage.setItem('domicilio_seleccionado', radio.value);
    });
  });
  document.querySelectorAll('input[name="paqueteria"]').forEach(radio => {
    radio.addEventListener('change', () => {
      localStorage.setItem('paqueteria_seleccionada', radio.value);
    });
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    const productosContainer = document.getElementById('productos-carrito');
    const resumenContainer = document.getElementById('resumen-carrito');
    const formulario = document.getElementById('formulario-compra');
    let subtotal = 0;

    // Mostrar productos del carrito
    carrito.forEach(producto => {
        const precioTotal = producto.precio * producto.cantidad;
        subtotal += precioTotal;

        const item = document.createElement('div');
        item.innerHTML = `
            <p><strong>${producto.nombre}</strong></p>
            <p>Cantidad: ${producto.cantidad}</p>
            <p>Precio: $${producto.precio.toFixed(2)}</p>
            <hr>
        `;
        productosContainer.appendChild(item);

        // Crear inputs ocultos para enviar en formulario
        const inputArticulo = document.createElement('input');
        inputArticulo.type = 'hidden';
        inputArticulo.name = 'articulos[]';
        inputArticulo.value = producto.id;
        formulario.appendChild(inputArticulo);

        const inputCantidad = document.createElement('input');
        inputCantidad.type = 'hidden';
        inputCantidad.name = 'cantidades[]';
        inputCantidad.value = producto.cantidad;
        formulario.appendChild(inputCantidad);
    });

    const iva = subtotal * 0.16;
    const envio = subtotal > 0 ? 120 : 0;
    const total = subtotal + iva + envio;

    resumenContainer.innerHTML = `
        <p>Subtotal: $${subtotal.toFixed(2)}</p>
        <p>IVA (16%): $${iva.toFixed(2)}</p>
        <p>Envío: $${envio.toFixed(2)}</p>
        <h4>Total: $${total.toFixed(2)}</h4>
    `;

    // Guardar resumen en localStorage
    const resumenCompra = {
        subtotal: subtotal.toFixed(2),
        iva: iva.toFixed(2),
        envio: envio.toFixed(2),
        total: total.toFixed(2)
    };
    localStorage.setItem('resumenCompra', JSON.stringify(resumenCompra));
});

function mostrarDireccionSeleccionada() {
    const formaEntrega = document.querySelector('input[name="forma_entrega"]:checked')?.value;
    const direccionDiv = document.getElementById('direccionSeleccionada');

    if (formaEntrega === 'Domicilio') {
        const domicilioSeleccionado = document.querySelector('input[name="domicilio_seleccionado"]:checked');
        if (domicilioSeleccionado) {
            const label = document.querySelector(`label[for="${domicilioSeleccionado.id}"]`);
            if (label) {
                direccionDiv.innerHTML = `Dirección seleccionada: ${label.textContent.trim()}`;
            }
        } else {
            direccionDiv.innerHTML = 'No se ha seleccionado ninguna dirección.';
        }
    } else {
        direccionDiv.innerHTML = '';
    }
}

// Llamar al cargar y cuando cambie la selección
mostrarDireccionSeleccionada();

document.querySelectorAll('input[name="domicilio_seleccionado"]').forEach(radio => {
    radio.addEventListener('change', mostrarDireccionSeleccionada);
});

document.querySelectorAll('input[name="forma_entrega"]').forEach(radio => {
    radio.addEventListener('change', mostrarDireccionSeleccionada);
});
</script>

</body> </html>
