<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');

$id_carrito = 1;

// Obtener datos del carrito
$queryCarrito = "
    SELECT c.id_carrito, c.fecha, c.total, cli.nom_persona
    FROM carrito c
    JOIN cliente cli ON c.id_cliente = cli.id_cliente
    WHERE c.id_carrito = $id_carrito
";
$resultCarrito = $conn->query($queryCarrito);
$carrito = $resultCarrito->fetch_assoc();

// Obtener detalles
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
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1 { color: #444; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { padding: 10px; border: 1px solid #ddd; text-align: left; vertical-align: middle; }
    th { background-color: #f0f0f0; }
    .total { text-align: right; font-weight: bold; margin-top: 15px; }
    .btn { padding: 10px 20px; background: #4CAF50; color: white; border: none; cursor: pointer; }
    .btn:disabled { background: #ccc; cursor: not-allowed; }
    img { width: 80px; height: auto; }

    .continuar-compra {
  margin-top: 20px;
  text-align: right;
}

.continuar-compra button {
  background-color: #4CAF50;
  color: white;
  padding: 12px 24px;
  font-size: 16px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.continuar-compra button:hover {
  background-color: #45a049;
}
  </style>
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
      <input type="checkbox" class="select-articulo" name="articulos[]" value="<?= $row['id_articulo'] ?>" onchange="actualizarTotal(this)">
    </td>
    <td>
      <?php if ($row['imagen']): ?>
        <img src="../imagenes/<?= htmlspecialchars($row['imagen']) ?>" alt="Imagen">
      <?php else: ?>
        Sin imagen
      <?php endif; ?>
    </td>
    <td><?= htmlspecialchars($row['descripcion']) ?></td>
    <td><?= htmlspecialchars($row['personalizacion']) ?></td>
    <td>
      <input type="number" name="cantidades[<?= $row['id_articulo'] ?>]" value="<?= $row['cantidad'] ?>" min="1" class="cantidad-input" onchange="actualizarTotal(this)">
    </td>
    <td>$<?= number_format($row['precio'], 2) ?></td>
    <td class="importe">$<?= number_format($row['importe'], 2) ?></td>
    <td>
      <button type="button" onclick="confirmarEliminacion('<?= $row['id_articulo'] ?>')" style="font-size: 20px; padding: 5px 10px;">
  🗑
</button>
    </td>
  </tr>
<?php endwhile; ?>
        </tbody>
      </table>

      <div class="total">
        Total del Carrito: $<span id="total">0.00</span>
      </div>

      <div class="continuar-compra">
  <button type="button" onclick="continuarCompra()">Continuar con la compra</button>
</div>
    </form>

    <script>
function actualizarTotal(elemento) {
  const filas = document.querySelectorAll('tbody tr');
  let total = 0;
  let algunoSeleccionado = false;

  filas.forEach(fila => {
    const checkbox = fila.querySelector('.select-articulo');
    const cantidadInput = fila.querySelector('.cantidad-input');
    const importeCell = fila.querySelector('.importe');
    const precio = parseFloat(fila.getAttribute('data-precio')) || 0;
    const cantidad = parseInt(cantidadInput.value) || 1;

    const nuevoImporte = cantidad * precio;
    importeCell.textContent = `$${nuevoImporte.toFixed(2)}`;

    if (checkbox.checked) {
      total += nuevoImporte;
      algunoSeleccionado = true;
    }
  });

  document.getElementById('total').textContent = total.toFixed(2);
  document.getElementById('continuarBtn').disabled = !algunoSeleccionado;
}

function confirmarEliminacion(idArticulo) {
  if (confirm("¿Estás seguro de que deseas eliminar este artículo del carrito?")) {
    window.location.href = "eliminar_articulo.php?id_articulo=" + idArticulo + "&id_carrito=<?= $id_carrito ?>";
  }
}

document.addEventListener('DOMContentLoaded', () => {
  actualizarTotal();
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
