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

// Obtener paqueterías disponibles
$queryPaqueteria = "SELECT id_paqueteria, nombre_paqueteria, descripcion, fecha FROM paqueteria";
$resultPaqueteria = $conn->query($queryPaqueteria);

$paqueteriasArray = [];
while ($row = $resultPaqueteria->fetch_assoc()) {
    $paqueteriasArray[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Confirmar Pedido</title>
</head>
<body>
    <h1>Confirmar Pedido</h1>

    <div id="forma-entrega"></div>
    <div id="articulos-lista"></div>
    <div id="resumen-costos"></div>

    <script>
        const direccionesCliente = <?php echo json_encode($direccionesArray); ?>;
        const paqueterias = <?php echo json_encode($paqueteriasArray); ?>;
    </script>

    <script>
        const pedidoJSON = localStorage.getItem("pedidoActual");
        const carritoJSON = localStorage.getItem("carrito_completo");
        const carrito = carritoJSON ? JSON.parse(carritoJSON) : [];

        if (!pedidoJSON) {
            document.body.innerHTML += "<p>No hay datos de pedido guardados.</p>";
        } else {
            const pedido = JSON.parse(pedidoJSON);
            let htmlFormaEntrega = `<h2>ID del Pedido: ${pedido.idPedido || "No disponible"}</h2>`;
            htmlFormaEntrega += `<h3>Forma de entrega: ${pedido.formaEntrega || "No especificada"}</h3>`;

            if (pedido.formaEntrega === "Domicilio") {
                const idSeleccionado = pedido.domicilioSeleccionado;
                const direccion = direccionesCliente.find(d => d.id_direccion == idSeleccionado);

                if (direccion) {
                    htmlFormaEntrega += `
                        <p><strong>Domicilio:</strong></p>
                        <ul>
                            <li><strong>Calle:</strong> ${direccion.calle}</li>
                            <li><strong>Número:</strong> ${direccion.num_ext}</li>
                            <li><strong>Colonia:</strong> ${direccion.colonia}</li>
                            <li><strong>Ciudad:</strong> ${direccion.ciudad}</li>
                            <li><strong>Estado:</strong> ${direccion.estado}</li>
                            <li><strong>Código Postal:</strong> ${direccion.codigo_postal}</li>
                        </ul>
                    `;
                } else {
                    htmlFormaEntrega += "<p><strong>No se encontró el domicilio seleccionado.</strong></p>";
                }

            } else if (pedido.formaEntrega === "Punto de Entrega") {
                const idPaqueteria = pedido.paqueteriaSeleccionada;
                const p = paqueterias.find(pq => pq.id_paqueteria == idPaqueteria) || {};

                htmlFormaEntrega += `
                    <p><strong>Paquetería seleccionada:</strong></p>
                    <ul>
                        <li><strong>Nombre:</strong> ${p.nombre_paqueteria || "No disponible"}</li>
                        <li><strong>Descripción:</strong> ${p.descripcion || "No disponible"}</li>
                        <li><strong>Fecha estimada:</strong> ${p.fecha || "No disponible"}</li>
                    </ul>
                `;
            }

            document.getElementById("forma-entrega").innerHTML = htmlFormaEntrega;

            // Mostrar artículos
            let htmlArticulos = "<h3>Artículos:</h3><ul>";
            carrito.forEach(item => {
                const imagen = (item.atributos || []).find(a => a.nombre === "Imagen");
                htmlArticulos += `
                    <li>
                        <p><strong>${item.descripcion}</strong></p>
                        ${imagen ? `<img src="${imagen.valor}" alt="Imagen del producto" width="100" />` : ""}
                        <p>Cantidad: ${item.cantidad}</p>
                        <p>Precio unitario: $${item.precio.toFixed(2)}</p>
                        <p><strong>Importe: $${item.importe.toFixed(2)}</strong></p>
                    </li>
                    <hr/>
                `;
            });
            htmlArticulos += "</ul>";
            document.getElementById("articulos-lista").innerHTML = htmlArticulos;

            // Resumen de costos
            if (pedido.resumen) {
                const r = pedido.resumen;
                const htmlResumen = `
                    <h3>Resumen de costos</h3>
                    <p>Subtotal: $${r.subtotal.toFixed(2)}</p>
                    <p>IVA: $${r.iva.toFixed(2)}</p>
                    <p>Costo de envío: $${r.costoEnvio.toFixed(2)}</p>
                    <p><strong>Total: $${r.total.toFixed(2)}</strong></p>
                `;
                document.getElementById("resumen-costos").innerHTML = htmlResumen;
            }
        }
    </script>
    
</body>
<?php include('../Nav/footer.php'); ?>
</html>
