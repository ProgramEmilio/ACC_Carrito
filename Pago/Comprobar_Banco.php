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
$articulos = $_POST['articulos'] ?? [];
$detalles = $_POST['detalles'] ?? [];

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
            
            // Procesar actualización de inventario y creación de reportes
            procesarDetallesCompra($conn, $detalles, $id_cliente, $id_pedido);
            
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
        
        // Procesar actualización de inventario y creación de reportes
        
        
        // Confirmar transacciones
        $conn->commit();
        
        // Redireccionar con éxito
        //header("Location: ../pago/confirmacion.php?folio=$folio&tipo=sucursal&bonus=" . number_format($bonus_monedero, 2) . "&monedero_usado=" . number_format($monedero_usado, 2) . "&id_pedido=$id_pedido");
        
        // Usar setTimeout para esperar a que el DOM esté listo
        
        
        ?>
        <form id="formBC" action="../Pago/Confirmacion.php" method="post">
            <div id="inputsOcultos">
                <?php foreach ($articulos as $index => $id): ?>
                    <input type="hidden" name="articulos[]" value="<?= htmlspecialchars($id) ?>">
                    <input type="hidden" name="detalles[<?= $id ?>]" value="<?= htmlspecialchars($detalles[$id]) ?>">
                <?php endforeach; ?>
                <input type="hidden" name="id_pedido" value="<?= htmlspecialchars($id_pedido) ?>">
                <input type="hidden" name="precio_total_pedido" value="<?= htmlspecialchars($total) ?>">
                <input type="hidden" name="tipo" value="sucursal">
                <input type="hidden" name="bonus" value="<?= htmlspecialchars(number_format($bonus_monedero, 2)) ?>">
                <input type="hidden" name="monedero_usado" value="<?= htmlspecialchars(number_format($monedero_usado, 2)) ?>">
                <input type="hidden" name="folio" value="<?= htmlspecialchars($folio) ?>">
                <input type="hidden" name="id_forma" value="<?= htmlspecialchars($id_forma_pago) ?>">
                <input type="hidden" name="mon" value="<?= htmlspecialchars($monto) ?>">
            </div>
        </form>
        <script>
            document.getElementById('formBC').submit(); // También se envía automáticamente
        </script>

    <?php
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
        
        // Procesar actualización de inventario y creación de reportes
        procesarDetallesCompra($conn, $detalles, $id_cliente, $id_pedido);
        
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

/**
 * Procesa los detalles de la compra: actualiza el inventario, elimina registros del carrito y crea reportes
 * 
 * @param mysqli $conn Conexión a la base de datos
 * @param array $detalles Array de detalles de artículos [id_detalle_articulo => id_detalle_carrito]
 * @param int $id_cliente ID del cliente
 * @param int $id_pedido ID del pedido
 * @throws Exception Si ocurre algún error durante el procesamiento
 */
function procesarDetallesCompra($conn, $detalles, $id_cliente, $id_pedido) {
    if (empty($detalles)) {
        return; // No hay detalles que procesar
    }
    
    // Generar ID único para el seguimiento de pedido
    $id_seguimiento_pedido = 'SEG' . date('YmdHis') . rand(100, 999);
    
    // Crear registro en seguimiento_pedido
    $query_seguimiento = "INSERT INTO seguimiento_pedido (id_seguimiento_pedido, id_pedido, id_cliente, Estado) 
                         VALUES (?, ?, ?, 'Enviado')";
    $stmt_seguimiento = $conn->prepare($query_seguimiento);
    $stmt_seguimiento->bind_param('sii', $id_seguimiento_pedido, $id_pedido, $id_cliente);
    
    if (!$stmt_seguimiento->execute()) {
        throw new Exception("Error insertando seguimiento de pedido: " . $stmt_seguimiento->error);
    }
    $stmt_seguimiento->close();
    
    // Obtener información del pedido para el reporte
    $query_pedido = "SELECT p.iva, p.ieps, p.precio_total_pedido, p.fecha_pedido, p.id_carrito, p.id_envio 
                    FROM pedido p WHERE p.id_pedido = ?";
    $stmt_pedido = $conn->prepare($query_pedido);
    $stmt_pedido->bind_param('i', $id_pedido);
    $stmt_pedido->execute();
    $result_pedido = $stmt_pedido->get_result();
    
    if ($result_pedido->num_rows === 0) {
        throw new Exception("No se encontró el pedido con ID: $id_pedido");
    }
    
    $pedido_info = $result_pedido->fetch_assoc();
    $stmt_pedido->close();
    
    // Obtener información del tipo de envío
    $query_envio = "SELECT tipo_envio FROM envio WHERE id_envio = ?";
    $stmt_envio = $conn->prepare($query_envio);
    $stmt_envio->bind_param('i', $pedido_info['id_envio']);
    $stmt_envio->execute();
    $stmt_envio->bind_result($tipo_envio);
    $stmt_envio->fetch();
    $stmt_envio->close();
    
    // Obtener información del cliente
    $query_cliente = "SELECT nom_persona, apellido_paterno, apellido_materno FROM cliente WHERE id_cliente = ?";
    $stmt_cliente = $conn->prepare($query_cliente);
    $stmt_cliente->bind_param('i', $id_cliente);
    $stmt_cliente->execute();
    $result_cliente = $stmt_cliente->get_result();
    
    if ($result_cliente->num_rows === 0) {
        throw new Exception("No se encontró el cliente con ID: $id_cliente");
    }
    
    $cliente_info = $result_cliente->fetch_assoc();
    $stmt_cliente->close();
    
    // Procesar cada detalle de artículo
foreach ($detalles as $id_articulo => $id_detalle_carrito) {
    // 1. Obtener información del detalle del carrito
    $query_detalle_carrito = "SELECT id_articulo, cantidad, precio, importe, personalizacion 
                              FROM detalle_carrito 
                              WHERE id_detalle_carrito = ?";
    $stmt_detalle_carrito = $conn->prepare($query_detalle_carrito);
    $stmt_detalle_carrito->bind_param('i', $id_detalle_carrito);
    $stmt_detalle_carrito->execute();
    $result_detalle_carrito = $stmt_detalle_carrito->get_result();

    if ($result_detalle_carrito->num_rows === 0) {
        throw new Exception("No se encontró el detalle del carrito con ID: $id_detalle_carrito");
    }

    $detalle_carrito = $result_detalle_carrito->fetch_assoc();
    $stmt_detalle_carrito->close();

    // 2. Obtener información del artículo (incluye id_detalle_articulo)
    $query_articulo = "SELECT descripcion, id_detalle_articulo 
                       FROM articulos 
                       WHERE id_articulo = ?";
    $stmt_articulo = $conn->prepare($query_articulo);
    $stmt_articulo->bind_param('s', $detalle_carrito['id_articulo']);
    $stmt_articulo->execute();
    $stmt_articulo->bind_result($descripcion_articulo, $id_detalle_articulo);
    $stmt_articulo->fetch();
    $stmt_articulo->close();

    // 3. Actualizar inventario en detalle_articulos
    $query_actualizar_existencias = "UPDATE detalle_articulos 
                                     SET existencia = existencia - ? 
                                     WHERE id_detalle_articulo = ?";
    $stmt_actualizar = $conn->prepare($query_actualizar_existencias);
    $stmt_actualizar->bind_param('di', $detalle_carrito['cantidad'], $id_detalle_articulo);

    if (!$stmt_actualizar->execute()) {
        throw new Exception("Error actualizando inventario: " . $stmt_actualizar->error);
    }
    $stmt_actualizar->close();

    // 4. Insertar en tabla_reporte
    $query_reporte = "INSERT INTO tabla_reporte (
        id_seguimiento_pedido, nom_persona, apellido_paterno, apellido_materno, 
        id_envio, tipo_envio, id_articulo, descripcion, cantidad, precio, 
        importe, personalizacion, id_pedido, iva, ieps, precio_total_pedido, fecha_pedido
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )";

    $stmt_reporte = $conn->prepare($query_reporte);
    $stmt_reporte->bind_param(
        'ssssisssdddsiidds',
        $id_seguimiento_pedido,
        $cliente_info['nom_persona'],
        $cliente_info['apellido_paterno'],
        $cliente_info['apellido_materno'],
        $pedido_info['id_envio'],
        $tipo_envio,
        $detalle_carrito['id_articulo'],
        $descripcion_articulo,
        $detalle_carrito['cantidad'],
        $detalle_carrito['precio'],
        $detalle_carrito['importe'],
        $detalle_carrito['personalizacion'],
        $id_pedido,
        $pedido_info['iva'],
        $pedido_info['ieps'],
        $pedido_info['precio_total_pedido'],
        $pedido_info['fecha_pedido']
    );

    if (!$stmt_reporte->execute()) {
        throw new Exception("Error insertando en tabla_reporte: " . $stmt_reporte->error);
    }
    $stmt_reporte->close();

    // 5. Eliminar registro del detalle_carrito
    $query_eliminar = "DELETE FROM detalle_carrito WHERE id_detalle_carrito = ?";
    $stmt_eliminar = $conn->prepare($query_eliminar);
    $stmt_eliminar->bind_param('i', $id_detalle_carrito);

    if (!$stmt_eliminar->execute()) {
        throw new Exception("Error eliminando detalle del carrito: " . $stmt_eliminar->error);
    }
    $stmt_eliminar->close();
}

}

?>

<form id="formBC" action="../Pago/Confirmacion.php" method="post">
    <div id="inputsOcultos">
        <?php foreach ($articulos as $index => $id): ?>
            <input type="hidden" name="articulos[]" value="<?= htmlspecialchars($id) ?>">
            <input type="hidden" name="detalles[<?= $id ?>]" value="<?= htmlspecialchars($detalles[$id]) ?>">
        <?php endforeach; ?>
        <input type="hidden" name="id_pedido" value="<?= htmlspecialchars($id_pedido) ?>">
        <input type="hidden" name="precio_total_pedido" value="<?= htmlspecialchars($total) ?>">
        <input type="hidden" name="tipo" value="sucursal">
        <input type="hidden" name="bonus" value="<?= htmlspecialchars(number_format($bonus_monedero, 2)) ?>">
        <input type="hidden" name="monedero_usado" value="<?= htmlspecialchars(number_format($monedero_usado, 2)) ?>">
        <input type="hidden" name="folio" value="<?= htmlspecialchars($folio) ?>">
    </div>
</form>
<script>
    document.getElementById('formBC').submit(); // También se envía automáticamente
</script>
