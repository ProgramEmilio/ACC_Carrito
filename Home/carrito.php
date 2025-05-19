<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Carrito de Compras</title>
  <link rel="stylesheet" href="Home.css" />
</head>
<body>

  <header>
    <h1>Mi Carrito</h1>
  </header>

  <div class="container" id="lista-carrito">
    <!-- Productos del carrito se mostrarán aquí -->
  </div>

  <script>
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    const contenedor = document.getElementById('lista-carrito');

    if (carrito.length === 0) {
      contenedor.innerHTML = '<p>Tu carrito está vacío.</p>';
    } else {
      carrito.forEach((producto, index) => {
        const div = document.createElement('div');
        div.classList.add('product');
        div.innerHTML = `
          <h3>${producto.nombre}</h3>
          <p class="price">${producto.precio}</p>
          <button class="btn" onclick="eliminarProducto(${index})">Eliminar</button>
        `;
        contenedor.appendChild(div);
      });
    }

    function eliminarProducto(indice) {
      carrito.splice(indice, 1);
      localStorage.setItem('carrito', JSON.stringify(carrito));
      location.reload();
    }
  </script>

</body>
</html>
