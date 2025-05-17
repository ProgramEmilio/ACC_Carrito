<?php
include('../Nav/header.php');
include('../BD/ConexionBD.php');

// Conexión a la base de datos
$_servername = 'localhost:3306';
$database = 'CARRITO_ACC';
$username = 'root';
$password = '';
$conn = mysqli_connect($_servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="Home.css" />
  <title>Inicio</title>
</head>
<body>

<div class="container">
  <?php
  $sql = "SELECT a.id_articulo, a.nombre_articulo, a.descripcion, a.imagen, d.precio
          FROM articulos a
          INNER JOIN detalle_articulos d ON a.id_detalle_articulo = d.id_detalle_articulo
          WHERE d.estatus = 'Disponible'";
  $resultado = $conn->query($sql);

  if ($resultado->num_rows > 0) {
      while ($row = $resultado->fetch_assoc()) {
          echo '<div class="product">';
          echo '<a href="detalle_producto.php?id=' . $row["id_articulo"] . '">';
          echo '<img src="../Imagenes/' . $row["imagen"] . '" alt="' . htmlspecialchars($row["nombre_articulo"]) . '">';
          echo '<h3>' . htmlspecialchars($row["nombre_articulo"]) . '</h3>';
          echo '</a>';
          echo '<p>' . htmlspecialchars($row["descripcion"]) . '</p>';
          echo '<p class="price">$' . number_format($row["precio"], 2) . ' MXN</p>';
          echo '<button class="btn" onclick="agregarAlCarrito(\'' . htmlspecialchars($row["nombre_articulo"]) . '\', \'$' . number_format($row["precio"], 2) . ' MXN\')">Agregar al carrito</button>';
          echo '</div>';
      }
  } else {
      echo "<p>No hay productos disponibles.</p>";
  }

  $conn->close();
  ?>
</div>

<script>
  let contador = 0;
  let carrito = [];

  window.onload = () => {
    const datosGuardados = localStorage.getItem('carrito');
    if (datosGuardados) {
      carrito = JSON.parse(datosGuardados);
      contador = carrito.length;
      const contadorCarrito = document.getElementById('contador-carrito');
      if (contadorCarrito) contadorCarrito.textContent = contador;
    }
  };

  function agregarAlCarrito(nombre, precio) {
    carrito.push({ nombre, precio });
    contador++;
    const contadorCarrito = document.getElementById('contador-carrito');
    if (contadorCarrito) contadorCarrito.textContent = contador;
    localStorage.setItem('carrito', JSON.stringify(carrito));
  }

  function irAlCarrito() {
    window.location.href = 'carrito.php';
  }
</script>

</body>
<?php
include ('../Nav/footer.php');
?>
</html>
