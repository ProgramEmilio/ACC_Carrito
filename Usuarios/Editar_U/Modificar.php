<?php
include('../../Nav/header2.php');

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

$id_usuario = $_GET['id'];
$query = "SELECT u.*, c.* FROM usuario u JOIN cliente c ON u.id_usuario = c.id_usuario WHERE u.id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

?>

<h2>Editar Usuario y Cliente</h2>
<form class="form_reg_usuario" action="Editar_B.php" method="POST">
  <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
  <label>Nombre Usuario:</label><br>
  <input type="text" name="nombre_usuario" value="<?= $usuario['nombre_usuario'] ?>" required><br>
  <label>Correo:</label><br>
  <input type="email" name="correo" value="<?= $usuario['correo'] ?>" required><br>
  <label>Contraseña:</label><br>
  <input type="password" name="contraseña" value="<?= $usuario['contraseña'] ?>" required><br><br>

  <h3>Datos del Cliente</h3>
  <label>Nombre:</label><br>
  <input type="text" name="nombre_cliente" value="<?= $usuario['nombre_cliente'] ?>" required><br>
  <label>Apellido Paterno:</label><br>
  <input type="text" name="apellido_paterno" value="<?= $usuario['apellido_paterno'] ?>" required><br>
  <label>Apellido Materno:</label><br>
  <input type="text" name="apellido_materno" value="<?= $usuario['apellido_materno'] ?>" required><br>
  <label>Código Postal:</label><br>
  <input type="text" name="codigo_postal" value="<?= $usuario['codigo_postal'] ?>" required><br>
  <label>Calle:</label><br>
  <input type="text" name="calle" value="<?= $usuario['calle'] ?>" required><br>
  <label>Número Exterior:</label><br>
  <input type="number" name="num_ext" value="<?= $usuario['num_ext'] ?>" required><br>
  <label>Colonia:</label><br>
  <input type="text" name="colonia" value="<?= $usuario['colonia'] ?>" required><br>
  <label>Ciudad:</label><br>
  <input type="text" name="ciudad" value="<?= $usuario['ciudad'] ?>" required><br>
  <label>Teléfono:</label><br>
  <input type="text" name="telefono" value="<?= $usuario['telefono'] ?>" required><br><br>

  <input type="submit" value="Guardar Cambios">
</form>

<a href="../Usuarios_B.php" class="regresar">Regresar</a>

</body>
<?php include('../../Nav/footer.php'); ?>
</html>