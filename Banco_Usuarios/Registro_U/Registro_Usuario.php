<?php
include('../../BD/ConexionBD.php');
include('../../Nav/header2.php');

// Obtener roles
$roles = $conn->query("SELECT id_rol, roles FROM roles");
$departamentos = $conn->query("SELECT id_departamento, nombre_departamento FROM departamento");

// Opciones para modo de pago (estático porque es ENUM)
$modos_pago = ['Efectivo', 'Tarjeta', 'Cheque'];

$conn->close();
?>

<body>
<h1 class="titulo">Registro de Usuario</h1>

<form class="form_reg_usuario" action="Registrar_U.php" method="POST">
    <!-- Datos de usuario -->
    <label for="nombre_usuario">Nombre de Usuario:</label>
    <input type="text" id="nombre_usuario" name="nombre_usuario" maxlength="50" required><br><br>

    <label for="correo">Correo Electrónico:</label>
    <input type="email" id="correo" name="correo" required><br><br>

    <label for="contraseña">Contraseña:</label>
    <input type="password" id="contraseña" name="contraseña" maxlength="25" required><br><br>

    <!-- Select rol -->
    <label for="id_rol">Rol:</label>
    <select id="id_rol" name="id_rol" required>
        <option value="">Seleccionar rol</option>
        <?php while($row = $roles->fetch_assoc()) : ?>
            <option value="<?= $row['id_rol'] ?>"><?= $row['roles'] ?></option>
        <?php endwhile; ?>
    </select><br><br>

    <!-- Select departamento -->
    <label for="id_departamento">Departamento:</label>
    <select id="id_departamento" name="id_departamento" required>
        <option value="">Seleccionar departamento</option>
        <?php while($row = $departamentos->fetch_assoc()) : ?>
            <option value="<?= $row['id_departamento'] ?>"><?= $row['nombre_departamento'] ?></option>
        <?php endwhile; ?>
    </select><br><br>

    <!-- Datos personales -->
    <label for="nom_persona">Nombre(s):</label>
    <input type="text" id="nom_persona" name="nom_persona" maxlength="50" required><br><br>

    <label for="apellido_paterno">Apellido Paterno:</label>
    <input type="text" id="apellido_paterno" name="apellido_paterno" maxlength="20" required><br><br>

    <label for="apellido_materno">Apellido Materno:</label>
    <input type="text" id="apellido_materno" name="apellido_materno" maxlength="20" required><br><br>

    <label for="curp">CURP:</label>
    <input type="text" id="curp" name="curp" maxlength="18" required><br><br>

    <label for="rfc">RFC:</label>
    <input type="text" id="rfc" name="rfc" maxlength="13" required><br><br>

    <label for="telefono">Teléfono:</label>
    <input type="text" id="telefono" name="telefono" maxlength="10" required><br><br>

    <label for="codigo_postal">Código Postal:</label>
    <input type="text" id="codigo_postal" name="codigo_postal" maxlength="5" required><br><br>

    <label for="calle">Calle:</label>
    <input type="text" id="calle" name="calle" maxlength="20" required><br><br>

    <label for="num_ext">Número Exterior:</label>
    <input type="number" id="num_ext" name="num_ext" min="1" required><br><br>

    <label for="colonia">Colonia:</label>
    <input type="text" id="colonia" name="colonia" maxlength="50" required><br><br>

    <label for="ciudad">Ciudad:</label>
    <input type="text" id="ciudad" name="ciudad" maxlength="20" required><br><br>


    <label for="modo_pago">Modo de Pago:</label>
    <select name="modo_pago" id="modo_pago" required>
        <option value="">Seleccionar forma de pago</option>
        <?php foreach ($modos_pago as $modo) : ?>
            <option value="<?= $modo ?>"><?= $modo ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="sueldo">Sueldo:</label>
    <input type="number" step="0.01" name="sueldo" id="sueldo" required><br><br>

    <input type="submit" value="Registrar Usuario">
</form>

<a href="../Usuario.php" class="regresar">Regresar</a>


</body>
<?php include('../../Nav/footer.php'); ?>
</html>

