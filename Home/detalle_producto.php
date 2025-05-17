<?php
include('../Nav/header.php');
include('../BD/ConexionBD.php');

if (!isset($_GET['id'])) {
    echo "Producto no especificado.";
    exit;
}

$id = $_GET['id'];  // sin convertir a entero


$sql = "SELECT a.nombre_articulo, a.descripcion, a.imagen, d.precio
        FROM articulos a
        INNER JOIN detalle_articulos d ON a.id_detalle_articulo = d.id_detalle_articulo
        WHERE a.id_articulo = '$id'";


$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {
    $producto = $resultado->fetch_assoc();
} else {
    echo "Producto no encontrado.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del producto</title>
</head>
<body>
<div class="detalle-producto">
    <img src="../Imagenes/<?php echo $producto['imagen']; ?>" alt="<?php echo htmlspecialchars($producto['nombre_articulo']); ?>">
    <div class="detalle-detalles">
        <h1><?php echo htmlspecialchars($producto['nombre_articulo']); ?></h1>
        <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
        <p><strong>Precio:</strong> $<?php echo number_format($producto['precio'], 2); ?> MXN</p>
        <button onclick="agregarAlCarrito('<?php echo htmlspecialchars($producto['nombre_articulo']); ?>', '<?php echo number_format($producto['precio'], 2); ?> MXN')">Agregar al carrito</button>
        <br><br>
        <a href="home.php">← Volver al inicio</a>
    </div>
</div>


    <script>
        function agregarAlCarrito(nombre, precio) {
            let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
            carrito.push({ nombre, precio });
            localStorage.setItem('carrito', JSON.stringify(carrito));
            
        }
    </script>
</body>




<style>
    
    .detalle-producto {
  max-width: 900px;
  margin: 40px auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-family: Arial, sans-serif;
  background-color: #fafafa;
  display: flex;
  gap: 30px;
  align-items: flex-start;
}


.detalle-producto img {
  width: 45%;
  height: auto;
  border-radius: 8px;
  object-fit: contain;
}


.detalle-detalles {
  width: 55%;
  display: flex;
  flex-direction: column;
}


.detalle-detalles h1 {
  font-size: 2rem;
  margin-bottom: 15px;
  color: #333;
}


.detalle-detalles p {
  font-size: 1rem;
  color: #555;
  margin-bottom: 15px;
  line-height: 1.4;
}


.detalle-detalles p strong {
  font-weight: 700;
  color: #222;
  font-size: 1.2rem;
}


.detalle-detalles button {
  background-color: #28a745;
  color: white;
  font-size: 1rem;
  padding: 12px 25px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  margin-top: 10px;
  align-self: flex-start;
}

.detalle-detalles button:hover {
  background-color: #218838;
}


.detalle-detalles a {
  display: inline-block;
  margin-top: 25px;
  color: #007bff;
  text-decoration: none;
  font-size: 0.9rem;
}

.detalle-detalles a:hover {
  text-decoration: underline;
}


    </style>

<?php
include('../Nav/footer.php');
?>
</html>
