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
    <link rel="stylesheet" href="Perfil.css">
</head>
<body>
<h1 class="titulo">Información Direcciones</h1>

<h2 class="sub_titulo">Mis Direcciones Guardadas</h2>

<?php if ($direccion_result->num_rows > 0): ?>
    <table border="1" cellpadding="5" cellspacing="0" class="tabla_forma_pago">
        <tr>
            <th>Calle</th>
            <th>Núm. Ext</th>
            <th>Colonia</th>
            <th>Ciudad</th>
            <th>Estado</th>
            <th>Código Postal</th>
            <th colspan="2">Acciones</th>
        </tr>
        <?php while ($direccion = $direccion_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($direccion['calle']) ?></td>
                <td><?= htmlspecialchars($direccion['num_ext']) ?></td>
                <td><?= htmlspecialchars($direccion['colonia']) ?></td>
                <td><?= htmlspecialchars($direccion['ciudad']) ?></td>
                <td><?= htmlspecialchars($direccion['estado']) ?></td>
                <td><?= htmlspecialchars($direccion['codigo_postal']) ?></td>
                <td><a class="accion" href="editar_direccion.php?id_direccion=<?= $direccion['id_direccion']?>">Editar</a></td>
                <td>
                    <a class="accion" href="eliminar_direccion.php?id_direccion=<?= $direccion['id_direccion'] ?>"
                       onclick="return confirm('¿Estás seguro de que quieres eliminar esta dirección?');">
                       Eliminar</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No tienes direcciones guardadas aún.</p>
<?php endif; ?>

<h2 class="sub_titulo">Agregar Nueva Dirección</h2>
<div>
    
    <form method="POST" action="guardar_direccion.php" class="form_edi_usuario">
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
        <label>Estado:
            <input type="text" name="estado" required>
        </label>
        <label>Código Postal:
            <input type="text" name="codigo_postal" maxlength="5" required>
        </label>
        <input class="btn" type="submit" value="Guardar Dirección">
    </form>
</div>

       <a href="Perfil.php" class="regresar">Regresar</a>

</body>
<?php
include('../Nav/footer.php');
?>
</html>
