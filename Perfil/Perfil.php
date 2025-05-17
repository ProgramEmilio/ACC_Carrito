<?php
session_start();
include('../Nav/header.php');
include '../BD/ConexionBD.php';

$id_usuario = $_SESSION['id_usuario'];

// Obtener id_cliente
$query_cliente = "SELECT id_cliente FROM cliente WHERE id_usuario = ?";
$stmt = $conn->prepare($query_cliente);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $id_cliente = $row['id_cliente'];

    $query_direccion = "SELECT * FROM direccion WHERE id_cliente = ?";
    $stmt2 = $conn->prepare($query_direccion);
    $stmt2->bind_param("i", $id_cliente);
    $stmt2->execute();
    $direccion_result = $stmt2->get_result();

    $direccion = $direccion_result->num_rows > 0 ? $direccion_result->fetch_assoc() : null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Dirección</title>
    <link rel="stylesheet" href="Perfil.css">
</head>
<body>

<!-- Aquí está tu nav incluído desde Nav.php -->


<div class="direccion-container">
    <h2>Mi Dirección</h2>

    <?php if ($direccion): ?>
        <p><strong>Calle:</strong> <?= htmlspecialchars($direccion['calle']) ?></p>
        <p><strong>Número exterior:</strong> <?= $direccion['num_ext'] ?></p>
        <p><strong>Colonia:</strong> <?= htmlspecialchars($direccion['colonia']) ?></p>
        <p><strong>Ciudad:</strong> <?= htmlspecialchars($direccion['ciudad']) ?></p>
        <p><strong>Código Postal:</strong> <?= $direccion['codigo_postal'] ?></p>
        <a class="edit-link" href="editar_direccion.php">Editar dirección</a>
    <?php else: ?>
        <form method="POST" action="guardar_direccion.php">
            <input type="hidden" name="id_cliente" value="<?= $id_cliente ?>">
            <label>Calle:
                <input type="text" name="calle" required>
            </label>
            <label>Número ext:
                <input type="number" name="num_ext" required>
            </label>
            <label>Colonia:
                <input type="text" name="colonia" required>
            </label>
            <label>Ciudad:
                <input type="text" name="ciudad" required>
            </label>
            <label>Código Postal:
                <input type="text" name="codigo_postal" maxlength="5" required>
            </label>
            <input class="btn" type="submit" value="Guardar Dirección">
        </form>

        


    <?php endif; ?>
</div>
     

<div class="btn-container">
  <button class="btn" onclick="window.location.href='Formas_pago.php'">Formas de pago</button>
</div>


<style>
.btn-container {
  display: flex;
  justify-content: center; /* Centra horizontalmente */
  margin-top: 20px;        /* Espacio arriba opcional */
}

.btn {
  background-color: rgb(38, 153, 48);
  color: white;
  border: none;
  padding: 12px 24px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.btn:hover {
  background-color:rgb(38, 153, 48);
  box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}

.btn:active {
  background-color:rgb(26, 107, 33);
  box-shadow: inset 0 3px 5px rgba(0,0,0,0.2);
}
</style>


</body>
</html>
