<?php
include '../BD/ConexionBanco.php';  // $conn2 -> Base banco_acc (tarjeta)
include '../BD/ConexionBD.php';     // $conn  -> Base principal (cliente)
include('../Nav/header.php');

// Procesar eliminación si se recibe eliminar_id
if (isset($_GET['eliminar_id'])) {
    $eliminar_id = intval($_GET['eliminar_id']);
    $stmtEliminar = $conn2->prepare("DELETE FROM tarjeta WHERE id_tarjeta = ?");
    $stmtEliminar->bind_param("i", $eliminar_id);
    if ($stmtEliminar->execute()) {
        echo "<p style='color:green;'>Tarjeta eliminada correctamente.</p>";
    } else {
        echo "<p style='color:red;'>Error al eliminar la tarjeta: " . $stmtEliminar->error . "</p>";
    }
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $numero_tarjeta = $_POST['numero_tarjeta'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $fecha_vencimiento = ($_POST['fecha_vencimiento'] ?? '') . "-01 00:00:00";
    $saldo = $_POST['saldo'] ?? 0;
    $tipo_tarjeta = $_POST['tipo_tarjeta'] ?? '';
    $red_pago = $_POST['red_pago'] ?? '';
    $nombre_titular = isset($_POST['nombre_titular']) ? trim($_POST['nombre_titular']) : '';
    $id_banco = $_POST['id_banco'] ?? 0;

    if (empty($nombre_titular)) {
        echo "<p style='color:red;'>El nombre del titular es obligatorio.</p>";
    } else {
        // Buscar id_cliente correspondiente al nombre completo en la base principal ($conn)
        $buscar_cliente = $conn->prepare("SELECT id_cliente FROM cliente WHERE CONCAT(nom_persona, ' ', apellido_paterno, ' ', apellido_materno) = ?");
        $buscar_cliente->bind_param("s", $nombre_titular);
        $buscar_cliente->execute();
        $res = $buscar_cliente->get_result();

        if ($res->num_rows === 0) {
            echo "<p style='color:red;'>No se encontró un cliente con ese nombre. Verifica que esté escrito exactamente igual.</p>";
        } else {
            $cliente = $res->fetch_assoc();
            $titular = $cliente['id_cliente'];

            // Insertar tarjeta en base banco_acc ($conn2)
            $stmt = $conn2->prepare("INSERT INTO tarjeta (numero_tarjeta, cvv, fecha_vencimiento, saldo, tipo_tarjeta, red_pago, titular, id_banco) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdssii", $numero_tarjeta, $cvv, $fecha_vencimiento, $saldo, $tipo_tarjeta, $red_pago, $titular, $id_banco);

            if ($stmt->execute()) {
                echo "<p style='color:green;'>Tarjeta registrada correctamente.</p>";
            } else {
                echo "<p style='color:red;'>Error al registrar la tarjeta: " . $stmt->error . "</p>";
            }
        }
    }
}

// Obtener bancos de base banco_acc
$bancos = $conn2->query("SELECT id_banco, nombre_banco FROM banco");

// Obtener tarjetas de base banco_acc
$tarjetas = $conn2->query("
    SELECT 
        t.id_tarjeta, 
        t.numero_tarjeta, 
        t.cvv, 
        t.fecha_vencimiento, 
        t.saldo, 
        t.tipo_tarjeta, 
        t.red_pago, 
        t.titular,
        b.nombre_banco
    FROM tarjeta t
    JOIN banco b ON t.id_banco = b.id_banco
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
<title>Registrar Tarjeta</title>
<!-- Incluir CSS externo -->
<link rel="stylesheet" href="Tarjetas.css" />
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

    <label>Saldo:</label>
    <input type="number" name="saldo" step="0.01" min="0" required>

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
    <input type="text" name="nombre_titular" placeholder="Ej. Juan Pérez López" required>

    <label>Banco:</label>
    <select name="id_banco" required>
        <option value="">-- Selecciona un banco --</option>
        <?php
        if ($bancos->num_rows > 0) {
            while ($banco = $bancos->fetch_assoc()) {
                echo "<option value='{$banco['id_banco']}'>{$banco['nombre_banco']}</option>";
            }
        } else {
            echo "<option value=''>No hay bancos disponibles</option>";
        }
        ?>
    </select>

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
            <th>Saldo</th>
            <th>Tipo</th>
            <th>Red</th>
            <th>Titular</th>
            <th>Banco</th>
            <th>Acciones</th> <!-- Nueva columna -->
        </tr>
    </thead>
    <tbody>
        <?php
        if ($tarjetas->num_rows > 0) {
            while ($t = $tarjetas->fetch_assoc()) {
                $nombre_titular = $clientes_array[$t['titular']] ?? 'Desconocido';
                echo "<tr>
                    <td>{$t['id_tarjeta']}</td>
                    <td>{$t['numero_tarjeta']}</td>
                    <td>{$t['cvv']}</td>
                    <td>" . date('Y-m', strtotime($t['fecha_vencimiento'])) . "</td>
                    <td>{$t['saldo']}</td>
                    <td>{$t['tipo_tarjeta']}</td>
                    <td>{$t['red_pago']}</td>
                    <td>{$nombre_titular}</td>
                    <td>{$t['nombre_banco']}</td>
                    <td><a href='?eliminar_id={$t['id_tarjeta']}' onclick='return confirm(\"¿Seguro que quieres eliminar esta tarjeta?\");'>Eliminar</a></td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='10'>No hay tarjetas registradas.</td></tr>";
        }
        ?>
    </tbody>
</table>

</body>
</html>
