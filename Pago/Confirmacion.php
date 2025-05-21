<?php 
include('../BD/ConexionBD.php'); 
include('../Nav/header.php');


$id_usuario = $_SESSION['id_usuario'];

// Obtener parámetros de la confirmación
$folio = $_GET['folio'] ?? '';
$tipo_pago = $_GET['tipo'] ?? '';
$tarjeta = $_GET['tarjeta'] ?? '';
$bonus = $_GET['bonus'] ?? '0.00';
$id_pedido = $_GET['id_pedido'] ?? '';

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
        <div class="success-icon">✓</div>
        <h1 class="success-title">¡Pago Procesado Exitosamente!</h1>
        <p class="success-subtitle">
            <?php if ($tipo_pago === 'tarjeta'): ?>
                Tu pago con tarjeta ha sido autorizado y procesado
            <?php else: ?>
                Tu código de pago ha sido generado
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
                <span class="detail-label">Envío:</span>
                <span class="detail-value">$50.00</span>
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
        <?php if ($id_pedido): ?>
            <a href="../pedidos/detalle.php?id=<?= $id_pedido ?>" class="btn btn-primary">
                Ver Detalle del Pedido
            </a>
        <?php endif; ?>
        
        <a href="../pedidos/mis_pedidos.php" class="btn btn-secondary">
            Mis Pedidos
        </a>
        
        <a href="../Home\Home.php" class="btn btn-success">
            Seguir Comprando
        </a>
    </div>
</div>

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

<?php include('../Nav/footer.php'); ?>