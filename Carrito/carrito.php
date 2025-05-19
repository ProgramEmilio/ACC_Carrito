<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');

$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    echo "Usuario no autenticado.";
    exit;
}

$queryCarrito = "
    SELECT c.id_carrito, c.fecha, c.total, cli.nom_persona,cli.apellido_materno,cli.apellido_paterno
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
    <title class="titulo">Carrito de Compras</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<h1 class = "titulo">Carrito de Compras</h1>

<?php if ($carrito): ?>
<p><strong class="nom">Nombre:</strong> <?= htmlspecialchars($carrito['nom_persona'] . ' ' . $carrito['apellido_paterno'] . ' ' . $carrito['apellido_materno']) ?></p>
   <!--  <p><strong>Fecha:</strong> <?= htmlspecialchars($carrito['fecha']) ?></p> -->

    <form id="formCarrito" method="POST" action="../Direccion/direccion.php">
        <input type="hidden" name="id_carrito" value="<?= $id_carrito ?>">
        <input type="hidden" name="total_carrito" id="inputTotalCarrito" value="0.00">
        <table class="carrito_table">
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
                            <img class="img_art" src="../imagenes/<?= htmlspecialchars($row['imagen']) ?>" alt="Imagen del artículo">
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

        <div class="total_carr">
            Total del Carrito: $<span id="total">0.00</span>
        </div>

        <div class="continuar-compra">
            <button type="submit" id="continuarCompra">Continuar con la compra</button>
        </div>
    </form>

<script>
function actualizarTotal() {
    let total = 0;
    const seleccionados = {};
    const cantidades = {};

    document.querySelectorAll('tbody tr').forEach(fila => {
        const id = fila.id.split('-')[1]; // id_detalle_carrito
        const checkbox = fila.querySelector('.select-articulo');
        const cantidadInput = fila.querySelector('.cantidad-input');
        const precio = parseFloat(fila.getAttribute('data-precio')) || 0;

        let cantidad = parseInt(cantidadInput.value) || 1;
        cantidad = Math.max(1, cantidad);
        cantidadInput.value = cantidad;

        const nuevoImporte = cantidad * precio;
        fila.querySelector('.importe').textContent = `$${nuevoImporte.toFixed(2)}`;

        // Guardar estado en objetos
        cantidades[id] = cantidad;
        seleccionados[id] = checkbox.checked;

        if (checkbox.checked) {
            total += nuevoImporte;
        }
    });

    document.getElementById('total').textContent = total.toFixed(2);
    document.getElementById('inputTotalCarrito').value = total.toFixed(2);

    // Guardar en localStorage
    localStorage.setItem('cantidades', JSON.stringify(cantidades));
    localStorage.setItem('seleccionados', JSON.stringify(seleccionados));
    localStorage.setItem('total_carrito', total.toFixed(2));
}

function restaurarDesdeLocalStorage() {
    const cantidades = JSON.parse(localStorage.getItem('cantidades') || '{}');
    const seleccionados = JSON.parse(localStorage.getItem('seleccionados') || '{}');

    document.querySelectorAll('tbody tr').forEach(fila => {
        const id = fila.id.split('-')[1];
        const cantidadInput = fila.querySelector('.cantidad-input');
        const checkbox = fila.querySelector('.select-articulo');

        if (cantidades[id]) {
            cantidadInput.value = cantidades[id];
        }

        checkbox.checked = seleccionados[id] || false;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    restaurarDesdeLocalStorage();
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

    document.getElementById('formCarrito').addEventListener('submit', function(e) {
        const checkboxes = document.querySelectorAll('.select-articulo:checked');
        if (checkboxes.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debes seleccionar al menos un artículo para continuar con la compra.',
                confirmButtonText: 'Entendido'
            });
        }
    });
});
</script>

<?php else: ?>
    <p>Carrito no encontrado.</p>
<?php endif; ?>
<script>
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

    document.getElementById('formCarrito').addEventListener('submit', function(e) {
        const checkboxes = document.querySelectorAll('.select-articulo:checked');
        if (checkboxes.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debes seleccionar al menos un artículo para continuar con la compra.',
                confirmButtonText: 'Entendido'
            });
        }
    });
});
</script>
<script>
function confirmarEliminacion(idDetalle) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡Esta acción eliminará el artículo del carrito!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('eliminar_detalle.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id_detalle_carrito=' + encodeURIComponent(idDetalle)
            })
            .then(response => response.json())
.then(data => {
    if (data.success) {
        const fila = document.getElementById('fila-' + idDetalle);
        fila.remove();
        actualizarTotal();
    } else {
        Swal.fire('Error', data.error || 'No se pudo eliminar el artículo.', 'error');
    }
})
        }
    });
}
</script>

</body>
</html>

<?php
include('../Nav/footer.php');
$conn->close();
?>
