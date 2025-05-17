<?php 
include('../BD/ConexionBD.php'); 
include('../Nav/header.php');

$id_usuario = $_SESSION['id_usuario'];

// Obtener ID del cliente
$sql = "SELECT id_cliente FROM cliente WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($id_cliente);
$stmt->fetch();
$stmt->close();

// Obtener monto e id_pedido desde POST/GET
$monto = $_POST['monto'] ?? $_GET['monto'] ?? 50;
$id_pedido = $_POST['id_pedido'] ?? $_GET['id_pedido'] ?? 1;

// Verificar que tenemos los datos necesarios
if (!$monto || !$id_pedido) {
    echo "<script>alert('Error: Faltan datos del pedido'); window.location.href='../carrito/';</script>";
    exit();
}

// Obtener tarjetas del cliente desde la base de datos principal
$query_tarjetas = "SELECT * FROM tarjeta WHERE titular = ?";
$stmt_tarjetas = $conn->prepare($query_tarjetas);
$stmt_tarjetas->bind_param('i', $id_cliente);
$stmt_tarjetas->execute();
$tarjetas = $stmt_tarjetas->get_result();

// Verificar si hay un mensaje de resultado del procesamiento
$mensaje_resultado = $_GET['resultado'] ?? '';
$tipo_resultado = $_GET['tipo'] ?? '';
?>

<style>
.payment-container {
    display: flex;
    gap: 20px;
    padding: 20px;
}

.payment-section {
    flex: 1;
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.payment-method {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin: 10px 0;
    cursor: pointer;
    transition: all 0.3s;
}

.payment-method:hover {
    border-color: #007bff;
    background: #f0f8ff;
}

.payment-method.selected {
    border-color: #007bff;
    background: #e7f3ff;
}

.card-item {
    background: white;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 10px;
    margin: 5px 0;
    cursor: pointer;
}

.card-item:hover {
    background: #f5f5f5;
}

.card-item.selected {
    border-color: #007bff;
    background: #e7f3ff;
}

.loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 900000;
    backdrop-filter: blur(5px);
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.loading-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 40px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    animation: slideUp 0.4s ease-out;
    max-width: 400px;
    width: 90%;
}

@keyframes slideUp {
    from { transform: translate(-50%, -30%); opacity: 0; }
    to { transform: translate(-50%, -50%); opacity: 1; }
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
    transition: transform 0.3s ease;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-content h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 24px;
}

.loading-content p {
    color: #666;
    font-size: 16px;
    line-height: 1.5;
    margin: 0;
}

/* Progreso visual opcional */
.loading-progress {
    width: 100%;
    height: 4px;
    background: #f0f0f0;
    border-radius: 2px;
    margin-top: 20px;
    overflow: hidden;
}

.loading-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #007bff, #0056b3);
    border-radius: 2px;
    animation: progressBar 5s linear forwards;
    width: 0%;
}

@keyframes progressBar {
    from { width: 0%; }
    to { width: 100%; }
}

/* Estados del botón mejorados */
.btn-pagar:disabled {
    background: #6c757d;
    cursor: not-allowed;
    position: relative;
    overflow: hidden;
}

.btn-pagar:disabled::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

.right-section {
    flex: 0 0 300px;
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: fit-content;
}

.resumen-titulo {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
    color: #333;
}

.resumen-compra {
    border-top: 1px solid #eee;
    padding-top: 15px;
}

.resumen-linea {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    color: #666;
}

.resumen-linea.total-final {
    font-weight: bold;
    font-size: 18px;
    color: #333;
    border-top: 1px solid #eee;
    padding-top: 10px;
    margin-top: 15px;
}

.estado-pago {
    margin-top: 15px;
    padding: 10px;
    border-radius: 5px;
    text-align: center;
    font-weight: bold;
}

.estado-pago.pendiente {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.estado-pago.autorizado {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.estado-pago.rechazado {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.btn-pagar {
    width: 100%;
    padding: 12px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 15px;
}

.btn-pagar:hover {
    background: #0056b3;
}

.btn-pagar:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.btn-nuevo-tarjeta {
    display: block;
    width: 100%;
    padding: 10px;
    background: #28a745;
    color: white;
    text-decoration: none;
    text-align: center;
    border-radius: 5px;
    margin-top: 10px;
}

.btn-nuevo-tarjeta:hover {
    background: #218838;
    color: white;
    text-decoration: none;
}

.mensaje-resultado {
    padding: 15px;
    margin: 10px 0;
    border-radius: 5px;
    font-weight: bold;
}

.mensaje-resultado.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.mensaje-resultado.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>

<div class="payment-container">
    <div class="payment-section">
        <h2>Selecciona tu método de pago</h2>

        <?php if ($mensaje_resultado): ?>
            <div class="mensaje-resultado <?= $tipo_resultado ?>">
                <?= htmlspecialchars($mensaje_resultado) ?>
            </div>
        <?php endif; ?>

        <form id="paymentForm" action="Comprobar_Banco.php" method="POST">
            <!-- Campos ocultos con valores verificados -->
            <input type="hidden" name="monto" value="<?= htmlspecialchars($monto, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($id_cliente, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id_pedido" value="<?= htmlspecialchars($id_pedido, ENT_QUOTES, 'UTF-8') ?>">
            
            <!-- Debug: mostrar valores -->
            <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; font-size: 12px;">
                <strong>Información del pedido:</strong><br>
                Monto: $<?= number_format($monto, 2) ?><br>
                ID Cliente: <?= $id_cliente ?><br>
                ID Pedido: <?= $id_pedido ?><br>
            </div>
            
            <!-- Pago con Tarjeta -->
            <div class="payment-method" onclick="selectPaymentMethod('tarjeta')">
                <input type="radio" name="metodo_pago" value="tarjeta" id="tarjeta">
                <label for="tarjeta">💳 Pago con Tarjeta</label>
            </div>
            
            <div id="tarjeta-options" style="display: none; margin-left: 20px;">
                <h4>Selecciona una tarjeta:</h4>
                <?php if ($tarjetas->num_rows > 0): ?>
                    <?php 
                    // Reset del resultado para poder iterarlo de nuevo
                    $tarjetas->data_seek(0); 
                    ?>
                    <?php while ($tarjeta = $tarjetas->fetch_assoc()): ?>
                        <div class="card-item" onclick="selectCard('<?= $tarjeta['id_tarjeta'] ?>')">
                            <input type="radio" name="id_tarjeta" value="<?= $tarjeta['id_tarjeta'] ?>" id="card_<?= $tarjeta['id_tarjeta'] ?>">
                            <label for="card_<?= $tarjeta['id_tarjeta'] ?>">
                                <strong><?= htmlspecialchars($tarjeta['red_pago']) ?></strong> 
                                **** **** **** <?= substr($tarjeta['numero_tarjeta'], -4) ?>
                                <br>
                                <small><?= ucfirst(strtolower(htmlspecialchars($tarjeta['tipo_tarjeta']))) ?></small>
                            </label>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No tienes tarjetas registradas.</p>
                <?php endif; ?>
                
                <a href="Nueva_Tarjeta.php?return=pago&monto=<?= urlencode($monto) ?>&id_pedido=<?= urlencode($id_pedido) ?>" class="btn-nuevo-tarjeta">
                    + Agregar nueva tarjeta
                </a>
            </div>
            
            <!-- Pago en Sucursal -->
            <div class="payment-method" onclick="selectPaymentMethod('sucursal')">
                <input type="radio" name="metodo_pago" value="sucursal" id="sucursal">
                <label for="sucursal">🏪 Pago en Sucursal</label>
            </div>
            
            <div id="sucursal-options" style="display: none; margin-left: 20px;">
                <p>Se generará un código para que puedas pagar en cualquier OXXO o sucursal autorizada.</p>
                <p><strong>Tiempo límite:</strong> 3 días</p>
            </div>
            
            <button type="submit" class="btn-pagar" id="btn-pagar" disabled>
                Procesar Pago
            </button>
        </form>
    </div>
    
    <!-- Resumen de compra -->
    <div class="right-section">
        <div class="resumen-titulo">Resumen de la compra</div>
        <div class="resumen-compra">
            <div class="resumen-linea">
                <span>Subtotal</span>
                <span>$<?= number_format($monto, 2) ?></span>
            </div>
            <div class="resumen-linea">
                <span>Envío</span>
                <span>$50.00</span>
            </div>
            <div class="resumen-linea total-final">
                <span>Total a pagar</span>
                <span>$<?= number_format($monto + 50, 2) ?></span>
            </div>
        </div>
        
        <div id="estado-pago" class="estado-pago pendiente">
            Selecciona un método de pago
        </div>
    </div>
</div>

<!-- Overlay de carga -->
<div id="loading-overlay" class="loading-overlay">
    <div class="loading-content">
        <div class="spinner"></div>
        <h3>Procesando pago...</h3>
        <p id="loading-message">Por favor espera mientras verificamos tu información bancaria.</p>
        
        <!-- Barra de progreso opcional -->
        <div class="loading-progress">
            <div class="loading-progress-bar"></div>
        </div>
    </div>
</div>

<script>

function selectPaymentMethod(method) {
    // Ocultar todas las opciones
    document.getElementById('tarjeta-options').style.display = 'none';
    document.getElementById('sucursal-options').style.display = 'none';
    
    // Remover selección visual
    document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('selected'));
    
    // Mostrar opciones del método seleccionado
    document.getElementById(method + '-options').style.display = 'block';
    
    // Agregar selección visual
    event.target.closest('.payment-method').classList.add('selected');
    
    // Marcar radio button
    document.getElementById(method).checked = true;
    
    // Habilitar botón de pago
    checkFormValid();
    
    // Actualizar estado
    updatePaymentStatus('Método seleccionado');
}

function selectCard(cardId) {
    // Remover selección de todas las tarjetas
    document.querySelectorAll('.card-item').forEach(el => el.classList.remove('selected'));
    
    // Agregar selección a la tarjeta clickeada
    event.target.closest('.card-item').classList.add('selected');
    
    // Marcar radio button
    document.getElementById('card_' + cardId).checked = true;
    
    // Habilitar botón de pago
    checkFormValid();
    
    // Actualizar estado
    updatePaymentStatus('Tarjeta seleccionada');
}

function checkFormValid() {
    const metodo = document.querySelector('input[name="metodo_pago"]:checked');
    let valid = false;
    
    if (metodo) {
        if (metodo.value === 'sucursal') {
            valid = true;
        } else if (metodo.value === 'tarjeta') {
            const tarjeta = document.querySelector('input[name="id_tarjeta"]:checked');
            valid = tarjeta !== null;
        }
    }
    
    document.getElementById('btn-pagar').disabled = !valid;
}

function updatePaymentStatus(status) {
    const statusEl = document.getElementById('estado-pago');
    statusEl.textContent = status;
    statusEl.className = 'estado-pago pendiente';
}

// Variable para controlar si el formulario ya se está enviando
let formSubmitting = false;

// Manejar envío del formulario con delay
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevenir el envío inmediato
    
    // Evitar múltiples envíos
    if (formSubmitting) return;
    formSubmitting = true;
    
    // Mostrar loading inmediatamente
    document.getElementById('loading-overlay').style.display = 'block';
    updatePaymentStatus('Procesando...');
    
    // Deshabilitar el botón para evitar clics múltiples
    document.getElementById('btn-pagar').disabled = true;
    document.getElementById('btn-pagar').textContent = 'Procesando...';
    
    // Simular tiempo de procesamiento (3 segundos) antes de enviar
    setTimeout(() => {
        // Actualizar mensaje durante el processing
        updatePaymentStatus('Verificando información bancaria...');
        
        // Después de 2 segundos más, enviar el formulario
        setTimeout(() => {
            updatePaymentStatus('Finalizando transacción...');
            
            // Enviar el formulario después del delay total (5 segundos)
            setTimeout(() => {
                this.submit();
            }, 1000);
        }, 2000);
    }, 2000);
});

// Opcional: Añadir efectos visuales adicionales al spinner
document.addEventListener('DOMContentLoaded', function() {
    const spinner = document.querySelector('.spinner');
    if (spinner) {
        // Añadir clase para animación más llamativa cuando esté visible
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.target.style.display === 'block') {
                    spinner.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        if (spinner.style.transform) {
                            spinner.style.transform = 'scale(1)';
                        }
                    }, 200);
                }
            });
        });
        
        observer.observe(document.getElementById('loading-overlay'), {
            attributes: true,
            attributeFilter: ['style']
        });
    }
});
</script>

<?php include('../Nav/footer.php'); ?>