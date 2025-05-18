<?php
include('../Nav/header.php');

// Verificar que se recibió el parámetro id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: Home.php");
    exit();
}

$id_articulo = $_GET['id'];

// Consulta para obtener detalles del artículo
$sql = "SELECT DISTINCT
          a.id_articulo, 
          a.descripcion, 
          d.precio,
          d.existencia,
          d.estatus,
          d.id_detalle_articulo
        FROM articulos a
        INNER JOIN detalle_articulos d ON a.id_detalle_articulo = d.id_detalle_articulo
        WHERE a.id_articulo = ? AND d.estatus = 'Disponible'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id_articulo);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: Home.php");
    exit();
}

$producto = $resultado->fetch_assoc();

// Obtener todas las imágenes del artículo
$sql_imagenes = "SELECT ac.valor 
                FROM articulo_completo ac
                INNER JOIN atributos at ON ac.id_atributo = at.id_atributo
                WHERE ac.id_articulo = ? AND at.nombre = 'Imagen'";

$stmt_imagenes = $conn->prepare($sql_imagenes);
$stmt_imagenes->bind_param("s", $id_articulo);
$stmt_imagenes->execute();
$resultado_imagenes = $stmt_imagenes->get_result();

$imagenes = [];
while ($img = $resultado_imagenes->fetch_assoc()) {
    $imagenes[] = $img['valor'];
}

// Obtener todos los atributos del artículo
$sql_atributos = "SELECT at.nombre, ac.valor 
                 FROM articulo_completo ac
                 INNER JOIN atributos at ON ac.id_atributo = at.id_atributo
                 WHERE ac.id_articulo = ? AND at.nombre != 'Imagen'
                 ORDER BY at.nombre";

$stmt_atributos = $conn->prepare($sql_atributos);
$stmt_atributos->bind_param("s", $id_articulo);
$stmt_atributos->execute();
$resultado_atributos = $stmt_atributos->get_result();

$atributos = [];
while ($attr = $resultado_atributos->fetch_assoc()) {
    $atributos[$attr['nombre']] = $attr['valor'];
}

$stmt->close();
$stmt_imagenes->close();
$stmt_atributos->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="Home.css" />
  <title>Detalle del producto - <?php echo htmlspecialchars($producto['id_articulo']); ?></title>
  <style>
    .detalle-container {
      max-width: 1200px;
      margin: 20px auto;
      padding: 20px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .producto-detalle {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      margin-bottom: 30px;
    }

    .imagenes-container {
      position: relative;
    }

    .imagen-principal {
      width: 100%;
      height: 400px;
      background-color: #f8f9fa;
      border-radius: 10px;
      overflow: hidden;
      position: relative;
      border: 2px solid #e9ecef;
    }

    .imagen-principal img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      position: absolute;
      top: 0;
      left: 0;
      opacity: 0;
      transition: opacity 0.5s ease-in-out;
      background-color: white;
    }

    .imagen-principal img.active {
      opacity: 1;
    }

    .imagenes-miniatura {
      display: flex;
      gap: 10px;
      margin-top: 15px;
      flex-wrap: wrap;
      justify-content: center;
    }

    .miniatura {
      width: 80px;
      height: 80px;
      border: 2px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
      cursor: pointer;
      transition: border-color 0.3s;
    }

    .miniatura:hover,
    .miniatura.active {
      border-color: #3498db;
    }

    .miniatura img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      background-color: white;
    }

    .navegacion-imagenes {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background-color: rgba(0,0,0,0.5);
      color: white;
      border: none;
      padding: 10px 15px;
      cursor: pointer;
      border-radius: 50%;
      font-size: 18px;
      transition: background-color 0.3s;
    }

    .navegacion-imagenes:hover {
      background-color: rgba(0,0,0,0.7);
    }

    .nav-izquierda {
      left: 10px;
    }

    .nav-derecha {
      right: 10px;
    }

    .informacion-producto {
      padding: 20px 0;
    }

    .titulo-producto {
      font-size: 2em;
      color: #333;
      margin-bottom: 10px;
      border-bottom: 2px solid #3498db;
      padding-bottom: 10px;
    }

    .precio-producto {
      font-size: 2.5em;
      color: #e74c3c;
      font-weight: bold;
      margin: 20px 0;
    }

    .stock-info {
      font-size: 1.2em;
      color: #27ae60;
      margin-bottom: 20px;
      padding: 10px;
      background-color: #f8f9fa;
      border-radius: 5px;
      border-left: 4px solid #27ae60;
    }

    .descripcion-producto {
      font-size: 1.1em;
      line-height: 1.6;
      color: #555;
      margin-bottom: 25px;
      padding: 15px;
      background-color: #f8f9fa;
      border-radius: 8px;
    }

    .atributos-lista {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 30px;
    }

    .atributo-item {
      background-color: #fff;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 15px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .atributo-nombre {
      font-weight: bold;
      color: #3498db;
      font-size: 0.9em;
      text-transform: uppercase;
      margin-bottom: 5px;
    }

    .atributo-valor {
      color: #333;
      font-size: 1.1em;
    }

    .formulario-compra {
      background-color: #f8f9fa;
      padding: 25px;
      border-radius: 10px;
      border: 2px solid #e9ecef;
    }

    .campo-cantidad {
      margin-bottom: 20px;
    }

    .campo-cantidad label {
      display: block;
      font-weight: bold;
      margin-bottom: 5px;
      color: #333;
    }

    .campo-cantidad input {
      width: 100px;
      padding: 10px;
      border: 2px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      text-align: center;
    }

    .campo-personalizacion {
      margin-bottom: 25px;
    }

    .campo-personalizacion label {
      display: block;
      font-weight: bold;
      margin-bottom: 8px;
      color: #333;
    }

    .campo-personalizacion select {
      width: 100%;
      padding: 12px;
      border: 2px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      background-color: white;
    }

    .botones-accion {
      display: flex;
      gap: 15px;
      margin-top: 20px;
    }

    .btn-agregar-carrito {
      flex: 1;
      background-color: #27ae60;
      color: white;
      padding: 15px 25px;
      border: none;
      border-radius: 8px;
      font-size: 1.1em;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .btn-agregar-carrito:hover {
      background-color: #219a52;
    }

    .btn-volver {
      background-color: #3498db;
      color: white;
      padding: 15px 25px;
      border: none;
      border-radius: 8px;
      text-decoration: none;
      font-size: 1.1em;
      text-align: center;
      transition: background-color 0.3s;
    }

    .btn-volver:hover {
      background-color: #2980b9;
      color: white;
      text-decoration: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .producto-detalle {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .botones-accion {
        flex-direction: column;
      }
      
      .detalle-container {
        margin: 10px;
        padding: 15px;
      }
    }

    .alert {
      margin-bottom: 20px;
      padding: 15px;
      border-radius: 5px;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
  </style>
</head>
<body>

<div class="detalle-container">
  <?php
 /* // Mostrar mensajes de éxito o error
  if (isset($_SESSION['mensaje_exito'])) {
      echo '<div class="alert alert-success">';
      echo $_SESSION['mensaje_exito'];
      echo '</div>';
      unset($_SESSION['mensaje_exito']);
  }*/
  
  if (isset($_SESSION['mensaje_error'])) {
      echo '<div class="alert alert-error">';
      echo $_SESSION['mensaje_error'];
      echo '</div>';
      unset($_SESSION['mensaje_error']);
  }
  ?>

  <div class="producto-detalle">
    <!-- Sección de imágenes -->
    <div class="imagenes-container">
      <div class="imagen-principal">
        <?php if (!empty($imagenes)): ?>
          <?php foreach ($imagenes as $index => $imagen): ?>
            <img src="../Imagenes/<?php echo htmlspecialchars($imagen); ?>" 
                 alt="<?php echo htmlspecialchars($producto['descripcion']); ?>" 
                 class="imagen-detalle <?php echo $index === 0 ? 'active' : ''; ?>"
                 data-index="<?php echo $index; ?>">
          <?php endforeach; ?>
          
          <?php if (count($imagenes) > 1): ?>
            <button class="navegacion-imagenes nav-izquierda" onclick="cambiarImagen(-1)">‹</button>
            <button class="navegacion-imagenes nav-derecha" onclick="cambiarImagen(1)">›</button>
          <?php endif; ?>
        <?php else: ?>
          <img src="../Imagenes/no-image.png" alt="Sin imagen" class="imagen-detalle active">
        <?php endif; ?>
      </div>
      
      <?php if (count($imagenes) > 1): ?>
        <div class="imagenes-miniatura">
          <?php foreach ($imagenes as $index => $imagen): ?>
            <div class="miniatura <?php echo $index === 0 ? 'active' : ''; ?>" onclick="mostrarImagen(<?php echo $index; ?>)">
              <img src="../Imagenes/<?php echo htmlspecialchars($imagen); ?>" alt="Miniatura <?php echo $index + 1; ?>">
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Sección de información -->
    <div class="informacion-producto">
      <h1 class="titulo-producto"><?php echo htmlspecialchars($producto['id_articulo']); ?></h1>
      
      <p class="precio-producto">$<?php echo number_format($producto['precio'], 2); ?> MXN</p>
      
      <div class="stock-info">
        <strong>Disponibilidad:</strong> <?php echo $producto['existencia']; ?> unidades en stock
      </div>
      
      <div class="descripcion-producto">
        <h3>Descripción:</h3>
        <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
      </div>
      
      <?php if (!empty($atributos)): ?>
        <h3>Características:</h3>
        <div class="atributos-lista">
          <?php foreach ($atributos as $nombre => $valor): ?>
            <div class="atributo-item">
              <div class="atributo-nombre"><?php echo htmlspecialchars($nombre); ?></div>
              <div class="atributo-valor"><?php echo htmlspecialchars($valor); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      
      <!-- Formulario para agregar al carrito -->
      <div class="formulario-compra">
        <form action="Agregar_Carrito.php" method="POST">
          <input type="hidden" name="id_articulo" value="<?php echo htmlspecialchars($producto['id_articulo']); ?>">
          <input type="hidden" name="precio" value="<?php echo $producto['precio']; ?>">
          <input type="hidden" name="descripcion" value="<?php echo htmlspecialchars($producto['descripcion']); ?>">
          <input type="hidden" name="id_detalle_articulo" value="<?php echo $producto['id_detalle_articulo']; ?>">
          
          <div class="campo-cantidad">
            <label for="cantidad">Cantidad:</label>
            <input type="number" id="cantidad" name="cantidad" value="1" min="1" max="<?php echo $producto['existencia']; ?>" required>
          </div>
          
          <div class="campo-personalizacion">
            <label for="personalizacion">Personalización:</label>
            <select name="personalizacion" id="personalizacion">
              <option value="">Sin personalización</option>
              <option value="Icono">Con Icono</option>
              <option value="Texto">Con Texto</option>
              <option value="Imagen">Con Imagen personalizada</option>
            </select>
          </div>
          
          <div class="botones-accion">
            <button type="submit" class="btn-agregar-carrito">Agregar al carrito</button>
            <a href="Home.php" class="btn-volver">Volver a la galería</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  let imagenActual = 0;
  const totalImagenes = <?php echo count($imagenes); ?>;

  function mostrarImagen(index) {
    // Ocultar imagen actual
    const imagenActiva = document.querySelector('.imagen-detalle.active');
    const miniaturaActiva = document.querySelector('.miniatura.active');
    
    if (imagenActiva) imagenActiva.classList.remove('active');
    if (miniaturaActiva) miniaturaActiva.classList.remove('active');
    
    // Mostrar nueva imagen
    const nuevaImagen = document.querySelector(`.imagen-detalle[data-index="${index}"]`);
    const nuevaMiniatura = document.querySelectorAll('.miniatura')[index];
    
    if (nuevaImagen) nuevaImagen.classList.add('active');
    if (nuevaMiniatura) nuevaMiniatura.classList.add('active');
    
    imagenActual = index;
  }

  function cambiarImagen(direccion) {
    let nuevaImagen = imagenActual + direccion;
    
    if (nuevaImagen >= totalImagenes) {
      nuevaImagen = 0;
    } else if (nuevaImagen < 0) {
      nuevaImagen = totalImagenes - 1;
    }
    
    mostrarImagen(nuevaImagen);
  }

  // Auto-play de imágenes (opcional)
  if (totalImagenes > 1) {
    setInterval(() => {
      cambiarImagen(1);
    }, 5000); // Cambiar cada 5 segundos
  }

  // Actualizar contador del carrito
  window.onload = () => {
    actualizarContadorCarrito();
  };

  function actualizarContadorCarrito() {
    const contadorCarrito = document.getElementById('contador-carrito');
    if (contadorCarrito) {
      const datosGuardados = localStorage.getItem('carrito');
      if (datosGuardados) {
        const carrito = JSON.parse(datosGuardados);
        contadorCarrito.textContent = carrito.length;
      }
    }
  }
</script>

</body>
<?php
include ('../Nav/footer.php');
$conn->close();
?>
</html>