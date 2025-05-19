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
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }
    
    .product {
      border: 1px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      background: white;
    }
    
    .product:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    
    .product-images {
      position: relative;
      width: 100%;
      height: 250px;
      overflow: hidden;
      background-color: #f8f9fa;
    }
    
    .product-images img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      position: absolute;
      top: 0;
      left: 0;
      opacity: 0;
      transition: opacity 0.8s ease-in-out;
      background-color: white;
    }
    
    .product-images img.active {
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

    .product-content {
      padding: 15px;
    }

    .product-content h3 {
      margin: 0 0 10px 0;
      font-size: 1.1em;
      color: #333;
    }

    .product-content p {
      margin: 5px 0;
      color: #666;
      font-size: 0.9em;
    }

    .product-attributes {
      margin: 10px 0;
    }

    .attribute {
      display: inline-block;
      background: #f0f0f0;
      padding: 3px 8px;
      margin: 2px;
      border-radius: 15px;
      font-size: 0.8em;
    }

    .price {
      font-size: 1.3em !important;
      font-weight: bold !important;
      color: #e74c3c !important;
      margin: 10px 0 !important;
    }

    .stock {
      color: #27ae60 !important;
      font-weight: bold !important;
    }

    .btn-ver-detalle {
      width: 100%;
      background-color: #3498db;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      text-align: center;
      transition: background-color 0.3s;
      font-weight: bold;
    }

    .btn-ver-detalle:hover {
      background-color: #2980b9;
      text-decoration: none;
      color: white;
    }

    .no-products {
      text-align: center;
      padding: 50px;
      color: #666;
      font-size: 1.2em;
    }

    .page-title {
      text-align: center;
      color: #333;
      margin-bottom: 30px;
    }
  </style>
</head>
<body>
<h1 class="titulo">Nuestros Productos</h1>
<div class="container">
  
  
  <?php
 /* // Mostrar mensajes de éxito o error
  if (isset($_SESSION['mensaje_exito'])) {
      echo '<div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 5px;">';
      echo $_SESSION['mensaje_exito'];
      echo '</div>';
      unset($_SESSION['mensaje_exito']);
  }*/
  
  if (isset($_SESSION['mensaje_error'])) {
      echo '<div class="alert alert-error" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border: 1px solid #f5c6cb; border-radius: 5px;">';
      echo $_SESSION['mensaje_error'];
      echo '</div>';
      unset($_SESSION['mensaje_error']);
  }
  ?>
  
  <div class="products-grid">
    <?php
    // Consulta SQL para obtener productos disponibles
    $sql = "SELECT DISTINCT
              a.id_articulo, 
              a.descripcion, 
              d.precio,
              d.existencia,
              d.estatus,
              d.id_detalle_articulo
            FROM articulos a
            INNER JOIN detalle_articulos d ON a.id_detalle_articulo = d.id_detalle_articulo
            WHERE d.estatus = 'Disponible'
            ORDER BY a.id_articulo";
    
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
            
            // Mostrar imágenes
            echo '<div class="product-images">';
            if (!empty($imagenes)) {
                foreach ($imagenes as $index => $imagen) {
                    $activeClass = ($index === 0) ? ' active' : '';
                    echo '<img src="../Imagenes/' . htmlspecialchars($imagen) . '" alt="' . htmlspecialchars($row["descripcion"]) . '" class="product-img' . $activeClass . '">';
                }
                if (count($imagenes) > 1) {
                    echo '<div class="image-indicators">';
                    for ($i = 0; $i < count($imagenes); $i++) {
                        echo '<span class="indicator' . ($i === 0 ? ' active' : '') . '" data-index="' . $i . '"></span>';
                    }
                    echo '</div>';
                }
            } else {
                echo '<img src="../Imagenes/no-image.png" alt="Sin imagen" class="product-img active">';
            }
            echo '</div>';
            
            echo '<div class="product-content">';
            echo '<h3>Artículo: ' . htmlspecialchars($row["id_articulo"]) . '</h3>';
            echo '<p>' . htmlspecialchars($row["descripcion"]) . '</p>';
            
            // Mostrar algunos atributos principales
            if (!empty($atributos)) {
                echo '<div class="product-attributes">';
                $contador = 0;
                foreach ($atributos as $nombre => $valor) {
                    if ($contador < 3) { // Mostrar solo los primeros 3 atributos
                        echo '<span class="attribute"><strong>' . htmlspecialchars($nombre) . ':</strong> ' . htmlspecialchars($valor) . '</span> ';
                        $contador++;
                    }
                }
                if (count($atributos) > 3) {
                    echo '<span class="attribute">+' . (count($atributos) - 3) . ' más...</span>';
                }
                echo '</div>';
            }
            
            echo '<p class="price">$' . number_format($row["precio"], 2) . ' MXN</p>';
            echo '<p class="stock">Existencia: ' . $row["existencia"] . '</p>';
            
            echo '<a href="Detalle_articulo.php?id=' . $row["id_articulo"] . '" class="btn-ver-detalle">Ver Detalles</a>';
            echo '</div>';
            echo '</div>';
            
            $stmt->close();
            $stmt_attr->close();
        }
    } else {
        echo '<div class="no-products">No hay productos disponibles en este momento.</div>';
    }

    $conn->close();
    ?>
  </div>
</div>

<script>
  // Inicializar carrusel de imágenes
  window.onload = () => {
    initImageCarousel();
  };

  function initImageCarousel() {
    const products = document.querySelectorAll('.product');
    
    products.forEach(product => {
      const images = product.querySelectorAll('.product-images .product-img');
      const indicators = product.querySelectorAll('.indicator');
      
      if (images.length > 1) {
        let currentIndex = 0;
        let interval;
        let isTransitioning = false;
        
        // Función para cambiar imagen
        function showImage(index) {
          if (isTransitioning) return;
          
          isTransitioning = true;
          
          // Quitar clase active de todas las imágenes e indicadores
          images.forEach(img => img.classList.remove('active'));
          indicators.forEach(indicator => indicator.classList.remove('active'));
          
          // Agregar clase active a la imagen e indicador correspondiente
          images[index].classList.add('active');
          indicators[index].classList.add('active');
          
          currentIndex = index;
          
          // Permitir nueva transición después del tiempo de transición
          setTimeout(() => {
            isTransitioning = false;
          }, 800);
        }
        
        // Auto-play de imágenes
        function startAutoPlay() {
          interval = setInterval(() => {
            const nextIndex = (currentIndex + 1) % images.length;
            showImage(nextIndex);
          }, 4000);
        }
        
        function stopAutoPlay() {
          if (interval) {
            clearInterval(interval);
            interval = null;
          }
        }
        
        // Event listeners para indicadores
        indicators.forEach((indicator, index) => {
          indicator.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            stopAutoPlay();
            showImage(index);
            // Reanudar después de 6 segundos
            setTimeout(() => {
              if (!interval) startAutoPlay();
            }, 6000);
          });
        });
        
        // Pausar auto-play al hover
        product.addEventListener('mouseenter', stopAutoPlay);
        product.addEventListener('mouseleave', () => {
          if (!interval) startAutoPlay();
        });
        
        // Inicializar con la primera imagen activa
        showImage(0);
        
        // Iniciar auto-play después de un breve delay
        setTimeout(startAutoPlay, 2000);
      }
    });
  }
</script>

</body>
<?php
include ('../Nav/footer.php');
?>
</html>