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
    border-radius: 50%;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    color: white;
}

.success-icon.tarjeta {
    background: #28a745;
}

.success-icon.sucursal {
    background: #ffc107;
    color: #856404;
}

.success-title {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 10px;
}

.success-title.tarjeta {
    color: #28a745;
}

.success-title.sucursal {
    color: #856404;
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
    cursor: pointer;
    transition: all 0.3s;
}

.tracking-number:hover {
    background: #f0f8ff;
    transform: scale(1.02);
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

.btn-warning {
    background: #ffc107;
    color: #856404;
    border: 2px solid #856404;
}

.btn-warning:hover {
    background: #e0a800;
    color: #856404;
    text-decoration: none;
}

.folio-box {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 3px solid #ffc107;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    margin: 20px 0;
    box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
}

.folio-box h3 {
    color: #856404;
    margin-bottom: 15px;
    font-size: 22px;
}

.folio-number {
    font-size: 32px;
    font-weight: bold;
    color: #856404;
    letter-spacing: 3px;
    margin: 15px 0;
    padding: 15px;
    background: white;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s;
    border: 2px dashed #ffc107;
}

.folio-number:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
}

.folio-info {
    color: #856404;
    font-weight: 600;
    margin: 10px 0;
}

.time-limit {
    background: #dc3545;
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    display: inline-block;
    margin: 10px 0;
    font-weight: bold;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.instructions {
    background: #f8f9fa;
    border-left: 5px solid #ffc107;
    border-radius: 10px;
    padding: 25px;
    margin: 30px 0;
}

.instructions h4 {
    color: #856404;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 20px;
}

.instructions ol {
    padding-left: 20px;
    color: #333;
}

.instructions li {
    padding: 10px 0;
    font-weight: 500;
    border-bottom: 1px solid #e9ecef;
}

.instructions li:last-child {
    border-bottom: none;
}

.instructions li strong {
    color: #856404;
    background: #fff3cd;
    padding: 2px 8px;
    border-radius: 4px;
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
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 15px;
    flex-shrink: 0;
    font-weight: bold;
}

.timeline-icon.completed {
    background: #28a745;
}

.timeline-icon.pending {
    background: #ffc107;
    color: #856404;
}

.timeline-icon.future {
    background: #6c757d;
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

.sucursal-guide {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border: 2px solid #2196f3;
    border-radius: 15px;
    padding: 25px;
    margin: 30px 0;
}

.sucursal-guide h4 {
    color: #1976d2;
    margin-bottom: 20px;
    font-size: 20px;
    text-align: center;
}

.sucursal-stores {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.store-item {
    background: white;
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    border: 2px solid #e3f2fd;
    transition: all 0.3s;
}

.store-item:hover {
    border-color: #2196f3;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(33, 150, 243, 0.2);
}

.store-logo {
    font-size: 24px;
    margin-bottom: 10px;
}

.copy-button {
    background: #007bff;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12px;
    margin-left: 10px;
    transition: all 0.3s;
}

.copy-button:hover {
    background: #0056b3;
}

.alert-warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    text-align: center;
    font-weight: 600;
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
    
    .folio-number {
        font-size: 24px;
        letter-spacing: 2px;
    }
    
    .sucursal-stores {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="confirmation-container">
    <!-- Header de éxito -->
    <div class="success-header">
        <?php if ($tipo_pago === 'tarjeta'): ?>
            <div class="success-icon tarjeta">✓</div>
            <h1 class="success-title tarjeta">¡Pago Procesado Exitosamente!</h1>
            <p class="success-subtitle">Tu pago con tarjeta ha sido autorizado y procesado</p>
        <?php else: ?>
            <div class="success-icon sucursal">📋</div>
            <h1 class="success-title sucursal">¡Código de Pago Generado!</h1>
            <p class="success-subtitle">Tu pedido está reservado. Completa el pago en sucursal para procesarlo</p>
        <?php endif; ?>
    </div>

    <!-- Información específica del tipo de pago -->
    <?php if ($tipo_pago === 'sucursal' && $folio): ?>
        <div class="folio-box">
            <h3>💳 Tu Código de Pago</h3>
            <div class="folio-number" id="folioNumber" title="Clic para copiar">
                <?= htmlspecialchars($folio) ?>
                <button class="copy-button" onclick="copyFolio()">📋 Copiar</button>
            </div>
            <p class="folio-info">Presenta este código en cualquier sucursal autorizada</p>
            <div class="time-limit">⏰ Válido por 3 días</div>
            
            <div class="alert-warning">
                <strong>⚠️ Importante:</strong> Tu pedido será cancelado automáticamente si no realizas el pago dentro del tiempo límite
            </div>
        </div>
        
        <!-- Guía de sucursales autorizadas -->
        <div class="sucursal-guide">
            <h4>🏪 Sucursales Autorizadas</h4>
            <div class="sucursal-stores">
                <div class="store-item">
                    <div class="store-logo">🟢</div>
                    <strong>OXXO</strong>
                    <p>Más de 20,000 tiendas</p>
                </div>
                <div class="store-item">
                    <div class="store-logo">🔵</div>
                    <strong>7-Eleven</strong>
                    <p>Disponible 24/7</p>
                </div>
                <div class="store-item">
                    <div class="store-logo">🟡</div>
                    <strong>Farmacias del Ahorro</strong>
                    <p>Red nacional</p>
                </div>
                <div class="store-item">
                    <div class="store-logo">🔴</div>
                    <strong>Circle K</strong>
                    <p>Servicio rápido</p>
                </div>
            </div>
        </div>
        
        <div class="instructions">
            <h4>📝 Instrucciones Paso a Paso</h4>
            <ol>
                <li>Dirígete a cualquier <strong>OXXO</strong> o sucursal autorizada</li>
                <li>Solicita realizar un <strong>"Pago de Servicio"</strong></li>
                <li>Proporciona tu código: <strong><?= htmlspecialchars($folio) ?></strong></li>
                <li>Paga el monto total: <strong>$<?= $pedido_info ? number_format($pedido_info['precio_total_pedido'], 2) : '0.00' ?></strong> en efectivo</li>
                <li><strong>Guarda tu comprobante</strong> de pago como respaldo</li>
                <li>Tu pedido será procesado <strong>automáticamente</strong> una vez confirmado el pago</li>
                <li>Recibirás una <strong>notificación por email</strong> cuando el pago sea confirmado</li>
            </ol>
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
            <?php if ($tipo_pago === 'sucursal'): ?>
            <div class="detail-item">
                <span class="detail-label">Código de Pago:</span>
                <span class="detail-value"><?= htmlspecialchars($folio) ?></span>
            </div>
            <?php endif; ?>
            <div class="detail-item">
                <span class="detail-label">Subtotal:</span>
                <span class="detail-value">$<?= number_format($pedido_info['total'], 2) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Envío:</span>
                <span class="detail-value">$50.00</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Total <?= $tipo_pago === 'sucursal' ? 'a Pagar' : 'Pagado' ?>:</span>
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
        <p><?= $tipo_pago === 'sucursal' ? 'Se agregará el 5% del monto total a tu monedero una vez confirmado el pago' : 'Se ha agregado el 5% del monto total a tu monedero' ?></p>
    </div>
    <?php endif; ?>

    <!-- Información de seguimiento -->
    <?php if ($tipo_pago === 'tarjeta' || ($tipo_pago === 'sucursal' && $folio)): ?>
    <div class="tracking-info">
        <h3>📍 Seguimiento de tu Pedido</h3>
        <p>Número de Seguimiento:</p>
        <div class="tracking-number" id="trackingNumber" title="Clic para copiar">
            <?= $numero_seguimiento ?>
        </div>
        <p>Podrás rastrear tu pedido con este número en nuestra sección de pedidos</p>
        <?php if ($tipo_pago === 'sucursal'): ?>
        <div class="alert-warning">
            El seguimiento se activará una vez confirmado tu pago en sucursal
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Timeline del proceso -->
    <div class="timeline">
        <h3>📅 Estado del Pedido</h3>
        
        <?php if ($tipo_pago === 'tarjeta'): ?>
        <div class="timeline-item">
            <div class="timeline-icon completed">1</div>
            <div class="timeline-content">
                <div class="timeline-title">✓ Pago Procesado</div>
                <div class="timeline-description">Tu pago ha sido autorizado y procesado exitosamente</div>
            </div>
        </div>
        
        <div class="timeline-item">
            <div class="timeline-icon pending">2</div>
            <div class="timeline-content">
                <div class="timeline-title">📋 Procesando Pedido</div>
                <div class="timeline-description">Preparando tu pedido para envío</div>
            </div>
        </div>
        
        <div class="timeline-item">
            <div class="timeline-icon future">3</div>
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
            <div class="timeline-icon future">4</div>
            <div class="timeline-content">
                <div class="timeline-title">📦 Entregado</div>
                <div class="timeline-description">Tu pedido será entregado en la dirección especificada</div>
            </div>
        </div>
        
        <?php else: // Pago en sucursal ?>
        
        <div class="timeline-item">
            <div class="timeline-icon completed">1</div>
            <div class="timeline-content">
                <div class="timeline-title">✓ Código Generado</div>
                <div class="timeline-description">Código de pago creado: <?= htmlspecialchars($folio) ?></div>
            </div>
        </div>
        
        <div class="timeline-item">
            <div class="timeline-icon pending">2</div>
            <div class="timeline-content">
                <div class="timeline-title">⏳ Esperando Pago</div>
                <div class="timeline-description">
                    <strong>Realiza tu pago en sucursal dentro de 3 días</strong><br>
                    Monto a pagar: $<?= $pedido_info ? number_format($pedido_info['precio_total_pedido'], 2) : '0.00' ?>
                </div>
            </div>
        </div>
        
        <div class="timeline-item">
            <div class="timeline-icon future">3</div>
            <div class="timeline-content">
                <div class="timeline-title">📋 Procesando Pedido</div>
                <div class="timeline-description">Una vez confirmado el pago, procesaremos tu pedido</div>
            </div>
        </div>
        
        <div class="timeline-item">
            <div class="timeline-icon future">4</div>
            <div class="timeline-content">
                <div class="timeline-title">🚚 Envío</div>
                <div class="timeline-description">Tu pedido será enviado en 1-2 días hábiles tras confirmar el pago</div>
            </div>
        </div>
        
        <div class="timeline-item">
            <div class="timeline-icon future">5</div>
            <div class="timeline-content">
                <div class="timeline-title">📦 Entregado</div>
                <div class="timeline-description">
                    <?php if ($pedido_info && $pedido_info['fecha_estimada']): ?>
                        Entrega estimada: <?= date('d/m/Y', strtotime($pedido_info['fecha_estimada'] . ' +2 days')) ?>
                    <?php else: ?>
                        Entrega estimada: 5-7 días hábiles desde el pago
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Botones de acción -->
    <div class="action-buttons">
        <?php if ($tipo_pago === 'sucursal'): ?>
            <button onclick="copyFolio()" class="btn btn-warning">
                📋 Copiar Código de Pago
            </button>
        <?php endif; ?>
        
        <?php if ($id_pedido): ?>
            <a href="../pedidos/detalle.php?id=<?= $id_pedido ?>" class="btn btn-primary">
                Ver Detalle del Pedido
            </a>
        <?php endif; ?>
        
        <a href="../Pedido/seguimiento_pedido.php" class="btn btn-secondary">
            Mis Pedidos
        </a>
        
        <a href="../Home/Home.php" class="btn btn-success">
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
        const message = '<?= $tipo_pago === "tarjeta" ? "¡Pago procesado exitosamente!" : "¡Código de pago generado!" ?>';
        const type = '<?= $tipo_pago === "tarjeta" ? "success" : "warning" ?>';
        showToast(message, type);
    }
    
    // Opcional: enviar evento de conversión para analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', '<?= $tipo_pago === "tarjeta" ? "purchase" : "begin_checkout" ?>', {
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
        showCopyMessage('Número de seguimiento copiado al portapapeles');
    }).catch(function() {
        // Fallback para navegadores que no soportan clipboard API
        const textArea = document.createElement('textarea');
        textArea.value = trackingNumber;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showCopyMessage('Número de seguimiento copiado al portapapeles');
    });
}

// Función para copiar código de folio
function copyFolio() {
    const folio = '<?= $folio ?>';
    navigator.clipboard.writeText(folio).then(function() {
        showCopyMessage('Código de pago copiado al portapapeles');
    }).catch(function() {
        // Fallback para navegadores que no soportan clipboard API
        const textArea = document.createElement('textarea');
        textArea.value = folio;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showCopyMessage('Código de pago copiado al portapapeles');
    });
}

// Función para mostrar mensaje de copia
function showCopyMessage(message) {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 9999;
        font-weight: bold;
        animation: slideIn 0.3s ease-out;
    `;
    notification.textContent = message;
    
    // Agregar animación CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(notification);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Agregar funcionalidad de copia al hacer clic en los números
document.addEventListener('DOMContentLoaded', function() {
    const trackingElement = document.querySelector('.tracking-number');
    if (trackingElement) {
        trackingElement.addEventListener('click', copyTracking);
    }
    
    const folioElement = document.querySelector('.folio-number');
    if (folioElement) {
        folioElement.addEventListener('click', function(e) {
            // Prevenir que se ejecute si se hace clic en el botón
            if (e.target.classList.contains('copy-button')) {
                return;
            }
            copyFolio();
        });
    }
    
    // Actualizar countdown si es pago en sucursal
    <?php if ($tipo_pago === 'sucursal'): ?>
    updatePaymentCountdown();
    <?php endif; ?>
});

<?php if ($tipo_pago === 'sucursal'): ?>
// Función para actualizar el countdown del pago
function updatePaymentCountdown() {
    const timeLimit = new Date();
    timeLimit.setDate(timeLimit.getDate() + 3); // 3 días desde ahora
    
    function updateCounter() {
        const now = new Date().getTime();
        const distance = timeLimit.getTime() - now;
        
        if (distance > 0) {
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            
            let timeText = '';
            if (days > 0) {
                timeText = `⏰ ${days} día${days > 1 ? 's' : ''}, ${hours} hora${hours > 1 ? 's' : ''}`;
            } else if (hours > 0) {
                timeText = `⏰ ${hours} hora${hours > 1 ? 's' : ''}, ${minutes} minuto${minutes > 1 ? 's' : ''}`;
            } else {
                timeText = `⏰ ${minutes} minuto${minutes > 1 ? 's' : ''} restantes`;
            }
            
            const timeLimitElement = document.querySelector('.time-limit');
            if (timeLimitElement) {
                timeLimitElement.textContent = timeText;
            }
        } else {
            const timeLimitElement = document.querySelector('.time-limit');
            if (timeLimitElement) {
                timeLimitElement.textContent = '⚠️ Tiempo expirado';
                timeLimitElement.style.background = '#dc3545';
            }
        }
    }
    
    // Actualizar cada minuto
    updateCounter();
    setInterval(updateCounter, 60000);
}
<?php endif; ?>

// Función para imprimir la página
function printConfirmation() {
    window.print();
}

// Agregar botón de imprimir si es necesario
document.addEventListener('DOMContentLoaded', function() {
    const actionButtons = document.querySelector('.action-buttons');
    if (actionButtons) {
        const printButton = document.createElement('button');
        printButton.className = 'btn btn-secondary';
        printButton.innerHTML = '🖨️ Imprimir Confirmación';
        printButton.onclick = printConfirmation;
        actionButtons.appendChild(printButton);
    }
});

// Verificar estado del pago periódicamente si es pago en sucursal
<?php if ($tipo_pago === 'sucursal' && $id_pedido): ?>
function checkPaymentStatus() {
    fetch('../ajax/check_payment_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id_pedido=<?= $id_pedido ?>&folio=<?= $folio ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'paid') {
            // Recargar la página para mostrar el estado actualizado
            location.reload();
        }
    })
    .catch(error => {
        console.log('Error checking payment status:', error);
    });
}

// Verificar cada 30 segundos
setInterval(checkPaymentStatus, 30000);
<?php endif; ?>
</script>

<?php include('../Nav/footer.php'); 








?>