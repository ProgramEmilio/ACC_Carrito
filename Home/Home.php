<?php
include('../Nav.php');
include('../Footer.php');
?>

<!DOCTYPE html>
<html lang="es">
  
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <link rel="stylesheet" href="Home.css">
  
</head>
<body>



  <div class="container">
    <div class="product">
      <img src="Imagenes/playera2.png" alt="Producto 1">
      <h3>Playera con logo</h3>
      <p>Descripción breve del producto número 1.</p>
      <p class="price">$250.00 MXN</p>
      <button class="btn" onclick="agregarAlCarrito('Playera con logo', '$250.00 MXN')">Agregar al carrito</button>
    </div>

    <div class="product">
      <img src="Imagenes/agenda1.png" alt="Producto 2">
      <h3>Agenda 2025</h3>
      <p>Este producto es excelente para el uso diario.</p>
      <p class="price">$150.00 MXN</p>
      <button class="btn" onclick="agregarAlCarrito('Agenda 2025', '$150.00 MXN')">Agregar al carrito</button>
    </div>

    <div class="product">
      <img src="Imagenes/termo3.png" alt="Producto 3">
      <h3>Termo YETI</h3>
      <p>La mejor calidad y precio.</p>
      <p class="price">$300.00 MXN</p>
      <button class="btn" onclick="agregarAlCarrito('Termo YETI', '$300.00 MXN')">Agregar al carrito</button>
    </div>
  </div>

  <script>
    let contador = 0;
    let carrito = [];

    // Cargar estado inicial del carrito desde localStorage
    window.onload = () => {
      const datosGuardados = localStorage.getItem('carrito');
      if (datosGuardados) {
        carrito = JSON.parse(datosGuardados);
        contador = carrito.length;
        document.getElementById('contador-carrito').textContent = contador;
      }
    };

    function agregarAlCarrito(nombre, precio) {
      carrito.push({ nombre, precio });
      contador++;
      document.getElementById('contador-carrito').textContent = contador;

      // Guardar en localStorage
      localStorage.setItem('carrito', JSON.stringify(carrito));
    }

    function irAlCarrito() {
      window.location.href = 'carrito.php';
    }
  </script>

</body>
</html>
