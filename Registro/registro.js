let idUsuario = null;

document.getElementById('paso1').addEventListener('submit', function(event) {
    event.preventDefault();
    mostrarPaso2();
});

function mostrarPaso2() {
    const usuario = document.getElementById('usuario').value.trim();
    const correo = document.getElementById('correo').value.trim();
    const contrasena = document.getElementById('contraseña').value.trim();

    if (!usuario || !correo || !contrasena) {
        alert('Todos los campos son obligatorios.');
        return;
    }

    fetch('registro_usuario.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `usuario=${encodeURIComponent(usuario)}&correo=${encodeURIComponent(correo)}&contraseña=${encodeURIComponent(contrasena)}`
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);
        if (data.success) {
            idUsuario = data.id_usuario;
            document.getElementById('paso1').style.display = 'none';
            document.getElementById('paso2').style.display = 'block';
        } else {
            alert(data.message || "Error al registrar usuario.");
        }
    })
    .catch(error => {
        console.error('Error en la solicitud:', error);
        alert("Error en la solicitud.");
    });
}

document.getElementById('paso2').addEventListener('submit', function(event) {
    event.preventDefault();

    if (!idUsuario) {
        alert("Error: ID de usuario no encontrado.");
        return;
    }

    const formData = new FormData(this);
    formData.append('id_usuario', idUsuario);

    fetch('registro_persona.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Registro completado exitosamente.');
            window.location.href = data.redirect;
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});
