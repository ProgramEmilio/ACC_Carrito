<?php
include '../BD/ConexionBD.php';     // $conn  -> Base principal (cliente)


// Procesar eliminación si se recibe eliminar_id
if (isset($_GET['eliminar_id'])) {
    $eliminar_id = intval($_GET['eliminar_id']);
    $stmtEliminar = $conn->prepare("DELETE FROM tarjeta WHERE id_tarjeta = ?");
    $stmtEliminar->bind_param("i", $eliminar_id);
    if ($stmtEliminar->execute()) {
      
    } else {
       
    }
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $numero_tarjeta = $_POST['numero_tarjeta'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $fecha_vencimiento = ($_POST['fecha_vencimiento'] ?? '') . "-01 00:00:00";
    $tipo_tarjeta = $_POST['tipo_tarjeta'] ?? '';
    $red_pago = $_POST['red_pago'] ?? '';
    $titular = isset($_POST['titular']) ? trim($_POST['titular']) : '';  // corregido typo
    

    if (empty($titular)) {
        echo "<p style='color:red;'>El nombre del titular es obligatorio.</p>";
    } else {
        // Buscar id_cliente correspondiente al nombre completo en la base principal ($conn)
        $buscar_cliente = $conn->prepare("SELECT id_cliente FROM cliente WHERE CONCAT(nom_persona, ' ', apellido_paterno, ' ', apellido_materno) = ?");
        $buscar_cliente->bind_param("s", $titular);
        $buscar_cliente->execute();
        $res = $buscar_cliente->get_result();

        if ($res->num_rows === 0) {
            echo "<p style='color:red;'>No se encontró un cliente con ese nombre. Verifica que esté escrito exactamente igual.</p>";
        } else {
            $cliente = $res->fetch_assoc();
            $titular = $cliente['id_cliente'];

            // Insertar tarjeta en base banco_acc ($conn2)
            $stmt = $conn->prepare("INSERT INTO tarjeta (numero_tarjeta, cvv, fecha_vencimiento, tipo_tarjeta, red_pago, titular) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $numero_tarjeta, $cvv, $fecha_vencimiento, $tipo_tarjeta, $red_pago, $titular); // corregido bind_param

            if ($stmt->execute()) {
                
            } else {
                
            }
        }
    }
}



// Obtener tarjetas de base banco_acc
$tarjetas = $conn->query("
    SELECT 
        t.id_tarjeta, 
        t.numero_tarjeta, 
        t.cvv, 
        t.fecha_vencimiento, 
        t.tipo_tarjeta, 
        t.red_pago, 
        t.titular
    FROM tarjeta t
");

// Obtener clientes para mostrar nombres (base principal)
$clientes_result = $conn->query("SELECT id_cliente, CONCAT(nom_persona, ' ', apellido_paterno, ' ', apellido_materno) AS nombre FROM cliente");
$clientes_array = [];
while ($row = $clientes_result->fetch_assoc()) {
    $clientes_array[$row['id_cliente']] = $row['nombre'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

<?php include('../Nav/header.php'); ?>

<title>Registrar Tarjeta</title>
<!-- Incluir CSS externo -->

</head>
<body>

<br></br>
<h2>Registrar nueva tarjeta</h2>
<div class="form-container">
<form method="POST" action="">
    <label>Número de tarjeta (16 dígitos):</label>
    <input type="text" name="numero_tarjeta" maxlength="16" pattern="\d{16}" required>

    <label>CVV (3 dígitos):</label>
    <input type="text" name="cvv" maxlength="3" pattern="\d{3}" required>

    <label>Fecha de vencimiento:</label>
    <input type="month" name="fecha_vencimiento" required>

    <label>Tipo de tarjeta:</label>
    <select name="tipo_tarjeta" required>
        <option value="Debito">Débito</option>
        <option value="Credito">Crédito</option>
    </select>

    <label>Red de pago:</label>
    <select name="red_pago" required>
        <option value="VISA">VISA</option>
        <option value="MASTERCARD">MasterCard</option>
    </select>

    <label>Nombre del titular (exacto como está registrado):</label>
    <input type="text" name="titular" placeholder="Ej. Juan Pérez López" required>

    <button type="submit">Registrar Tarjeta</button>
</form>
</div>

<h2>Tarjetas Registradas</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Número</th>
            <th>CVV</th>
            <th>Fecha Vencimiento</th>
            <th>Tipo</th>
            <th>Red</th>
            <th>Titular</th>
            <th>Acciones</th> <!-- Nueva columna -->
        </tr>
    </thead>
    <tbody>
        <?php
        if ($tarjetas->num_rows > 0) {
            while ($t = $tarjetas->fetch_assoc()) {
                $nombre_titular = $clientes_array[$t['titular']] ?? 'Desconocido';  // corregido variable
                echo "<tr>
                    <td>{$t['id_tarjeta']}</td>
                    <td>{$t['numero_tarjeta']}</td>
                    <td>{$t['cvv']}</td>
                    <td>" . date('Y-m', strtotime($t['fecha_vencimiento'])) . "</td>
                    <td>{$t['tipo_tarjeta']}</td>
                    <td>{$t['red_pago']}</td>
                    <td>{$nombre_titular}</td>
                    <td><a href='?eliminar_id={$t['id_tarjeta']}' onclick='return confirm(\"¿Seguro que quieres eliminar esta tarjeta?\");'>Eliminar</a></td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='8'>No hay tarjetas registradas.</td></tr>";
        }
        ?>
    </tbody>
</table>

</body>
<?php include ('../Nav/footer.php'); ?>

<style>
/* estilos_tarjeta.css */

.form-container {
  max-width: 500px;
  margin: 20px auto 40px auto;
  padding: 20px 30px;
  background-color: #f9f9f9;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  font-family: Arial, sans-serif;
}
.form-container h2 {
  text-align: center;
  margin-bottom: 25px;
  color: #333;
}
.form-container label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  color: #444;
}
.form-container input[type="text"],
.form-container input[type="month"],
.form-container input[type="number"],
.form-container select {
  width: 100%;
  padding: 10px 12px;
  margin-bottom: 18px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
  transition: border-color 0.3s;
}
.form-container input[type="text"]:focus,
.form-container input[type="month"]:focus,
.form-container input[type="number"]:focus,
.form-container select:focus {
  border-color: #007bff;
  outline: none;
}
.form-container button {
  width: 100%;
  padding: 12px 0;
  background-color: #007bff;
  border: none;
  color: white;
  font-size: 1.1rem;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.3s;
}
.form-container button:hover {
  background-color: #0056b3;
}
/* Mensajes */
.form-container p {
  text-align: center;
  font-weight: 600;
}

/* Tabla */
table {
  width: 95%;
  margin: 0 auto 40px auto;
  border-collapse: collapse;
  font-family: Arial, sans-serif;
}
table thead {
  background-color: #007bff;
  color: white;
}
table th, table td {
  padding: 8px 10px;
  border: 1px solid #ddd;
  text-align: center;
  font-size: 0.9rem;
}
table tbody tr:nth-child(even) {
  background-color: #f2f2f2;
}
table a {
  color: #d9534f;
  text-decoration: none;
  font-weight: bold;
}
table a:hover {
  text-decoration: underline;
}

/* Responsive tabla */
@media (max-width: 600px) {
  table, thead, tbody, th, td, tr {
    display: block;
  }
  table thead tr {
    display: none;
  }
  table tbody tr {
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 10px;
  }
  table tbody td {
    border: none;
    padding: 6px 10px;
    text-align: right;
    position: relative;
    padding-left: 50%;
    font-size: 0.9rem;
  }
  table tbody td::before {
    content: attr(data-label);
    position: absolute;
    left: 15px;
    width: 45%;
    padding-left: 5px;
    font-weight: 700;
    text-align: left;
    color: #333;
  }
}

</style>
</html>
