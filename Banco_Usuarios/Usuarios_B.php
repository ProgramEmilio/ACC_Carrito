// usuarios/index.php
<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');
session_start();

if (!isset($_SESSION['id_rol'])) {
    header("Location: ../Login/login.php");
    exit();
}

$query = "SELECT * FROM usuario";
$resultado = $conn->query($query);
?>

<h2>Lista de Usuarios</h2>
<a href="crear.php" class="btn">Agregar Usuario</a>
<table>
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
      <a href="editar.php?id=<?= $row['id_usuario'] ?>">Editar</a> |
      <a href="eliminar.php?id=<?= $row['id_usuario'] ?>" onclick="return confirm('¿Eliminar usuario?')">Eliminar</a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>

<?php include('../Nav/footer.php'); ?>