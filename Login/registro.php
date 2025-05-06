<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Crear Cuenta</title>

    <!-- Fuente Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="registro.css">
</head>
<body>

    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <img src="../Imagenes/acc_logo.png" alt="Logo">
            </div>
            <h2>Crear Cuenta</h2>
            <form>
                <div class="input-group">
                    <input type="text" id="nombre" required>
                    <label for="nombre">Nombre</label>
                </div>
                <div class="input-group">
                    <input type="text" id="apellido" required>
                    <label for="apellido">Apellido</label>
                </div>
                <div class="input-group">
                    <input type="email" id="correo" required>
                    <label for="correo">Correo Electrónico</label>
                </div>
                <div class="input-group">
                    <input type="text" id="direccion" required>
                    <label for="direccion">Dirección</label>
                </div>
                <div class="input-group">
                    <input type="tel" id="telefono" required>
                    <label for="telefono">Teléfono</label>
                </div>
                <button type="submit" class="register-button">Crear Cuenta</button>
                <p class="login-link">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
            </form>
        </div>
    </div>

</body>
</html>
