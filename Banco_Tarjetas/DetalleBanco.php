<?php
include('../Nav/header.php');

$id_banco = $_GET['id'];

$query = "SELECT t.*, c.nombre_cliente, c.apellido_paterno, c.apellido_materno
          FROM tarjeta t
          JOIN cliente c ON t.titular = c.id_cliente
          WHERE t.id_banco = ?";
$stmt = $conn2->prepare($query);
$stmt->bind_param("i", $id_banco);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<h1 class="titulo">Tarjetas del Banco</h1>
<a href="Registro_T/Registro_Tarjeta.php?id_banco=<?= $id_banco ?>" class="button">Agregar Tarjeta</a>
<table class="tabla">
  <tr>
    <th>ID Tarjeta</th>
    <th>Número</th>
    <th>Tipo</th>
    <th>Red</th>
    <th>Titular</th>
    <th>Saldo</th>
    <th>Acciones</th>
  </tr>
  <?php while ($row = $resultado->fetch_assoc()): ?>
    <tr>
      <td><?= $row['id_tarjeta'] ?></td>
      <td><?= $row['numero_tarjeta'] ?></td>
      <td><?= $row['tipo_tarjeta'] ?></td>
      <td><?= $row['red_pago'] ?></td>
      <td><?= $row['nombre_cliente'] . ' ' . $row['apellido_paterno'] ?></td>
      <td><?= $row['saldo'] ?></td>
      <td>
        <a href="Editar_T/Modificar.php?id_tarjeta=<?= $row['id_tarjeta'] ?>&id_banco=<?= $id_banco ?>" class="button">Editar</a> |
        <a href="Eliminar_T/Eliminar_Tarjeta.php?id_tarjeta=<?= $row['id_tarjeta'] ?>&id_banco=<?= $id_banco ?>" class="button" onclick="return confirm('¿Eliminar esta tarjeta?')">Eliminar</a>
      </td>
    </tr>
  <?php endwhile; ?>
</table>

<a href="../Bancos\Bancos.php" class="regresar">Regresar</a>

<?php include('../Nav/footer.php'); ?>
