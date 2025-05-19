<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');

$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    echo "Usuario no autenticado.";
    exit;
}

// Obtener el último carrito activo del usuario
$queryCarrito = "
    SELECT c.id_carrito, c.fecha, c.total, cli.nom_persona, cli.id_cliente
    FROM carrito c
    JOIN cliente cli ON c.id_cliente = cli.id_cliente
    JOIN usuario u ON cli.id_usuario = u.id_usuario
    WHERE u.id_usuario = $id_usuario
    ORDER BY c.fecha DESC
    LIMIT 1
";
$resultCarrito = $conn->query($queryCarrito);
$carrito = $resultCarrito->fetch_assoc();

if (!$carrito) {
    echo "No hay carrito activo.";
    exit;
}

$id_carrito = $carrito['id_carrito'];

// Opcional: obtener info de envío asociado al carrito, si ya fue seleccionado
// Para este ejemplo supondremos que la info de envío viene del localStorage, la tomaremos en JS

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Confirmar Pedido - Resumen</title>
    <style>
        table {
            border-collapse: collapse; width: 100%; margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd; padding: 8px; text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn {
            padding: 10px 20px; margin: 5px; cursor: pointer;
            border: none; border-radius: 5px;
        }
        .btn-back {
            background-color: #ccc;
        }
        .btn-continue {
            background-color: #28a745; color: white;
        }
    </style>
</head>
<body>

<h1>Confirmar Pedido</h1>
<p><strong>Cliente:</strong> <?= htmlspecialchars($carrito['nom_persona']) ?></p>
<p><strong>Fecha del carrito:</strong> <?= htmlspecialchars($carrito['fecha']) ?></p>

<h2>Resumen de Compra</h2>
<table id="resumenTabla">
    <thead>
        <tr>
            <th>Artículo</th>
            <th>Cantidad</th>
            <th>Personalización</th>
            <th>Precio Unitario</th>
            <th>Importe</th>
        </tr>
    </thead>
    <tbody>
        <!-- Aquí se llenará con JS desde localStorage -->
    </tbody>
</table>

<h2>Información de Envío</h2>
<p>Tipo de envío: <span id="tipoEnvio"></span></p>
<p>Fecha estimada de llegada: <span id="fechaEntrega"></span></p>

<div>
    <button class="btn btn-back" onclick="window.history.back()">Retroceder</button>
    <button class="btn btn-continue" id="btnContinuar">Continuar</button>
</div>

<script>
// Ejemplo de cómo puede venir almacenada la info en localStorage:
// "carrito_items": JSON.stringify([{id_articulo:"P001", descripcion:"Playera", cantidad:2, personalizacion:"Texto", precio:250.00, importe:500.00}, ...])
// "envio_seleccionado": JSON.stringify({tipo_envio:"Domicilio", fecha_estimada:"2025-05-19T00:00:00"})

function formatearFecha(fechaStr) {
    const fecha = new Date(fechaStr);
    return fecha.toLocaleDateString('es-MX', {year: 'numeric', month: 'long', day: 'numeric'});
}

function cargarResumen() {
    const resumenBody = document.querySelector("#resumenTabla tbody");
    resumenBody.innerHTML = '';

    const carritoItemsStr = localStorage.getItem("carrito_items");
    if (!carritoItemsStr) {
        resumenBody.innerHTML = '<tr><td colspan="5">No hay artículos en el carrito.</td></tr>';
        return;
    }
    
    const carritoItems = JSON.parse(carritoItemsStr);
    let total = 0;

    carritoItems.forEach(item => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${item.descripcion}</td>
            <td>${item.cantidad}</td>
            <td>${item.personalizacion}</td>
            <td>$${item.precio.toFixed(2)}</td>
            <td>$${item.importe.toFixed(2)}</td>
        `;
        resumenBody.appendChild(tr);
        total += item.importe;
    });

    // Mostrar total al final de la tabla
    const trTotal = document.createElement("tr");
    trTotal.innerHTML = `
        <td colspan="4" style="text-align:right;"><strong>Total:</strong></td>
        <td><strong>$${total.toFixed(2)}</strong></td>
    `;
    resumenBody.appendChild(trTotal);
}

function cargarEnvio() {
    const envioStr = localStorage.getItem("envio_seleccionado");
    if (!envioStr) {
        document.getElementById("tipoEnvio").textContent = "No seleccionado";
        document.getElementById("fechaEntrega").textContent = "-";
        return;
    }

    const envio = JSON.parse(envioStr);
    document.getElementById("tipoEnvio").textContent = envio.tipo_envio || "-";
    document.getElementById("fechaEntrega").textContent = envio.fecha_estimada ? formatearFecha(envio.fecha_estimada) : "-";
}

document.getElementById("btnContinuar").addEventListener("click", () => {
    // Aquí podrías hacer validaciones o enviar la orden para procesar en el backend
    // Por ahora, redirigiremos a la página de procesamiento
    window.location.href = "procesar_pedido.php";
});

cargarResumen();
cargarEnvio();
</script>

</body>
</html>
