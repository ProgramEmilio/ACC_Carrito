<?php
include('../BD/ConexionBDB.php');
include('../Nav/header_Banco.php');

$query = "SELECT * FROM banco";
$resultado = $conn->query($query);
?>
<h1 class="titulo">Lista de Bancos</h1>
<a href="Registro_B/Registro_Banco.php" class="button">Agregar Banco</a>
<table class='tabla'>
  <tr>
    <th>ID Banco</th>
    <th>Nombre del Banco</th>
    <th>Acciones</th>
  </tr>
  <?php while ($row = $resultado->fetch_assoc()): ?>
  <tr>
    <td><?= $row['id_banco'] ?></td>
    <td><?= $row['nombre_banco'] ?></td>
    <td>
      <a href="Editar_B/Modificar.php?id=<?= $row['id_banco'] ?>" class="button">Editar</a> |
      <a href="Eliminar_B/Eliminar_Banco.php?id=<?= $row['id_banco'] ?>" onclick="return confirm('¿Eliminar banco?')" class="button">Eliminar</a> | 
      <a href=" ../Banco_Tarjetas\DetalleBanco.php?id=<?= $row['id_banco'] ?>" class="button">Registros</a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php include('../Nav/footer.php'); ?>