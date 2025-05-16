<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de usuario</title>
    <link rel="stylesheet" href="registro.css"> 
</head>
<body>


<div class="login-container">
    <div class="login-box">
    <div class="logo">
                <img src="../Imagenes/acc_logo.png" alt="Logo">
    </div>
        <h2>Registro</h2>
        <form action="procesar_registro.php" method="POST">
            <div class="input-group">
                <input type="text" name="nombre_usuario" required>
                <label for="nombre_usuario">Nombre de usuario</label>
            </div>
            <div class="input-group">
                <input type="email" name="correo" required>
                <label for="correo">Correo</label>
            </div>
            <div class="input-group">
                <input type="password" name="contraseña" required>
                <label for="contraseña">Contraseña</label>
            </div>
            <button type="submit" class="login-button">Registrar</button>

            <br></br>
            <div class="login-link">
                    ¿Ya tienes cuenta? <a href="../Login/login.php">Inicia sesión aquí</a>
                </div>
        </form>
    </div>
</div>

</body>
</html>

