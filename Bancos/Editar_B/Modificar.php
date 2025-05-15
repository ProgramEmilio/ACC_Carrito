<?php
include('../../BD/ConexionBD.php');
include('../../Nav/header2.php');

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

$id_usuario = $_GET['id_usuario'];

$query = "SELECT u.*, p.*
          FROM usuario u
          JOIN persona p ON u.id_usuario = p.id_usuario
          WHERE u.id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$datos = $result->fetch_assoc();

$roles = $conn->query("SELECT id_rol, roles FROM roles");
$departamentos = $conn->query("SELECT id_departamento, nombre_departamento FROM departamento");
$modos_pago = ['Efectivo', 'Tarjeta', 'Cheque'];
?>

<body>
<h1 class="titulo">Modificar Usuario</h1>

<form class="form_reg_usuario" action="Editar_U.php" method="POST">
    <input type="hidden" name="id_usuario" value="<?= $datos['id_usuario'] ?>">
    <input type="hidden" name="id_persona" value="<?= $datos['id_persona'] ?>">

    <!-- Usuario -->
    <label>Nombre de Usuario:</label>
    <input type="text" name="nombre_usuario" value="<?= $datos['nombre_usuario'] ?>" required><br><br>

    <label>Correo Electrónico:</label>
    <input type="email" name="correo" value="<?= $datos['correo'] ?>" required><br><br>

    <label>Contraseña:</label>
    <input type="password" name="contraseña" value="<?= $datos['contraseña'] ?>" required><br><br>

    <!-- Rol -->
    <label>Rol:</label>
    <select name="id_rol" required>
        <option value="">Seleccionar rol</option>
        <?php while ($rol = $roles->fetch_assoc()): ?>
            <option value="<?= $rol['id_rol'] ?>" <?= ($rol['id_rol'] == $datos['id_rol']) ? 'selected' : '' ?>>
                <?= $rol['roles'] ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>

    <!-- Departamento -->
    <label>Departamento:</label>
    <select name="id_departamento" required>
        <option value="">Seleccionar departamento</option>
        <?php while ($dep = $departamentos->fetch_assoc()): ?>
            <option value="<?= $dep['id_departamento'] ?>" <?= ($dep['id_departamento'] == $datos['id_departamento']) ? 'selected' : '' ?>>
                <?= $dep['nombre_departamento'] ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>

    <!-- Persona -->
    <label>Nombre(s):</label>
    <input type="text" name="nom_persona" value="<?= $datos['nom_persona'] ?>" required><br><br>

    <label>Apellido Paterno:</label>
    <input type="text" name="apellido_paterno" value="<?= $datos['apellido_paterno'] ?>" required><br><br>

    <label>Apellido Materno:</label>
    <input type="text" name="apellido_materno" value="<?= $datos['apellido_materno'] ?>" required><br><br>

    <label>CURP:</label>
    <input type="text" name="curp" value="<?= $datos['curp'] ?>" required><br><br>

    <label>RFC:</label>
    <input type="text" name="rfc" value="<?= $datos['rfc'] ?>" required><br><br>

    <label>Teléfono:</label>
    <input type="text" name="telefono" value="<?= $datos['telefono'] ?>" required><br><br>

    <label>Código Postal:</label>
    <input type="text" name="codigo_postal" value="<?= $datos['codigo_postal'] ?>" required><br><br>

    <label>Calle:</label>
    <input type="text" name="calle" value="<?= $datos['calle'] ?>" required><br><br>

    <label>Número Exterior:</label>
    <input type="number" name="num_ext" value="<?= $datos['num_ext'] ?>" required><br><br>

    <label>Colonia:</label>
    <input type="text" name="colonia" value="<?= $datos['colonia'] ?>" required><br><br>

    <label>Ciudad:</label>
    <input type="text" name="ciudad" value="<?= $datos['ciudad'] ?>" required><br><br>

    <label>Modo de Pago:</label>
    <select name="modo_pago" required>
        <option value="">Seleccionar forma de pago</option>
        <?php foreach ($modos_pago as $modo): ?>
            <option value="<?= $modo ?>" <?= ($modo == $datos['modo_Pago']) ? 'selected' : '' ?>><?= $modo ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Sueldo:</label>
    <input type="number" step="0.01" name="sueldo" value="<?= $datos['sueldo'] ?>" required><br><br>

    <input type="submit" value="Guardar Cambios">
</form>

<a href="../Usuario.php" class="regresar">Regresar</a>

</body>
<?php include('../../Nav/footer.php'); ?>
</html>
