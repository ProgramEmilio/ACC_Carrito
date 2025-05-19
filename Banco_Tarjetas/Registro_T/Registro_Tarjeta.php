<?php
include('../../Nav/header2.php');

$id_banco = $_GET['id_banco'];

$clientes = $conn2->query("SELECT * FROM cliente");
?>

<h1 class="titulo">Agregar Tarjeta</h1>
<form class="form_reg_usuario" action="Registrar_T.php" method="POST">
  <input type="hidden" name="id_banco" value="<?= $id_banco ?>">
  <label>Número de Tarjeta:</label><br>
  <input type="text" name="numero_tarjeta" required maxlength="16" pattern="\d{16}" inputmode="numeric"><br>
  <label>CVV:</label><br>
  <input type="text" name="cvv" required maxlength="3" pattern="\d{3}" inputmode="numeric"><br>
  <label>Fecha de Vencimiento:</label><br>
  <input type="date" name="fecha_vencimiento" required><br>
  <label>Saldo:</label><br>
  <input type="number" step="0.01" name="saldo" required><br>
  <label>Tipo de Tarjeta:</label><br>
  <select name="tipo_tarjeta" required>
    <option value="Debito">Débito</option>
    <option value="Credito">Crédito</option>
  </select><br>
  <label>Red de Pago:</label><br>
  <select name="red_pago" required>
    <option value="VISA">VISA</option>
    <option value="MASTERCARD">MASTERCARD</option>
  </select><br>
  <label>Titular (Cliente):</label><br>
  <select name="titular" required>
    <?php while ($c = $clientes->fetch_assoc()): ?>
      <option value="<?= $c['id_cliente'] ?>">
        <?= $c['nombre_cliente'] . " " . $c['apellido_paterno'] ?>
      </option>
    <?php endwhile; ?>
  </select><br><br>

  <input type="submit" value="Guardar Tarjeta">
</form>

<a href="../DetalleBanco.php?id=<?= $id_banco ?>" class="regresar">Regresar</a>
<?php include('../../Nav/footer.php'); ?>