<?php
  include('../../Nav/header2.php');
  ?>
  <h1 class="titulo">Agregar Banco</h1>
  <form class="form_reg_usuario" action="Registrar_B.php" method="POST">
    <label>Nombre del Banco:</label><br>
    <input type="text" name="nombre_banco" required><br><br>
    <input type="submit" value="Guardar Banco">
  </form>
  <a href="../Bancos.php" class="regresar">Regresar</a>
  <?php include('../../Nav/footer.php'); ?>
