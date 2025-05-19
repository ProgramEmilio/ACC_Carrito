<?php
include('../Nav/header.php');
include('../BD/ConexionBD.php');

if (!isset($_GET['id'])) {
    echo "Producto no especificado.";
    exit;
}

$id = $_GET['id'];

$sql = "SELECT a.nombre_articulo, a.descripcion, d.precio
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

$sql_imagenes = "SELECT nombre_imagen FROM imagenes_articulo WHERE id_articulo = '$id'";
$res_imagenes = $conn->query($sql_imagenes);

$imagenes = [];
if ($res_imagenes->num_rows > 0) {
    while ($img = $res_imagenes->fetch_assoc()) {
        $imagenes[] = $img['nombre_imagen'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del producto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    
</head>
<body>

<div class="detalle-producto">

    <!-- Carrusel con Swiper -->
    <div class="swiper-container galeria-imagenes">
        <div class="swiper-wrapper">
            <?php foreach ($imagenes as $img): ?>
                <div class="swiper-slide">
                    <img src="../Imagenes/<?php echo $img; ?>" alt="Imagen del producto">
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Controles -->
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-pagination"></div>
    </div>

    <div class="detalle-detalles">
        <h1><?php echo htmlspecialchars($producto['nombre_articulo']); ?></h1>
        <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
        <p><strong>Precio:</strong> $<?php echo number_format($producto['precio'], 2); ?> MXN</p>
        <button onclick="agregarAlCarrito('<?php echo htmlspecialchars($producto['nombre_articulo']); ?>', '<?php echo number_format($producto['precio'], 2); ?> MXN')">Agregar al carrito</button>
        <br><br>
        <a href="home.php">← Volver al inicio</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
    var swiper = new Swiper('.swiper-container', {
        loop: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev'
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true
        }
    });

    function agregarAlCarrito(nombre, precio) {
        let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
        carrito.push({ nombre, precio });
        localStorage.setItem('carrito', JSON.stringify(carrito));
    }
</script>

<?php include('../Nav/footer.php'); ?>
</body>


<style>
 
.detalle-producto {
  max-width: 1000px;
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


.galeria-imagenes {
  width: 45%;
  position: relative;
}

.swiper-container {
  width: 100%;
  height: 400px;
  border-radius: 8px;
  overflow: hidden;
  position: relative;
}

.swiper-slide img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 8px;
}


.swiper-button-prev,
.swiper-button-next {
  color: #333;
  width: 30px;
  height: 30px;
  background-color: rgba(255, 255, 255, 0.8);
  border-radius: 50%;
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.15);
  top: 50%;
  transform: translateY(-50%);
}

.swiper-button-prev {
  left: 10px;
}

.swiper-button-next {
  right: 10px;
}

/* Paginación activa del carrusel */
.swiper-pagination-bullet-active {
  background: #007bff;
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
</html>
