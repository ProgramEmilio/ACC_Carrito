<?php
session_start();

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: Pagos.php?resultado=Método no permitido&tipo=error");
    exit();
}

// Verificar que existan los datos necesarios
if (!isset($_POST['id_cliente']) || !isset($_POST['id_pedido'])) {
    header("Location: Pagos.php?resultado=Faltan datos del pago&tipo=error");
    exit();
}

// Obtener y validar datos básicos
$id_cliente = intval($_POST['id_cliente']);
$id_pedido = intval($_POST['id_pedido']);
$monto = isset($_POST['monto']) ? floatval($_POST['monto']) : 0;

// Validar datos
if ($monto <= 0) {
    header("Location: Pagos.php?resultado=Monto inválido&tipo=error&id_pedido=$id_pedido");
    exit();
}

if ($id_cliente <= 0) {
    header("Location: Pagos.php?resultado=Cliente inválido&tipo=error&id_pedido=$id_pedido");
    exit();
}

if ($id_pedido <= 0) {
    header("Location: Pagos.php?resultado=Pedido inválido&tipo=error");
    exit();
}

// Verificar si se está usando el monedero
$usar_monedero = isset($_POST['usar_monedero']) && $_POST['usar_monedero'] == 'on';
$monedero_usado = isset($_POST['monedero_usado']) ? floatval($_POST['monedero_usado']) : 0;

// Verificar método de pago seleccionado (puede ser null si solo usa monedero para pago completo)
$metodo_pago = isset($_POST['metodo_pago']) ? $_POST['metodo_pago'] : null;

// Incluir conexiones
try {
    include('../BD/ConexionBD.php');  // Base de datos principal
    include('../BD/ConexionBDB.php'); // Base de datos del banco
} catch (Exception $e) {
    header("Location: Pagos.php?resultado=Error de conexión: " . urlencode($e->getMessage()) . "&tipo=error&id_pedido=$id_pedido");
    exit();
}

// Verificar conexiones
if (!$conn || $conn->connect_error) {
    header("Location: Pagos.php?resultado=Error de conexión principal&tipo=error&id_pedido=$id_pedido");
    exit();
}

if (!$conn2 || $conn2->connect_error) {
    header("Location: Pagos.php?resultado=Error de conexión banco&tipo=error&id_pedido=$id_pedido");
    exit();
}

try {
    // Iniciar transacción en ambas conexiones
    $conn->autocommit(FALSE);
    $conn2->autocommit(FALSE);
    
    // Si usa monedero, validar el saldo disponible
    if ($usar_monedero && $monedero_usado > 0) {
        // Verificar saldo disponible en monedero
        $query_verificar_monedero = "SELECT monedero FROM cliente WHERE id_cliente = ?";
        $stmt_verificar = $conn->prepare($query_verificar_monedero);
        $stmt_verificar->bind_param('i', $id_cliente);
        $stmt_verificar->execute();
        $stmt_verificar->bind_result($monedero_disponible);
        $stmt_verificar->fetch();
        $stmt_verificar->close();
        
        if ($monedero_usado > $monedero_disponible) {
            throw new Exception("El monto a usar del monedero ($monedero_usado) excede el saldo disponible ($monedero_disponible)");
        }
        
        // Descontar del monedero
        $query_update_monedero = "UPDATE cliente SET monedero = monedero - ? WHERE id_cliente = ?";
        $stmt_update = $conn->prepare($query_update_monedero);
        $stmt_update->bind_param('di', $monedero_usado, $id_cliente);
        
        if (!$stmt_update->execute()) {
            throw new Exception("Error al actualizar el monedero: " . $stmt_update->error);
        }
        $stmt_update->close();
        
        // Registrar uso del monedero como forma de pago si cubre todo el monto
        if ($monedero_usado >= $monto) {
            // El monedero cubre todo el pago
            $query_forma_pago = "INSERT INTO formas_pago (forma, estado) VALUES ('Monedero', 'Usado')";
            $stmt_forma_pago = $conn->prepare($query_forma_pago);
            
            if (!$stmt_forma_pago->execute()) {
                throw new Exception("Error insertando forma de pago monedero: " . $stmt_forma_pago->error);
            }
            
            $id_forma_pago = $conn->insert_id;
            $stmt_forma_pago->close();
            
            // Generar ID único para el pago
            $id_pago = 'PAY' . date('YmdHis') . rand(100, 999);
            
            // Crear registro de pago
            $query_pago = "INSERT INTO pago (id_pago, id_forma_pago, id_pedido, monto, fecha_pago) VALUES (?, ?, ?, ?, NOW())";
            $stmt_pago = $conn->prepare($query_pago);
            $stmt_pago->bind_param('siid', $id_pago, $id_forma_pago, $id_pedido, $monedero_usado);
            
            if (!$stmt_pago->execute()) {
                throw new Exception("Error insertando pago con monedero: " . $stmt_pago->error);
            }
            $stmt_pago->close();
            
            // Si el pago es completo con monedero, finalizamos aquí
            $conn->commit();
            
            // Redireccionar con éxito
            header("Location: ../pago/confirmacion.php?tipo=monedero&monto=" . number_format($monedero_usado, 2) . "&id_pedido=$id_pedido");
            exit();
        }
        
        // Si llegamos aquí, el monedero no cubre todo el monto y debemos continuar con otro método de pago
        // Ajustamos el monto para el resto del procesamiento
        $monto = $monto - $monedero_usado;
    }
    
    // Procesamiento según método de pago
    if ($metodo_pago === 'sucursal') {
        // PAGO EN SUCURSAL
        
        // Generar folio único
        $folio = 'SUC' . date('YmdHis') . rand(100, 999);
        
        // Insertar forma de pago
        $query_forma_pago = "INSERT INTO formas_pago (forma, folio, estado) VALUES ('Sucursal', ?, 'Pendiente')";
        $stmt_forma_pago = $conn->prepare($query_forma_pago);
        $stmt_forma_pago->bind_param('s', $folio);
        
        if (!$stmt_forma_pago->execute()) {
            throw new Exception("Error insertando forma de pago: " . $stmt_forma_pago->error);
        }
        
        $id_forma_pago = $conn->insert_id;
        $stmt_forma_pago->close();
        
        // Generar ID único para el pago
        $id_pago = 'SUC' . date('YmdHis') . rand(100, 999);
        
        // Crear registro de pago
        $query_pago = "INSERT INTO pago (id_pago, id_forma_pago, id_pedido, monto, fecha_pago) VALUES (?, ?, ?, ?, NOW())";
        $stmt_pago = $conn->prepare($query_pago);
        $stmt_pago->bind_param('siid', $id_pago, $id_forma_pago, $id_pedido, $monto);
        
        if (!$stmt_pago->execute()) {
            throw new Exception("Error insertando pago: " . $stmt_pago->error);
        }
        
        $stmt_pago->close();
        
        // Si se usó parcialmente el monedero, registrarlo como pago adicional
        if ($usar_monedero && $monedero_usado > 0) {
            $query_forma_monedero = "INSERT INTO formas_pago (forma, estado) VALUES ('Monedero', 'Usado')";
            $stmt_forma_monedero = $conn->prepare($query_forma_monedero);
            
            if (!$stmt_forma_monedero->execute()) {
                throw new Exception("Error insertando forma de pago monedero: " . $stmt_forma_monedero->error);
            }
            
            $id_forma_monedero = $conn->insert_id;
            $stmt_forma_monedero->close();
            
            // Generar ID único para el pago con monedero
            $id_pago_monedero = 'MON' . date('YmdHis') . rand(100, 999);
            
            // Crear registro de pago
            $query_pago_monedero = "INSERT INTO pago (id_pago, id_forma_pago, id_pedido, monto, fecha_pago) VALUES (?, ?, ?, ?, NOW())";
            $stmt_pago_monedero = $conn->prepare($query_pago_monedero);
            $stmt_pago_monedero->bind_param('siid', $id_pago_monedero, $id_forma_monedero, $id_pedido, $monedero_usado);
            
            if (!$stmt_pago_monedero->execute()) {
                throw new Exception("Error insertando pago con monedero: " . $stmt_pago_monedero->error);
            }
            $stmt_pago_monedero->close();
        }
        
        // Agregar 5% al monedero del cliente como bonus
        $bonus_monedero = $monto * 0.05;
        $query_bonus = "UPDATE cliente SET monedero = monedero + ? WHERE id_cliente = ?";
        $stmt_bonus = $conn->prepare($query_bonus);
        $stmt_bonus->bind_param('di', $bonus_monedero, $id_cliente);
        
        if (!$stmt_bonus->execute()) {
            throw new Exception("Error actualizando bonus de monedero: " . $stmt_bonus->error);
        }
        
        $stmt_bonus->close();
        
        // Confirmar transacciones
        $conn->commit();
        
        // Redireccionar con éxito
        header("Location: ../pago/confirmacion.php?folio=$folio&tipo=sucursal&bonus=" . number_format($bonus_monedero, 2) . "&monedero_usado=" . number_format($monedero_usado, 2) . "&id_pedido=$id_pedido");
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
        if ($tarjeta_banco['saldo'] < $monto) {
            throw new Exception("Transacción rechazada: Fondos insuficientes");
        }
        
        // 4. Descontar el monto del saldo en la BD del banco
        $query_descuento = "UPDATE tarjeta SET saldo = saldo - ? WHERE id_tarjeta = ?";
        $stmt_descuento = $conn2->prepare($query_descuento);
        $stmt_descuento->bind_param('di', $monto, $tarjeta_banco['id_tarjeta']);
        
        if (!$stmt_descuento->execute()) {
            throw new Exception("Error al descontar saldo: " . $stmt_descuento->error);
        }
        
        $stmt_descuento->close();
        
        // 5. Insertar forma de pago en BD principal para la tarjeta
        $query_forma_pago = "INSERT INTO formas_pago (forma, estado) VALUES ('Tarjeta', 'Usado')";
        $stmt_forma_pago = $conn->prepare($query_forma_pago);
        
        if (!$stmt_forma_pago->execute()) {
            throw new Exception("Error insertando forma de pago: " . $stmt_forma_pago->error);
        }
        
        $id_forma_pago = $conn->insert_id;
        $stmt_forma_pago->close();
        
        // Generar ID único para el pago con tarjeta
        $id_pago = 'TAR' . date('YmdHis') . rand(100, 999);
        
        // 6. Crear registro de pago para la tarjeta
        $query_pago = "INSERT INTO pago (id_pago, id_forma_pago, id_pedido, monto, fecha_pago) VALUES (?, ?, ?, ?, NOW())";
        $stmt_pago = $conn->prepare($query_pago);
        $stmt_pago->bind_param('siid', $id_pago, $id_forma_pago, $id_pedido, $monto);
        
        if (!$stmt_pago->execute()) {
            throw new Exception("Error insertando pago: " . $stmt_pago->error);
        }
        
        $stmt_pago->close();
        
        // Si se usó parcialmente el monedero, registrarlo como pago adicional
        if ($usar_monedero && $monedero_usado > 0) {
            $query_forma_monedero = "INSERT INTO formas_pago (forma, estado) VALUES ('Monedero', 'Usado')";
            $stmt_forma_monedero = $conn->prepare($query_forma_monedero);
            
            if (!$stmt_forma_monedero->execute()) {
                throw new Exception("Error insertando forma de pago monedero: " . $stmt_forma_monedero->error);
            }
            
            $id_forma_monedero = $conn->insert_id;
            $stmt_forma_monedero->close();
            
            // Generar ID único para el pago con monedero
            $id_pago_monedero = 'MON' . date('YmdHis') . rand(100, 999);
            
            // Crear registro de pago para el monedero
            $query_pago_monedero = "INSERT INTO pago (id_pago, id_forma_pago, id_pedido, monto, fecha_pago) VALUES (?, ?, ?, ?, NOW())";
            $stmt_pago_monedero = $conn->prepare($query_pago_monedero);
            $stmt_pago_monedero->bind_param('siid', $id_pago_monedero, $id_forma_monedero, $id_pedido, $monedero_usado);
            
            if (!$stmt_pago_monedero->execute()) {
                throw new Exception("Error insertando pago con monedero: " . $stmt_pago_monedero->error);
            }
            $stmt_pago_monedero->close();
        }
        
        // 7. Agregar 5% al monedero del cliente
        $bonus_monedero = $monto * 0.05;
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
        header("Location: ../pago/confirmacion.php?tipo=tarjeta&tarjeta=" . urlencode($numero_oculto) . "&bonus=" . number_format($bonus_monedero, 2) . "&monedero_usado=" . number_format($monedero_usado, 2) . "&id_pedido=$id_pedido");
        exit();
        
    } else {
        throw new Exception("Método de pago no válido o no seleccionado");
    }
    
} catch (Exception $e) {
    // Rollback en caso de error
    $conn->rollback();
    if (isset($conn2)) {
        $conn2->rollback();
    }
    
    // Redireccionar con error
    header("Location: Pagos.php?resultado=" . urlencode("Error: " . $e->getMessage()) . "&tipo=error&precio_total_pedido=$monto&id_pedido=$id_pedido");
    exit();
    
} finally {
    // Restaurar autocommit y cerrar conexiones
    if (isset($conn)) {
        $conn->autocommit(TRUE);
        $conn->close();
    }
    if (isset($conn2)) {
        $conn2->autocommit(TRUE);
        $conn2->close();
    }
}
?>