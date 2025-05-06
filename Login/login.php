<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Iniciar Sesión</title>

    <!-- Fuente Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="login.css">
</head>
<body>

    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <img src="../Imagenes/acc_logo.png" alt="Logo">
            </div>
            <h2>Iniciar Sesión</h2>
            <form action="login_validar.php" method="POST">
                <div class="input-group">
                    <input type="email" name="correo" id="email" required>
                    <label for="email">Correo Electrónico</label>
                </div>
                
                <div class="input-group">
                    <input type="password" name="password" id="password" required>
                    <label for="password">Contraseña</label>
                </div>
                <button type="submit" class="login-button">Ingresar</button>
                <p class="register-link">¿No tienes cuenta? <a href="../Registro/registro.php">Regístrate</a></p>
            </form>

        </div>
    </div>

</body>
</html>
