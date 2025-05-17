<?php
session_start();

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: Pagos.php?resultado=Método no permitido&tipo=error");
    exit();
}

// Verificar que existan los datos necesarios
if (!isset($_POST['metodo_pago']) || !isset($_POST['monto']) || !isset($_POST['id_cliente']) || !isset($_POST['id_pedido'])) {
    header("Location: Pagos.php?resultado=Faltan datos del pago&tipo=error");
    exit();
}

$metodo_pago = $_POST['metodo_pago'];
$monto = floatval($_POST['monto']);
$id_cliente = intval($_POST['id_cliente']);
$id_pedido = intval($_POST['id_pedido']);

// Validar datos
if ($monto <= 0) {
    header("Location: Pagos.php?resultado=Monto inválido&tipo=error&monto=$monto&id_pedido=$id_pedido");
    exit();
}

if ($id_cliente <= 0) {
    header("Location: Pagos.php?resultado=Cliente inválido&tipo=error&monto=$monto&id_pedido=$id_pedido");
    exit();
}

if ($id_pedido <= 0) {
    header("Location: Pagos.php?resultado=Pedido inválido&tipo=error&monto=$monto&id_pedido=$id_pedido");
    exit();
}

// Agregar costo de envío
$monto_total = $monto + 50.00;

// Incluir conexiones
try {
    include('../BD/ConexionBD.php');  // Base de datos principal
    include('../BD/ConexionBDB.php'); // Base de datos del banco
} catch (Exception $e) {
    header("Location: Pagos.php?resultado=Error de conexión: " . urlencode($e->getMessage()) . "&tipo=error&monto=$monto&id_pedido=$id_pedido");
    exit();
}

// Verificar conexiones
if (!$conn || $conn->connect_error) {
    header("Location: Pagos.php?resultado=Error de conexión principal&tipo=error&monto=$monto&id_pedido=$id_pedido");
    exit();
}

if (!$conn2 || $conn2->connect_error) {
    header("Location: Pagos.php?resultado=Error de conexión banco&tipo=error&monto=$monto&id_pedido=$id_pedido");
    exit();
}

try {
    // Iniciar transacción en ambas conexiones
    $conn->autocommit(FALSE);
    $conn2->autocommit(FALSE);

    if ($metodo_pago === 'sucursal') {
        // PAGO EN SUCURSAL
        
        // Generar folio único
        $folio = 'SUC' . date('YmdHis') . rand(100, 999);
        
        // Insertar forma de pago
        $query_forma_pago = "INSERT INTO formas_pago (forma, folio, estado) VALUES ('Sucursal', ?, 'Activo')";
        $stmt_forma_pago = $conn->prepare($query_forma_pago);
        $stmt_forma_pago->bind_param('s', $folio);
        
        if (!$stmt_forma_pago->execute()) {
            throw new Exception("Error insertando forma de pago: " . $stmt_forma_pago->error);
        }
        
        $id_forma_pago = $conn->insert_id;
        $stmt_forma_pago->close();
        
        // Crear registro de pago
        $query_pago = "INSERT INTO pago (id_forma_pago, id_pedido, fecha_pago) VALUES (?, ?, NOW())";
        $stmt_pago = $conn->prepare($query_pago);
        $stmt_pago->bind_param('ii', $id_forma_pago, $id_pedido);
        
        if (!$stmt_pago->execute()) {
            throw new Exception("Error insertando pago: " . $stmt_pago->error);
        }
        
        $stmt_pago->close();
        
        // Agregar 5% al monedero del cliente
        $bonus_monedero = $monto_total * 0.05;
        $query_monedero = "UPDATE cliente SET monedero = monedero + ? WHERE id_cliente = ?";
        $stmt_monedero = $conn->prepare($query_monedero);
        $stmt_monedero->bind_param('di', $bonus_monedero, $id_cliente);
        
        if (!$stmt_monedero->execute()) {
            throw new Exception("Error actualizando monedero: " . $stmt_monedero->error);
        }
        
        $stmt_monedero->close();
        
        // Confirmar transacciones
        $conn->commit();
        
        // Redireccionar con éxito
        header("Location: ../pago/confirmacion.php?folio=$folio&tipo=sucursal&bonus=" . number_format($bonus_monedero, 2));
        exit();
        
    } elseif ($metodo_pago === 'tarjeta') {
        // PAGO CON TARJETA
        
        if (!isset($_POST['id_tarjeta'])) {
            throw new Exception("ID de tarjeta no proporcionado");
        }
        
        $id_tarjeta = intval($_POST['id_tarjeta']);
        
        if ($id_tarjeta <= 0) {
            throw new Exception("ID de tarjeta inválido");
        }
        
        // 1. Obtener información de la tarjeta de la BD principal
        $query_tarjeta_info = "SELECT numero_tarjeta, cvv, tipo_tarjeta, red_pago FROM tarjeta WHERE id_tarjeta = ? AND titular = ?";
        $stmt_tarjeta_info = $conn->prepare($query_tarjeta_info);
        $stmt_tarjeta_info->bind_param('ii', $id_tarjeta, $id_cliente);
        $stmt_tarjeta_info->execute();
        $result_tarjeta = $stmt_tarjeta_info->get_result();
        
        if ($result_tarjeta->num_rows === 0) {
            throw new Exception("Tarjeta no encontrada o no pertenece al cliente");
        }
        
        $tarjeta_info = $result_tarjeta->fetch_assoc();
        $stmt_tarjeta_info->close();
        
        // 2. Buscar la tarjeta en la BD del banco
        $query_banco_tarjeta = "SELECT id_tarjeta, saldo FROM tarjeta WHERE numero_tarjeta = ? AND cvv = ?";
        $stmt_banco_tarjeta = $conn2->prepare($query_banco_tarjeta);
        $stmt_banco_tarjeta->bind_param('ss', $tarjeta_info['numero_tarjeta'], $tarjeta_info['cvv']);
        $stmt_banco_tarjeta->execute();
        $result_banco = $stmt_banco_tarjeta->get_result();
        
        if ($result_banco->num_rows === 0) {
            throw new Exception("Tarjeta no válida en el sistema bancario");
        }
        
        $tarjeta_banco = $result_banco->fetch_assoc();
        $stmt_banco_tarjeta->close();
        
        // 3. Verificar saldo suficiente
        if ($tarjeta_banco['saldo'] < $monto_total) {
            throw new Exception("Saldo insuficiente. Saldo actual: $" . number_format($tarjeta_banco['saldo'], 2) . ", Requerido: $" . number_format($monto_total, 2));
        }
        
        // 4. Descontar el monto del saldo en la BD del banco
        $query_descuento = "UPDATE tarjeta SET saldo = saldo - ? WHERE id_tarjeta = ?";
        $stmt_descuento = $conn2->prepare($query_descuento);
        $stmt_descuento->bind_param('di', $monto_total, $tarjeta_banco['id_tarjeta']);
        
        if (!$stmt_descuento->execute()) {
            throw new Exception("Error al descontar saldo: " . $stmt_descuento->error);
        }
        
        $stmt_descuento->close();
        
        // 5. Insertar forma de pago en BD principal
        $query_forma_pago = "INSERT INTO formas_pago (forma, estado) VALUES ('Tarjeta', 'Usado')";
        $stmt_forma_pago = $conn->prepare($query_forma_pago);
        
        if (!$stmt_forma_pago->execute()) {
            throw new Exception("Error insertando forma de pago: " . $stmt_forma_pago->error);
        }
        
        $id_forma_pago = $conn->insert_id;
        $stmt_forma_pago->close();
        
        // 6. Crear registro de pago
        $query_pago = "INSERT INTO pago (id_forma_pago, id_pedido, fecha_pago) VALUES (?, ?, NOW())";
        $stmt_pago = $conn->prepare($query_pago);
        $stmt_pago->bind_param('ii', $id_forma_pago, $id_pedido);
        
        if (!$stmt_pago->execute()) {
            throw new Exception("Error insertando pago: " . $stmt_pago->error);
        }
        
        $stmt_pago->close();
        
        // 7. Agregar 5% al monedero del cliente
        $bonus_monedero = $monto_total * 0.05;
        $query_monedero = "UPDATE cliente SET monedero = monedero + ? WHERE id_cliente = ?";
        $stmt_monedero = $conn->prepare($query_monedero);
        $stmt_monedero->bind_param('di', $bonus_monedero, $id_cliente);
        
        if (!$stmt_monedero->execute()) {
            throw new Exception("Error actualizando monedero: " . $stmt_monedero->error);
        }
        
        $stmt_monedero->close();
        
        // Confirmar transacciones en ambas BDs
        $conn->commit();
        $conn2->commit();
        
        // Redireccionar con éxito
        $numero_oculto = "**** **** **** " . substr($tarjeta_info['numero_tarjeta'], -4);
        header("Location: ../pago/confirmacion.php?tipo=tarjeta&tarjeta=" . urlencode($numero_oculto) . "&bonus=" . number_format($bonus_monedero, 2) . "&id_pedido=$id_pedido");
        exit();
        
    } else {
        throw new Exception("Método de pago no válido");
    }
    
} catch (Exception $e) {
    // Rollback en caso de error
    $conn->rollback();
    $conn2->rollback();
    
    // Redireccionar con error
    header("Location: Pagos.php?resultado=" . urlencode("Error: " . $e->getMessage()) . "&tipo=error&monto=$monto&id_pedido=$id_pedido");
    exit();
    
} finally {
    // Restaurar autocommit y cerrar conexiones
    if ($conn) {
        $conn->autocommit(TRUE);
        $conn->close();
    }
    if ($conn2) {
        $conn2->autocommit(TRUE);
        $conn2->close();
    }
}
?>