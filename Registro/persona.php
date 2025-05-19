<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Datos Personales</title>
    <link rel="stylesheet" href="registro.css"> 

</head>
<body>

    <div class="register-container">
    <div class="login-box">
        <div class="register-box">
            <h2>Datos Personales</h2>
            <strong><h2 class="subtitle">Completa tu información</h2></strong>

            <form action="procesar_persona.php" method="POST">

                <!-- Campo oculto con id_usuario -->
                <input type="hidden" name="id_usuario" value="<?php echo $_GET['id_usuario']; ?>">

                <div class="input-group">
                    <input type="text" name="nom_persona" required>
                    <label>Nombre</label>
                </div>

                <div class="input-group">
                    <input type="text" name="apellido_paterno" required>
                    <label>Apellido Paterno</label>
                </div>

                <div class="input-group">
                    <input type="text" name="apellido_materno" required>
                    <label>Apellido Materno</label>
                </div>


                <div class="input-group">
                    <input type="text" name="telefono" required>
                    <label>Teléfono</label>
                </div>

                <div class="input-group">
                    <input type="text" name="monedero" required>
                    <label>Monedero</label>
                </div>
                

                <button type="submit" class="register-button">Guardar Datos</button>

            </form>
        </div>
    </div>
    </div>
</body>
</html>
