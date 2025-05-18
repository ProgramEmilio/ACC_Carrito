<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');

$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    echo "Usuario no autenticado.";
    exit;
}

$queryCarrito = "
    SELECT c.id_carrito, c.fecha, c.total, cli.nom_persona
    FROM carrito c
    JOIN cliente cli ON c.id_cliente = cli.id_cliente
    JOIN usuario u ON cli.id_usuario = u.id_usuario
    WHERE u.id_usuario = $id_usuario
    ORDER BY c.fecha DESC
    LIMIT 1
";

$resultCarrito = $conn->query($queryCarrito);
$carrito = $resultCarrito->fetch_assoc();
$id_carrito = $carrito['id_carrito'] ?? null;

if (!$id_carrito) {
    echo "No hay carrito activo.";
    exit;
}

$queryDetalles = "
    SELECT dc.id_detalle_carrito, a.id_articulo, a.descripcion, dc.cantidad, dc.precio, 
    dc.importe, dc.personalizacion,
        (SELECT valor FROM articulo_completo ac 
         WHERE ac.id_articulo = a.id_articulo AND ac.id_atributo = 3 LIMIT 1) AS imagen
    FROM detalle_carrito dc
    JOIN articulos a ON dc.id_articulo = a.id_articulo
    WHERE dc.id_carrito = $id_carrito
";
$resultDetalles = $conn->query($queryDetalles);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        img { width: 80px; height: auto; }
        table, th, td { border: 1px solid #ccc; border-collapse: collapse; padding: 8px; }
    </style>
</head>
<body>

<h1>Carrito de Compras</h1>

<?php if ($carrito): ?>
    <p><strong>Cliente:</strong> <?= htmlspecialchars($carrito['nom_persona']) ?></p>
    <p><strong>Fecha:</strong> <?= htmlspecialchars($carrito['fecha']) ?></p>

    <form method="POST" action="../Direccion/direccion.php">
        <input type="hidden" class="id-carrito" value="<?= $id_carrito ?>">
        <table>
            <thead>
                <tr>
                    <th>Seleccionar</th>
                    <th>Imagen</th>
                    <th>Artículo</th>
                    <th>Personalización</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Importe</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $resultDetalles->fetch_assoc()): ?>
                <tr id="fila-<?= $row['id_detalle_carrito'] ?>" data-precio="<?= $row['precio'] ?>">
                    <td><input type="checkbox" class="select-articulo" name="articulos[]" value="<?= $row['id_articulo'] ?>"></td>
                    <td>
                        <?php if ($row['imagen']): ?>
                            <img src="../imagenes/<?= htmlspecialchars($row['imagen']) ?>" alt="Imagen del artículo">
                        <?php else: ?>
                            Sin imagen
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['descripcion']) ?></td>
                    <td><?= htmlspecialchars($row['personalizacion']) ?></td>
                    <td>
                        <input type="number" name="cantidades[<?= $row['id_articulo'] ?>]" value="<?= $row['cantidad'] ?>" min="1" class="cantidad-input">
                    </td>
                    <td>$<?= number_format($row['precio'], 2) ?></td>
                    <td class="importe">$<?= number_format($row['importe'], 2) ?></td>
                    <td>
                        <button type="button" class="eliminar-btn" onclick="confirmarEliminacion(<?= $row['id_detalle_carrito'] ?>)">🗑</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <div class="total">
            Total del Carrito: $<span id="total">0.00</span>
        </div>

        <div class="continuar-compra">
            <button type="submit" id="continuarCompra">Continuar con la compra</button>
        </div>
    </form>

<script>
function actualizarTotal() {
    let total = 0;

    document.querySelectorAll('tbody tr').forEach(fila => {
        const checkbox = fila.querySelector('.select-articulo');
        const cantidadInput = fila.querySelector('.cantidad-input');
        const precio = parseFloat(fila.getAttribute('data-precio')) || 0;

        let cantidad = parseInt(cantidadInput.value) || 1;
        cantidad = Math.max(1, cantidad);
        cantidadInput.value = cantidad;

        const nuevoImporte = cantidad * precio;
        fila.querySelector('.importe').textContent = `$${nuevoImporte.toFixed(2)}`;

        if (checkbox.checked) {
            total += nuevoImporte;
        }
    });

    document.getElementById('total').textContent = total.toFixed(2);
}

document.addEventListener('DOMContentLoaded', () => {
    actualizarTotal();

    document.querySelectorAll(".select-articulo").forEach(cb => {
        cb.addEventListener("change", actualizarTotal);
    });

    document.querySelectorAll(".cantidad-input").forEach(input => {
        input.addEventListener("change", actualizarTotal);
        input.addEventListener("input", (e) => {
            e.target.value = e.target.value.replace(/[^\d]/g, '');
        });
    });
});

function confirmarEliminacion(idDetalleCarrito) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción eliminará el artículo del carrito.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('eliminar_detalle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_detalle_carrito=' + encodeURIComponent(idDetalleCarrito)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const fila = document.getElementById('fila-' + idDetalleCarrito);
                    if (fila) fila.remove();
                    actualizarTotal();
                    Swal.fire('Eliminado', 'El artículo fue eliminado.', 'success');
                } else {
                    Swal.fire('Error', data.error || 'No se pudo eliminar.', 'error');
                }
            });
        }
    });
}
</script>

<?php else: ?>
    <p>Carrito no encontrado.</p>
<?php endif; ?>

</body>
</html>

<?php
include('../Nav/footer.php');
$conn->close();
?>
