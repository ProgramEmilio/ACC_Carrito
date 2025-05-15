<?php
include('../BD/ConexionBD.php');
include('../Nav/header.php');

// Verificar conexión a la base de datos
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Consulta SQL simplificada con solo los campos necesarios
$sql = "SELECT 
            usuario.id_usuario,
            roles.roles AS Rol,
            departamento.nombre_departamento AS Departamento,
            persona.nom_persona AS Nombre,
            persona.apellido_paterno AS Apellido_Paterno, 
            persona.apellido_materno AS Apellido_Materno, 
            persona.codigo_postal,
            persona.calle,
            persona.num_ext,
            persona.colonia,
            persona.ciudad,
            persona.telefono,
            persona.modo_Pago,
            persona.sueldo
        FROM usuario
        JOIN persona ON usuario.id_usuario = persona.id_usuario
        JOIN roles ON usuario.id_rol = roles.id_rol
        JOIN departamento ON usuario.id_departamento = departamento.id_departamento";

$result = $conn->query($sql);

// Verificar si la consulta fue exitosa
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}
?>

<body>
    <h1 class="titulo">Información de Empleados</h1>
    <br>
    <table class='tabla'>
        <thead>
            <tr class='cont'>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido Paterno</th>
                <th>Apellido Materno</th>
                <th>Dirección</th>
                <th>Teléfono</th>
                <th>Rol</th>
                <th>Departamento</th>
                <th>Modo de Pago</th>
                <th>Sueldo</th>
                <th>Editar</th>
                <th>Eliminar</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($fila = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?= htmlspecialchars($fila['id_usuario']) ?></td>
                    <td><?= htmlspecialchars($fila['Nombre']) ?></td>
                    <td><?= htmlspecialchars($fila['Apellido_Paterno']) ?></td>
                    <td><?= htmlspecialchars($fila['Apellido_Materno']) ?></td>
                    <td><?= htmlspecialchars($fila['ciudad']) . ", " . htmlspecialchars($fila['codigo_postal']) . ", " .($fila['calle']) . " #" . $fila['num_ext'] . ", " . htmlspecialchars($fila['colonia']) ?></td>
                    <td><?= htmlspecialchars($fila['telefono']) ?></td>
                    <td><?= htmlspecialchars($fila['Rol']) ?></td>
                    <td><?= htmlspecialchars($fila['Departamento']) ?></td>
                    <td><?= htmlspecialchars($fila['modo_Pago']) ?></td>
                    <td>$<?= number_format($fila['sueldo'], 2) ?></td>
                    <td><a href='Editar/Modificar.php?id_usuario=<?= htmlspecialchars($fila['id_usuario']) ?>' class='editar'>Editar</a></td>
                    <td><a href='Eliminar/Eliminar_Usuario.php?id_usuario=<?= htmlspecialchars($fila['id_usuario']) ?>' class='eliminar'>Eliminar</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

<?php include('../Nav/footer.php'); ?>
</html>
