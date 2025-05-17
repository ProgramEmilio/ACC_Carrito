<?php
include('../../BD/ConexionBDB.php');
include('../../Nav/header_Banco2.php');

if (!$conn2) {
    die("Error de conexión: " . mysqli_conn2ect_error());
}
$id_banco = $_GET['id'];
$query = "SELECT * FROM banco WHERE id_banco = ?";
$stmt = $conn2->prepare($query);
$stmt->bind_param("i", $id_banco);
$stmt->execute();
$result = $stmt->get_result();
$banco = $result->fetch_assoc();

if (!$banco) {
    echo "<p style='color:red;'>No se encontró el banco con ID $id_banco.</p>";
    exit();
}
?>
<h1 class="titulo">Editar Banco</h1>
<form class="form_reg_usuario" method="POST" action="Editar_B.php">
  <input type="hidden" name="id_banco" value="<?= $banco['id_banco'] ?>">
  <label>Nombre del Banco:</label><br>
  <input type="text" name="nombre_banco" value="<?= $banco['nombre_banco'] ?>" required><br><br>
  <input type="submit" value="Actualizar Banco">
</form>
<a href="../Bancos.php" class="regresar">Regresar</a>
<?php include('../../Nav/footer.php'); ?>
