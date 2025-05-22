<?php 
include('../BD/ConexionBD.php'); 
include('../Nav/header.php');


$id_usuario = $_SESSION['id_usuario'];

// Obtener parámetros de la confirmación
$folio = $_GET['folio'] ?? ($_POST['folio'] ?? '');
$tipo_pago = $_GET['tipo'] ?? ($_POST['tipo'] ?? '');
$tarjeta = $_GET['tarjeta'] ?? ($_POST['tarjeta'] ?? '');
$bonus = $_GET['bonus'] ?? ($_POST['bonus'] ?? '0.00');
$id_pedido = $_GET['id_pedido'] ?? ($_POST['id_pedido'] ?? '');
$id_forma = $_GET['id_forma'] ?? ($_POST['id_forma'] ?? '');
$mon = $_GET['mon'] ?? ($_POST['mon'] ?? '');
$articulos = $_POST['articulos'] ?? [];
$detalles = $_POST['detalles'] ?? [];



// Obtener información del cliente
$sql = "SELECT id_cliente, nom_persona, apellido_paterno, apellido_materno FROM cliente WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();
$stmt->close();

// Obtener información del pedido si existe
$pedido_info = null;
if ($id_pedido) {
    $query_pedido = "SELECT p.*, c.total, e.tipo_envio, e.fecha_estimada, paq.nombre_paqueteria 
                     FROM pedido p 
                     LEFT JOIN carrito c ON p.id_carrito = c.id_carrito
                     LEFT JOIN envio e ON p.id_envio = e.id_envio
                     LEFT JOIN paqueteria paq ON p.id_paqueteria = paq.id_paqueteria
                     WHERE p.id_pedido = ?";
    $stmt_pedido = $conn->prepare($query_pedido);
    $stmt_pedido->bind_param('i', $id_pedido);
    $stmt_pedido->execute();
    $result_pedido = $stmt_pedido->get_result();
    $pedido_info = $result_pedido->fetch_assoc();
    $stmt_pedido->close();
}

// Generar número de seguimiento
$numero_seguimiento = 'TRK' . date('YmdHis') . $id_pedido;
?>

<style>
.confirmation-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.success-header {
    text-align: center;
    padding: 30px 0;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 30px;
}

.success-icon {
    width: 80px;
    height: 80px;
    background: #28a745;
    border-radius: 50%;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    color: white;
}

.success-title {
    font-size: 28px;
    font-weight: bold;
    color: #28a745;
    margin-bottom: 10px;
}

.success-subtitle {
    font-size: 16px;
    color: #666;
}

.confirmation-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.detail-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 25px;
    border-left: 4px solid #007bff;
}

.detail-card h3 {
    font-size: 18px;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.detail-item:last-child {
    border-bottom: none;
    font-weight: bold;
    margin-top: 10px;
    padding-top: 15px;
    border-top: 2px solid #007bff;
}

.detail-label {
    color: #666;
    font-weight: 500;
}

.detail-value {
    color: #333;
    font-weight: bold;
}

.bonus-alert {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    margin: 20px 0;
}

.bonus-alert h4 {
    margin: 0 0 10px 0;
    font-size: 20px;
}

.bonus-amount {
    font-size: 24px;
    font-weight: bold;
}

.tracking-info {
    background: #e7f3ff;
    border: 2px solid #007bff;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    margin: 30px 0;
}

.tracking-number {
    font-size: 24px;
    font-weight: bold;
    color: #007bff;
    letter-spacing: 2px;
    margin: 10px 0;
    padding: 10px;
    background: white;
    border-radius: 5px;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 40px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 30px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    text-decoration: none;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    min-width: 200px;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    color: white;
    text-decoration: none;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
    color: white;
    text-decoration: none;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #218838;
    color: white;
    text-decoration: none;
}

.folio-box {
    background: #fff3cd;
    border: 2px solid #ffc107;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    margin: 20px 0;
}

.folio-number {
    font-size: 28px;
    font-weight: bold;
    color: #856404;
    letter-spacing: 2px;
    margin: 10px 0;
}

.instructions {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin: 30px 0;
}

.instructions h4 {
    color: #333;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.instructions ul {
    list-style: none;
    padding: 0;
}

.instructions li {
    padding: 8px 0;
    padding-left: 25px;
    position: relative;
}

.instructions li::before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #28a745;
    font-weight: bold;
}

.payment-summary {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
}

.timeline {
    margin: 30px 0;
}

.timeline-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #e9ecef;
}

.timeline-item:last-child {
    border-bottom: none;
}

.timeline-icon {
    width: 40px;
    height: 40px;
    background: #007bff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 15px;
    flex-shrink: 0;
}

.timeline-content {
    flex: 1;
}

.timeline-title {
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.timeline-description {
    color: #666;
    font-size: 14px;
}

@media (max-width: 768px) {
    .confirmation-container {
        margin: 20px;
        padding: 15px;
    }
    
    .confirmation-details {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 300px;
    }
}
</style>

<div class="confirmation-container">
    <!-- Header de éxito -->
    <div class="success-header">
    <?php if ($tipo_pago === 'tarjeta'): ?>
    <div class="success-icon">✓</div>
        <h1 class="success-title">¡Pago Procesado Exitosamente!</h1>
        <p class="success-subtitle">
                Tu pago con tarjeta ha sido autorizado y procesado
            <?php else: ?>
                <h1 class="success-title">¡Tu código de pago ha sido generado!</h1>
            <?php endif; ?>
        </p>
    </div>

    <!-- Información específica del tipo de pago -->
    <?php if ($tipo_pago === 'sucursal' && $folio): ?>
        <div class="folio-box">
            <h3>📋 Código de Pago</h3>
            <div class="folio-number"><?= htmlspecialchars($folio) ?></div>
            <p>Presenta este código en cualquier OXXO o sucursal autorizada</p>
            <p><strong>⏰ Tiempo límite: 3 días</strong></p>
        </div>
        
        <div class="instructions">
            <h4>📝 Instrucciones para pagar:</h4>
            <ul>
                <li>Dirígete a cualquier OXXO o sucursal autorizada</li>
                <li>Solicita realizar un pago de servicio</li>
                <li>Proporciona el código: <strong><?= htmlspecialchars($folio) ?></strong></li>
                <li>Paga el monto total en efectivo</li>
                <li>Guarda tu comprobante de pago</li>
                <li>Tu pedido será procesado una vez confirmado el pago</li>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($tipo_pago === 'tarjeta' && $tarjeta): ?>
        <div class="payment-summary">
            <h3>💳 Resumen del Pago</h3>
            <div class="detail-item">
                <span class="detail-label">Tarjeta utilizada:</span>
                <span class="detail-value"><?= htmlspecialchars($tarjeta) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Estado:</span>
                <span class="detail-value" style="color: #28a745;">✓ Autorizado</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Fecha de transacción:</span>
                <span class="detail-value"><?= date('d/m/Y H:i:s') ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Detalles de la confirmación -->
    <div class="confirmation-details">
        <div class="detail-card">
            <h3>👤 Información del Cliente</h3>
            <div class="detail-item">
                <span class="detail-label">Nombre:</span>
                <span class="detail-value">
                    <?= htmlspecialchars($cliente['nom_persona'] . ' ' . $cliente['apellido_paterno'] . ' ' . $cliente['apellido_materno']) ?>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">ID de Cliente:</span>
                <span class="detail-value"><?= $cliente['id_cliente'] ?></span>
            </div>
        </div>

        <?php if ($pedido_info): ?>
        <div class="detail-card">
            <h3>📦 Información del Pedido</h3>
            <div class="detail-item">
                <span class="detail-label">Número de Pedido:</span>
                <span class="detail-value">#<?= $id_pedido ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Subtotal:</span>
                <span class="detail-value">$<?= number_format($pedido_info['total'], 2) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">IVA:</span>
                <span class="detail-value">$<?= number_format($pedido_info['iva'], 2) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">IEPS:</span>
                <span class="detail-value">$0.00</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Envío:</span>
                <span class="detail-value">$<?= number_format($pedido_info['precio_total_pedido'], 2) - number_format($pedido_info['total'], 2) - number_format($pedido_info['iva'], 2) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Total Pagado:</span>
                <span class="detail-value">$<?= number_format($pedido_info['precio_total_pedido'], 2) ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>  

    <!-- Información del monedero bonus -->
    <?php if ($bonus > 0): ?>
    <div class="bonus-alert">
        <h4>🎉 ¡Felicidades! Has ganado cashback</h4>
        <div class="bonus-amount">+$<?= $bonus ?></div>
        <p>Se ha agregado el 5% del monto total a tu monedero</p>
    </div>
    <?php endif; ?>

    <!-- Información de seguimiento -->
    <?php if ($tipo_pago === 'tarjeta'): ?>
    <div class="tracking-info">
        <h3>📍 Seguimiento de tu Pedido</h3>
        <p>Número de Seguimiento:</p>
        <div class="tracking-number"><?= $numero_seguimiento ?></div>
        <p>Podrás rastrear tu pedido con este número en nuestra sección de pedidos</p>
    </div>
    <?php endif; ?>

    <!-- Timeline del proceso -->
    <div class="timeline">
        <h3>📅 Estado del Pedido</h3>
        
        <div class="timeline-item">
            <div class="timeline-icon" style="background: #28a745;">1</div>
            <div class="timeline-content">
                <div class="timeline-title">✓ Pago Procesado</div>
                <div class="timeline-description">
                    <?php if ($tipo_pago === 'tarjeta'): ?>
                        Tu pago ha sido autorizado y procesado exitosamente
                    <?php else: ?>
                        Código de pago generado. Pendiente de pago en sucursal
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if ($tipo_pago === 'tarjeta'): ?>
        <div class="timeline-item">
            <div class="timeline-icon">2</div>
            <div class="timeline-content">
                <div class="timeline-title">📋 Procesando Pedido</div>
                <div class="timeline-description">Preparando tu pedido para envío</div>
            </div>
        </div>
        
        <div class="timeline-item">
            <div class="timeline-icon">3</div>
            <div class="timeline-content">
                <div class="timeline-title">🚚 En Camino</div>
                <div class="timeline-description">
                    <?php if ($pedido_info && $pedido_info['fecha_estimada']): ?>
                        Entrega estimada: <?= date('d/m/Y', strtotime($pedido_info['fecha_estimada'])) ?>
                    <?php else: ?>
                        Entrega estimada: 3-5 días hábiles
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="timeline-item">
            <div class="timeline-icon">4</div>
            <div class="timeline-content">
                <div class="timeline-title">📦 Entregado</div>
                <div class="timeline-description">Tu pedido será entregado en la dirección especificada</div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Botones de acción -->
    <div class="action-buttons">
        <button type="submit" form="formBC" class="btn btn-primary">
            Pagar
        </button>

    </div>
</div>


<form id="formBC" action="../Pago/ConfirmacionSucursal.php" method="post">
    <div id="inputsOcultos">
        <?php foreach ($articulos as $index => $id): ?>
            <input type="hidden" name="articulos[]" value="<?= htmlspecialchars($id) ?>">
            <input type="hidden" name="detalles[<?= $id ?>]" value="<?= htmlspecialchars($detalles[$id]) ?>">
        <?php endforeach; ?>
        <input type="hidden" name="id_pedido" value="<?= htmlspecialchars($id_pedido) ?>">
        <input type="hidden" name="precio_total_pedido" value="<?= htmlspecialchars($total) ?>">
        <input type="hidden" name="tipo" value="sucursal">
        <input type="hidden" name="bonus" value="<?= htmlspecialchars(number_format($bonus, 2)) ?>">
        <input type="hidden" name="monedero_usado" value="<?= htmlspecialchars(number_format($monedero_usado, 2)) ?>">
        <input type="hidden" name="folio" value="<?= htmlspecialchars($folio) ?>">
    </div>
</form>



<script>
// Auto-scroll hacia arriba
window.scrollTo(0, 0);

// Mostrar notificación de éxito
document.addEventListener('DOMContentLoaded', function() {
    // Opcional: mostrar una notificación toast
    if (typeof showToast === 'function') {
        showToast('¡Pago procesado exitosamente!', 'success');
    }
    
    // Opcional: enviar evento de conversión para analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', 'purchase', {
            transaction_id: '<?= $id_pedido ?>',
            value: <?= $pedido_info['precio_total_pedido'] ?? 0 ?>,
            currency: 'MXN'
        });
    }
});

// Función para copiar número de seguimiento
function copyTracking() {
    const trackingNumber = '<?= $numero_seguimiento ?>';
    navigator.clipboard.writeText(trackingNumber).then(function() {
        alert('Número de seguimiento copiado al portapapeles');
    });
}

// Función para copiar código de folio
function copyFolio() {
    const folio = '<?= $folio ?>';
    navigator.clipboard.writeText(folio).then(function() {
        alert('Código de pago copiado al portapapeles');
    });
}

// Agregar funcionalidad de copia al hacer clic en los números
document.addEventListener('DOMContentLoaded', function() {
    const trackingElement = document.querySelector('.tracking-number');
    if (trackingElement) {
        trackingElement.style.cursor = 'pointer';
        trackingElement.title = 'Clic para copiar';
        trackingElement.addEventListener('click', copyTracking);
    }
    
    const folioElement = document.querySelector('.folio-number');
    if (folioElement) {
        folioElement.style.cursor = 'pointer';
        folioElement.title = 'Clic para copiar';
        folioElement.addEventListener('click', copyFolio);
    }
});
</script>

<?php include('../Nav/footer.php'); 



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    procesarDetallesCompra($conn, $detalles, $id_cliente, $id_pedido);
    try {
        // Recuperar datos del formulario
        $id_forma_pago = $_GET['id_forma'] ?? ($_POST['id_forma'] ?? '');
        $monto = $_GET['mon'] ?? ($_POST['mon'] ?? '');

        // Validar datos básicos
        if (!$id_forma_pago || !$id_pedido || !$monto) {
            throw new Exception("Faltan datos requeridos para registrar el pago.");
        }

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

    } catch (Exception $e) {
        echo "Error al registrar el pago: " . $e->getMessage();
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