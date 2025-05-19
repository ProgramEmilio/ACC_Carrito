<?php
include('../../Nav/header2.php');


?>
<h1 class="titulo">Agregar Usuario y Cliente</h1>

<form class="form_reg_usuario" action="Registrar_U.php" method="POST">
  <label>Nombre Usuario:</label><br>
  <input type="text" name="nombre_usuario" required><br>
  <label>Correo:</label><br>
  <input type="email" name="correo" required><br>
  <label>Contraseña:</label><br>
  <input type="password" name="contraseña" required><br><br>

  <h3>Datos del Cliente</h3>
  <label>Nombre:</label><br>
  <input type="text" name="nombre_cliente" required><br>
  <label>Apellido Paterno:</label><br>
  <input type="text" name="apellido_paterno" required><br>
  <label>Apellido Materno:</label><br>
  <input type="text" name="apellido_materno" required><br>
  <label>Código Postal:</label><br>
  <input type="text" name="codigo_postal" required><br>
  <label>Calle:</label><br>
  <input type="text" name="calle" required><br>
  <label>Número Exterior:</label><br>
  <input type="number" name="num_ext" required><br>
  <label>Colonia:</label><br>
  <input type="text" name="colonia" required><br>
  <label>Ciudad:</label><br>
  <input type="text" name="ciudad" required><br>
  <label>Teléfono:</label><br>
  <input type="text" name="telefono" required><br><br>

  <input type="submit" value="Guardar Cambios">
</form>

<a href="../Usuarios_B.php" class="regresar">Regresar</a>

<?php include('../../Nav/footer.php'); ?>