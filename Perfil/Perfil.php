<?php
session_start();
include('../Nav/header.php');
include '../BD/ConexionBD.php';

$id_usuario = $_SESSION['id_usuario'];

// Obtener id_cliente
$query_cliente = "SELECT id_cliente, nom_persona, apellido_paterno, apellido_materno, telefono  
FROM cliente 
WHERE id_usuario = ?";
$stmt = $conn->prepare($query_cliente);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();


if ($row = $result->fetch_assoc()) {
    $id_cliente = $row['id_cliente'];

    // Consultar todas las direcciones
    $query_direccion = "SELECT * FROM direccion WHERE id_cliente = ?";
    $stmt2 = $conn->prepare($query_direccion);
    $stmt2->bind_param("i", $id_cliente);
    $stmt2->execute();
    $direccion_result = $stmt2->get_result();
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="datos_perfil_container">
<h1 class="titulo">Mi información</h1>

<form action="actualizar_clientes.php" method="POST" class="form_edi_usuario">
    <?php
    $query_cliente = "SELECT id_cliente, nom_persona, apellido_paterno, apellido_materno, telefono  
                      FROM cliente 
                      WHERE id_usuario = ?";
    $stmt = $conn->prepare($query_cliente);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        ?>
            <input type="hidden" name="id_cliente[]" value="<?= $row['id_cliente'] ?>">

            <label>Nombre:</label><br>
            <input type="text" name="nom_persona[]" value="<?= htmlspecialchars($row['nom_persona']) ?>"><br>

            <label>Apellido Paterno:</label><br>
            <input type="text" name="apellido_paterno[]" value="<?= htmlspecialchars($row['apellido_paterno']) ?>"><br>

            <label>Apellido Materno:</label><br>
            <input type="text" name="apellido_materno[]" value="<?= htmlspecialchars($row['apellido_materno']) ?>"><br>

            <label>Teléfono:</label><br>
            <input type="text" name="telefono[]" value="<?= htmlspecialchars($row['telefono']) ?>"><br>
            <input class="btn" type="submit" value="Guardar Cambios">
        </fieldset>
        <?php
    }
    ?>
</form>

</div>
        <div class="paneles-container">
    <a href="direccion_usuario.php" class="panel">
        <i class="fas fa-map-marker-alt icono"></i>
        <span>Dirección</span>
    </a>
    <a href="Formas_pago.php" class="panel">
        <i class="fas fa-credit-card icono"></i>
        <span>Tarjetas</span>
    </a>
</div>


</body>
<?php
include('../Nav/footer.php');
?>
</html>
