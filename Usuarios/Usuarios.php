<?php
include('../Nav/header.php');

$query = "SELECT * FROM usuario";
$resultado = $conn->query($query);
?>
<h1 class="titulo">Lista de Usuarios</h1>
<a href="Registro_U\Registro_Usuario.php" class="regresar">Agregar Usuario</a>
<table class='tabla_forma_pago'>
  <tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Correo</th>
    <th>Acciones</th>
  </tr>
  <?php while ($row = $resultado->fetch_assoc()): ?>
  <tr>
    <td><?= $row['id_usuario'] ?></td>
    <td><?= $row['nombre_usuario'] ?></td>
    <td><?= $row['correo'] ?></td>
    <td>
      <a href="Editar_U/Modificar.php?id=<?= $row['id_usuario'] ?>" class="button">Editar</a> |
      <a href="Eliminar_U\Eliminar_U.php?id=<?= $row['id_usuario'] ?>" onclick="return confirm('¿Eliminar usuario?')" class="button">Eliminar</a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>

<?php include('../Nav/footer.php'); ?>