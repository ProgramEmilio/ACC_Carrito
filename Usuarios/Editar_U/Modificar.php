<?php
include('../../Nav/header2.php');

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

$id_usuario = $_GET['id'];

// Obtener datos del usuario y cliente
$query = "SELECT u.*, c.*, r.roles AS nombre_rol FROM usuario u
          JOIN cliente c ON u.id_usuario = c.id_usuario
          JOIN roles r ON u.id_rol = r.id_rol
          WHERE u.id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Obtener todos los roles
$roles_result = $conn->query("SELECT id_rol, roles FROM roles");
?>

<h2>Editar Usuario y Cliente</h2>
<form class="form_reg_usuario" action="Editar_B.php" method="POST">
  <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">

  <label>Nombre Usuario:</label><br>
  <input type="text" name="nombre_usuario" value="<?= $usuario['nombre_usuario'] ?>" required><br>
  <label>Correo:</label><br>
  <input type="email" name="correo" value="<?= $usuario['correo'] ?>" required><br>
  <label>Contraseña:</label><br>
  <input type="password" name="contraseña" value="<?= $usuario['contraseña'] ?>" required><br>
  <label>Rol:</label><br>
  <select name="id_rol" required>
    <?php while ($rol = $roles_result->fetch_assoc()): ?>
      <option value="<?= $rol['id_rol'] ?>" <?= $rol['id_rol'] == $usuario['id_rol'] ? 'selected' : '' ?>>
        <?= $rol['roles'] ?>
      </option>
    <?php endwhile; ?>
  </select><br><br>

  <h3>Datos del Cliente</h3>
  <label>Nombre:</label><br>
  <input type="text" name="nom_persona" value="<?= $usuario['nom_persona'] ?>" required><br>
  <label>Apellido Paterno:</label><br>
  <input type="text" name="apellido_paterno" value="<?= $usuario['apellido_paterno'] ?>" required><br>
  <label>Apellido Materno:</label><br>
  <input type="text" name="apellido_materno" value="<?= $usuario['apellido_materno'] ?>" required><br>
  <label>Teléfono:</label><br>
  <input type="text" name="telefono" value="<?= $usuario['telefono'] ?>" required><br>
  <label>Monedero:</label><br>
  <input type="number" step="0.01" name="monedero" value="<?= $usuario['monedero'] ?>" required><br><br>

  <input type="submit" value="Guardar Cambios">
</form>

<a href="../Usuarios.php" class="regresar">Regresar</a>

<?php include('../../Nav/footer.php'); ?>
