<?php
include('../Nav/header.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="Home.css" />
  <title>Inicio</title>
  <style>
    .product-images {
      position: relative;
      width: 100%;
      height: 200px;
      overflow: hidden;
    }
    
    .product-images img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      position: absolute;
      top: 0;
      left: 0;
      opacity: 0;
      transition: opacity 0.5s ease-in-out;
    }
    
    .product-images img.active {
      opacity: 1;
    }
    
    .product-images img:first-child {
      opacity: 1;
    }
    
    .image-indicators {
      position: absolute;
      bottom: 10px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 5px;
    }
    
    .indicator {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background-color: rgba(255, 255, 255, 0.5);
      cursor: pointer;
      transition: background-color 0.3s;
    }
    
    .indicator.active {
      background-color: white;
    }
  </style>
</head>
<body>

<div class="container">
  <?php
  // Consulta SQL corregida para tu estructura de base de datos
  $sql = "SELECT DISTINCT
            a.id_articulo, 
            a.descripcion, 
            d.precio,
            d.existencia,
            d.estatus
          FROM articulos a
          INNER JOIN detalle_articulos d ON a.id_detalle_articulo = d.id_detalle_articulo
          WHERE d.estatus = 'Disponible'";
  
  $resultado = $conn->query($sql);

  if ($resultado->num_rows > 0) {
      while ($row = $resultado->fetch_assoc()) {
          // Obtener todas las imágenes para este artículo
          $sql_imagenes = "SELECT ac.valor 
                          FROM articulo_completo ac
                          INNER JOIN atributos at ON ac.id_atributo = at.id_atributo
                          WHERE ac.id_articulo = ? AND at.nombre = 'Imagen'";
          
          $stmt = $conn->prepare($sql_imagenes);
          $stmt->bind_param("s", $row["id_articulo"]);
          $stmt->execute();
          $resultado_imagenes = $stmt->get_result();
          
          $imagenes = [];
          while ($img = $resultado_imagenes->fetch_assoc()) {
              $imagenes[] = $img['valor'];
          }
          
          // Obtener otros atributos (color, tamaño, etc.)
          $sql_atributos = "SELECT at.nombre, ac.valor 
                           FROM articulo_completo ac
                           INNER JOIN atributos at ON ac.id_atributo = at.id_atributo
                           WHERE ac.id_articulo = ? AND at.nombre != 'Imagen'";
          
          $stmt_attr = $conn->prepare($sql_atributos);
          $stmt_attr->bind_param("s", $row["id_articulo"]);
          $stmt_attr->execute();
          $resultado_atributos = $stmt_attr->get_result();
          
          $atributos = [];
          while ($attr = $resultado_atributos->fetch_assoc()) {
              $atributos[$attr['nombre']] = $attr['valor'];
          }
          
          echo '<div class="product" data-product-id="' . $row["id_articulo"] . '">';
          echo '<a href="detalle_producto.php?id=' . $row["id_articulo"] . '">';
          
          // Mostrar imágenes
          if (!empty($imagenes)) {
              echo '<div class="product-images">';
              foreach ($imagenes as $index => $imagen) {
                  echo '<img src="../Imagenes/' . htmlspecialchars($imagen) . '" alt="' . htmlspecialchars($row["descripcion"]) . '">';
              }
              if (count($imagenes) > 1) {
                  echo '<div class="image-indicators">';
                  for ($i = 0; $i < count($imagenes); $i++) {
                      echo '<span class="indicator' . ($i === 0 ? ' active' : '') . '" data-index="' . $i . '"></span>';
                  }
                  echo '</div>';
              }
              echo '</div>';
          } else {
              echo '<div class="product-images">';
              echo '<img src="../Imagenes/no-image.png" alt="Sin imagen">';
              echo '</div>';
          }
          
          echo '<h3>Artículo: ' . htmlspecialchars($row["id_articulo"]) . '</h3>';
          echo '</a>';
          
          echo '<p>' . htmlspecialchars($row["descripcion"]) . '</p>';
          
          // Mostrar atributos adicionales
          if (!empty($atributos)) {
              echo '<div class="product-attributes">';
              foreach ($atributos as $nombre => $valor) {
                  echo '<span class="attribute"><strong>' . htmlspecialchars($nombre) . ':</strong> ' . htmlspecialchars($valor) . '</span> ';
              }
              echo '</div>';
          }
          
          echo '<p class="price">$' . number_format($row["precio"], 2) . ' MXN</p>';
          echo '<p class="stock">Existencia: ' . $row["existencia"] . '</p>';
          echo '<button class="btn" onclick="agregarAlCarrito(\'' . htmlspecialchars($row["id_articulo"]) . '\', \'$' . number_format($row["precio"], 2) . ' MXN\')">Agregar al carrito</button>';
          echo '</div>';
          
          $stmt->close();
          $stmt_attr->close();
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
    
    // Inicializar carrusel de imágenes
    initImageCarousel();
  };

  function agregarAlCarrito(id_articulo, precio) {
    carrito.push({ id_articulo, precio });
    contador++;
    const contadorCarrito = document.getElementById('contador-carrito');
    if (contadorCarrito) contadorCarrito.textContent = contador;
    localStorage.setItem('carrito', JSON.stringify(carrito));
  }

  function irAlCarrito() {
    window.location.href = 'carrito.php';
  }

  function initImageCarousel() {
    const products = document.querySelectorAll('.product');
    
    products.forEach(product => {
      const images = product.querySelectorAll('.product-images img');
      const indicators = product.querySelectorAll('.indicator');
      
      if (images.length > 1) {
        let currentIndex = 0;
        let interval;
        
        // Función para cambiar imagen
        function showImage(index) {
          images.forEach((img, i) => {
            img.classList.toggle('active', i === index);
          });
          indicators.forEach((indicator, i) => {
            indicator.classList.toggle('active', i === index);
          });
          currentIndex = index;
        }
        
        // Auto-play de imágenes
        function startAutoPlay() {
          interval = setInterval(() => {
            currentIndex = (currentIndex + 1) % images.length;
            showImage(currentIndex);
          }, 3000); // Cambiar cada 3 segundos
        }
        
        function stopAutoPlay() {
          clearInterval(interval);
        }
        
        // Event listeners para indicadores
        indicators.forEach((indicator, index) => {
          indicator.addEventListener('click', () => {
            stopAutoPlay();
            showImage(index);
            setTimeout(startAutoPlay, 5000); // Reanudar después de 5 segundos
          });
        });
        
        // Pausar auto-play al hover
        product.addEventListener('mouseenter', stopAutoPlay);
        product.addEventListener('mouseleave', startAutoPlay);
        
        // Iniciar auto-play
        startAutoPlay();
      }
    });
  }
</script>

</body>
<?php
include ('../Nav/footer.php');
?>
</html>