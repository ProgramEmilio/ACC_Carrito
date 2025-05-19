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
    SELECT a.id_articulo, a.descripcion, dc.cantidad, dc.precio, dc.importe, dc.personalizacion,
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
</head>
<body>

<h1>Carrito de Compras</h1>

<?php if ($carrito): ?>
    <p><strong>Cliente:</strong> <?= htmlspecialchars($carrito['nom_persona']) ?></p>
    <p><strong>Fecha:</strong> <?= htmlspecialchars($carrito['fecha']) ?></p>

    <form method="POST" action="procesar_compra.php">

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
                <tr data-precio="<?= $row['precio'] ?>">
                    <td>
                        <input type="checkbox" class="select-articulo" name="articulos[]" value="<?= $row['id_articulo'] ?>">
                    </td>
                    <td>
                        <?php if ($row['imagen']): ?>
                            <a href="detalle_articulo.php?id=<?= urlencode($row['id_articulo']) ?>">
                                <img src="../imagenes/<?= htmlspecialchars($row['imagen']) ?>" alt="Imagen del artículo">
                            </a>
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
                        <button type="button" class="eliminar-btn" onclick="confirmarEliminacion('<?= $row['id_articulo'] ?>')">🗑</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <div class="total">
            Total del Carrito: $<span id="total">0.00</span>
        </div>

        <div class="continuar-compra">
            <button type="button" id="continuarCompra" onclick="validarContinuar()">Continuar con la compra</button>
        </div>
    </form>

    <script>
        const STORAGE_KEY = "articulos_seleccionados";

        function actualizarTotal() {
            let total = 0;
            document.querySelectorAll('tbody tr').forEach(fila => {
                const checkbox = fila.querySelector('.select-articulo');
                const cantidadInput = fila.querySelector('.cantidad-input');
                const precio = parseFloat(fila.getAttribute('data-precio')) || 0;
                const cantidad = parseInt(cantidadInput.value) || 1;
                const nuevoImporte = cantidad * precio;

                fila.querySelector('.importe').textContent = `$${nuevoImporte.toFixed(2)}`;

                if (checkbox.checked) {
                    total += nuevoImporte;
                }
            });

            document.getElementById('total').textContent = total.toFixed(2);
            localStorage.setItem("total_carrito", total.toFixed(2));
        }

        function validarContinuar() {
            const checkboxes = document.querySelectorAll('.select-articulo:checked');
            const productosSeleccionados = [];

            if (checkboxes.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: '¡Ups!',
                    text: 'Debes seleccionar al menos un producto para continuar.',
                    confirmButtonColor: '#4CAF50'
                });
            } else {
                checkboxes.forEach(cb => {
                    const fila = cb.closest('tr');
                    productosSeleccionados.push({
                        id_articulo: cb.value,
                        descripcion: fila.querySelector('td:nth-child(3)').innerText,
                        personalizacion: fila.querySelector('td:nth-child(4)').innerText,
                        cantidad: fila.querySelector('.cantidad-input').value,
                        precio: parseFloat(fila.getAttribute('data-precio')) || 0,
                        importe: (parseFloat(fila.querySelector('.cantidad-input').value) * (parseFloat(fila.getAttribute('data-precio')) || 0)).toFixed(2),
                        imagen: fila.querySelector('img')?.getAttribute('src') || ''
                    });
                });

                localStorage.setItem("productos_seleccionados", JSON.stringify(productosSeleccionados));
                window.location.href = "../Direccion/direccion.php";
            }
        }

        function confirmarEliminacion(idArticulo) {
            Swal.fire({
                title: '¿Eliminar artículo?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `eliminar_articulo.php?id_articulo=${idArticulo}&id_carrito=<?= $id_carrito ?>`;
                }
            });
        }

        function guardarSeleccionLocalStorage() {
            const seleccionados = Array.from(document.querySelectorAll(".select-articulo:checked")).map(cb => cb.value);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(seleccionados));
        }

        function restaurarSeleccionDesdeLocalStorage() {
            const seleccionados = JSON.parse(localStorage.getItem(STORAGE_KEY) || "[]");
            document.querySelectorAll(".select-articulo").forEach(cb => {
                cb.checked = seleccionados.includes(cb.value);
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            restaurarSeleccionDesdeLocalStorage();
            actualizarTotal();

            document.querySelectorAll(".select-articulo").forEach(cb => {
                cb.addEventListener("change", () => {
                    actualizarTotal();
                    guardarSeleccionLocalStorage();
                });
            });

            document.querySelectorAll(".cantidad-input").forEach(input => {
                input.addEventListener("change", actualizarTotal);
            });
        });
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
