<?php
include('../../Nav/header2.php');

// Obtener roles desde la base de datos
$roles_result = $conn->query("SELECT id_rol, roles FROM roles");
?>

<h1 class="titulo">Agregar Usuario y Cliente</h1>

<form class="form_reg_usuario" action="Registrar_U.php" method="POST">
  <label>Nombre Usuario:</label><br>
  <input type="text" name="nombre_usuario" required><br>
  <label>Correo:</label><br>
  <input type="email" name="correo" required><br>
  <label>Contraseña:</label><br>
  <input type="password" name="contraseña" required><br>
  <label>Rol:</label><br>
  <select name="id_rol" required>
    <?php while($rol = $roles_result->fetch_assoc()): ?>
      <option value="<?= $rol['id_rol'] ?>"><?= $rol['roles'] ?></option>
    <?php endwhile; ?>
  </select><br><br>

  <h3>Datos del Cliente</h3>
  <label>Nombre:</label><br>
  <input type="text" name="nom_persona" required><br>
  <label>Apellido Paterno:</label><br>
  <input type="text" name="apellido_paterno" required><br>
  <label>Apellido Materno:</label><br>
  <input type="text" name="apellido_materno" required><br>
  <label>Teléfono:</label><br>
  <input type="text" name="telefono" required><br>
  <label>Monedero:</label><br>
  <input type="number" step="0.01" name="monedero" value="0.00" required><br><br>

  <input type="submit" value="Guardar Cambios">
</form>

<a href="../Usuarios.php" class="regresar">Regresar</a>

<?php include('../../Nav/footer.php'); ?>
