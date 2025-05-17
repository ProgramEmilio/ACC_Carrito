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

    <form method="POST" action="../Direccion/direccion.php">

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
            <button type="submit" id="continuarCompra">Continuar con la compra</button>
        </div>
    </form>

    <script>
        const STORAGE_KEY = "articulos_seleccionados";
        const STORAGE_CANTIDADES_KEY = "cantidades_articulos";

        function actualizarTotal() {
            let total = 0;
            let cantidadesGuardadas = JSON.parse(localStorage.getItem(STORAGE_CANTIDADES_KEY) || "{}");

            document.querySelectorAll('tbody tr').forEach(fila => {
                const checkbox = fila.querySelector('.select-articulo');
                const cantidadInput = fila.querySelector('.cantidad-input');
                const precio = parseFloat(fila.getAttribute('data-precio')) || 0;

                let cantidad = parseInt(cantidadInput.value) || 1;
                cantidad = Math.max(1, Math.floor(cantidad)); // Bloquear decimales y negativos
                cantidadInput.value = cantidad;

                cantidadesGuardadas[cantidadInput.name] = cantidad;

                const nuevoImporte = cantidad * precio;
                fila.querySelector('.importe').textContent = `$${nuevoImporte.toFixed(2)}`;

                if (checkbox.checked) {
                    total += nuevoImporte;
                }
                  guardarCarritoEnStorage();
            });

            document.getElementById('total').textContent = total.toFixed(2);
            localStorage.setItem(STORAGE_CANTIDADES_KEY, JSON.stringify(cantidadesGuardadas));
            localStorage.setItem("total_carrito", total.toFixed(2));
        }

        function guardarCarritoEnStorage() {
    const carrito = [];

    document.querySelectorAll('tbody tr').forEach(fila => {
        const checkbox = fila.querySelector('.select-articulo');
        if (!checkbox.checked) return; // solo seleccionados

        const idArticulo = checkbox.value;
        const descripcion = fila.querySelector('td:nth-child(3)').textContent.trim();
        const personalizacion = fila.querySelector('td:nth-child(4)').textContent.trim();
        const cantidad = parseInt(fila.querySelector('.cantidad-input').value) || 1;
        const precio = parseFloat(fila.getAttribute('data-precio')) || 0;
        const importe = cantidad * precio;
        const imagenSrc = fila.querySelector('td:nth-child(2) img')?.getAttribute('src') || null;

        carrito.push({
            idArticulo,
            descripcion,
            personalizacion,
            cantidad,
            precio,
            importe,
            imagenSrc
        });
    });

    localStorage.setItem('carrito_completo', JSON.stringify(carrito));

    // También guardar total (ya lo tienes)
    let total = carrito.reduce((acc, item) => acc + item.importe, 0);
    localStorage.setItem('total_carrito', total.toFixed(2));
}

        function restaurarCantidadesDesdeLocalStorage() {
            const cantidadesGuardadas = JSON.parse(localStorage.getItem(STORAGE_CANTIDADES_KEY) || "{}");
            document.querySelectorAll(".cantidad-input").forEach(input => {
                if (cantidadesGuardadas.hasOwnProperty(input.name)) {
                    input.value = cantidadesGuardadas[input.name];
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
            restaurarCantidadesDesdeLocalStorage();
            actualizarTotal();

            document.querySelectorAll(".select-articulo").forEach(cb => {
                cb.addEventListener("change", () => {
                    actualizarTotal();
                    guardarSeleccionLocalStorage();
                });
            });

            document.querySelectorAll(".cantidad-input").forEach(input => {
                input.addEventListener("change", actualizarTotal);
                input.addEventListener("input", (e) => {
                    e.target.value = e.target.value.replace(/[^\d]/g, '');
                });
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
