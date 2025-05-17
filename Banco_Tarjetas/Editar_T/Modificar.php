<?php
include('../../BD/ConexionBDB.php');
include('../../Nav/header_Banco2.php');

// Obtener el ID de la tarjeta a modificar
$id_tarjeta = $_GET['id_tarjeta'];
$id_banco = $_GET['id_banco'];

// Consultar los datos de la tarjeta actual
$query_tarjeta = "SELECT * FROM tarjeta WHERE id_tarjeta = ?";
$stmt_tarjeta = $conn2->prepare($query_tarjeta);
$stmt_tarjeta->bind_param('i', $id_tarjeta);
$stmt_tarjeta->execute();
$resultado_tarjeta = $stmt_tarjeta->get_result();
$tarjeta = $resultado_tarjeta->fetch_assoc();

// Obtener lista de clientes para el select
$clientes = $conn2->query("SELECT * FROM cliente");
?>

<h1 class="titulo">Modificar Tarjeta</h1>
<form class="form_reg_usuario" action="Editar_T.php" method="POST">
  <input type="hidden" name="id_tarjeta" value="<?= $id_tarjeta ?>">
  <input type="hidden" name="id_banco" value="<?= $id_banco ?>">
  
  <label>Número de Tarjeta:</label><br>
  <input type="text" name="numero_tarjeta" required maxlength="16" pattern="\d{16}" inputmode="numeric" value="<?= $tarjeta['numero_tarjeta'] ?>"><br>
  
  <label>CVV:</label><br>
  <input type="text" name="cvv" required maxlength="3" pattern="\d{3}" inputmode="numeric" value="<?= $tarjeta['cvv'] ?>"><br>
  
  <label>Fecha de Vencimiento:</label><br>
  <input type="date" name="fecha_vencimiento" required value="<?= date('Y-m-d', strtotime($tarjeta['fecha_vencimiento'])) ?>"><br>
  
  <label>Saldo:</label><br>
  <input type="number" step="0.01" name="saldo" required value="<?= $tarjeta['saldo'] ?>"><br>
  
  <label>Tipo de Tarjeta:</label><br>
  <select name="tipo_tarjeta" required>
    <option value="Debito" <?= ($tarjeta['tipo_tarjeta'] == 'Debito') ? 'selected' : '' ?>>Débito</option>
    <option value="Credito" <?= ($tarjeta['tipo_tarjeta'] == 'Credito') ? 'selected' : '' ?>>Crédito</option>
  </select><br>
  
  <label>Red de Pago:</label><br>
  <select name="red_pago" required>
    <option value="VISA" <?= ($tarjeta['red_pago'] == 'VISA') ? 'selected' : '' ?>>VISA</option>
    <option value="MASTERCARD" <?= ($tarjeta['red_pago'] == 'MASTERCARD') ? 'selected' : '' ?>>MASTERCARD</option>
  </select><br>
  
  <label>Titular (Cliente):</label><br>
  <select name="titular" required>
    <?php while ($c = $clientes->fetch_assoc()): ?>
      <option value="<?= $c['id_cliente'] ?>" <?= ($tarjeta['titular'] == $c['id_cliente']) ? 'selected' : '' ?>>
        <?= $c['nombre_cliente'] . " " . $c['apellido_paterno'] ?>
      </option>
    <?php endwhile; ?>
  </select><br><br>

  <input type="submit" value="Actualizar Tarjeta">
</form>

<a href="../DetalleBanco.php?id=<?= $id_banco ?>" class="regresar">Regresar</a>
<?php include('../../Nav/footer.php'); ?>