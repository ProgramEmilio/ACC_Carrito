<?php 
include('../BD/ConexionBD.php'); 
include('../Nav/header.php');

$id_usuario = $_SESSION['id_usuario'];
$precio_total_pedido = $_GET['precio_total_pedido'] ?? null;
$articulos = $_POST['articulos'] ?? [];
$detalles = $_POST['detalles'] ?? [];

// Obtener ID del cliente
$sql = "SELECT id_cliente, monedero FROM cliente WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($id_cliente, $monedero_disponible);
$stmt->fetch();
$stmt->close();


// Obtener monto e id_pedido desde POST/GET
$monto = $_POST['precio_total_pedido'] ?? $_GET['precio_total_pedido'] ?? 50;
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../CSS/pagos.css" type="text/css">

    <title>Pagos</title>
</head>
<body>

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
            <input type="hidden" name="monto" id="monto_input" value="<?= htmlspecialchars($monto, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($id_cliente, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id_pedido" value="<?= htmlspecialchars($id_pedido, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="monedero_usado" id="monedero_usado_input" value="0">
            
        
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
                
                <a href="../Perfil/Formas_pago.php" class="btn-nuevo-tarjeta">
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
            
            <!-- Opción Monedero -->
            <div class="wallet-checkbox">
                <input type="radio" class="checkbox_monedero" id="usar_monedero" name="usar_monedero" onchange="toggleMonedero()" <?= $monedero_disponible <= 0 ? 'disabled' : '' ?>>
                <label for="usar_monedero">👛 Usar puntos de monedero</label>
                
                <div id="monedero-options" style="display: none; margin-top: 10px;">
                    <div class="wallet-balance">
                        Saldo disponible: $<span id="monedero_disponible"><?= number_format($monedero_disponible, 2) ?></span>
                    </div>
                    
                    <div class="wallet-amount">
                        <input type="range" id="monedero_slider" class="wallet-slider" min="0" 
                               max="<?= min($monedero_disponible, $monto) ?>" 
                               value="0" step="1" 
                               oninput="updateMonederoAmount(this.value)">
                        <div class="wallet-value">$<span id="monedero_amount">0.00</span></div>
                    </div>
                    
                    <div id="monedero_error" class="wallet-error" style="display: none;"></div>
                </div>
            </div>
            
            <button type="submit" class="btn-pagar" id="btn-pagar" disabled>
                Procesar Pago
            </button>

            <div id="inputsOcultos">
                <?php foreach ($articulos as $index => $id): ?>
                    <input type="hidden" name="articulos[]" value="<?= htmlspecialchars($id) ?>">
                    <input type="hidden" name="detalles[<?= $id ?>]" value="<?= htmlspecialchars($detalles[$id]) ?>">
                <?php endforeach; ?>
            </div>
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
            
            <div class="resumen-linea" id="monedero-descuento" style="display: none;">
                <span>Descuento monedero</span>
                <span id="monedero-descuento-valor">-$0.00</span>
            </div>
            
            <div class="resumen-linea">
                
            </div>
            
            <div class="resumen-linea total-final">
                <span>Total a pagar</span>
                <span id="total-final">$<?= number_format($monto) ?></span>
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
// Variables globales para mantener los valores
let montoOriginal = <?= $monto ?>;
let montoTotal = <?= $monto + 50 ?>; // Incluye envío
let montoMonedero = 0;
let monederoDisponible = <?= $monedero_disponible ?>;
let metodoPagoSeleccionado = '';

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
    
    // Actualizar variable global
    metodoPagoSeleccionado = method;
    
    // Habilitar botón de pago
    checkFormValid();
    
    // Actualizar estado
    updatePaymentStatus('Método seleccionado: ' + (method === 'tarjeta' ? 'Tarjeta' : 'Sucursal'));
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

function toggleMonedero() {
    const checkbox = document.getElementById('usar_monedero');
    const optionsDiv = document.getElementById('monedero-options');
    const descuentoDiv = document.getElementById('monedero-descuento');
    
    if (checkbox.checked) {
        optionsDiv.style.display = 'block';
        descuentoDiv.style.display = 'flex';
        // Establecer un valor inicial para el slider
        const valorInicial = Math.min(monederoDisponible, montoOriginal) / 2;
        document.getElementById('monedero_slider').value = valorInicial;
        updateMonederoAmount(valorInicial);
    } else {
        optionsDiv.style.display = 'none';
        descuentoDiv.style.display = 'none';
        // Resetear el valor del monedero
        updateMonederoAmount(0);
    }
    
    checkFormValid();
}

function updateMonederoAmount(amount) {
    amount = parseFloat(amount);
    
    // Validar que no sea mayor que el monto original
    if (amount > montoOriginal) {
        amount = montoOriginal;
        document.getElementById('monedero_error').innerText = "El descuento no puede ser mayor que el monto de la compra";
        document.getElementById('monedero_error').style.display = "block";
    } else {
        document.getElementById('monedero_error').style.display = "none";
    }
    
    // Actualizar variables y UI
    montoMonedero = amount;
    document.getElementById('monedero_amount').innerText = amount.toFixed(2);
    document.getElementById('monedero-descuento-valor').innerText = '-$' + amount.toFixed(2);
    document.getElementById('monedero_usado_input').value = amount;
    
    // Calcular el nuevo total (monto original + envío - descuento monedero)
    const nuevoTotal = (montoOriginal + 50) - amount;
    document.getElementById('total-final').innerText = '$' + nuevoTotal.toFixed(2);
    
    // Actualizar el campo oculto con el nuevo monto a pagar
    document.getElementById('monto_input').value = nuevoTotal;
    
    checkFormValid();
}

function checkFormValid() {
    const metodo = document.querySelector('input[name="metodo_pago"]:checked');
    const usarMonedero = document.getElementById('usar_monedero').checked;
    let valid = false;
    
    if (metodo) {
        if (metodo.value === 'sucursal') {
            valid = true;
        } else if (metodo.value === 'tarjeta') {
            const tarjeta = document.querySelector('input[name="id_tarjeta"]:checked');
            valid = tarjeta !== null;
        }
    }
    
    // Si se usa monedero como único método de pago, verificar que cubra todo el monto
    if (usarMonedero && !metodo && montoMonedero >= montoOriginal) {
        valid = true;
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
        updatePaymentStatus('Verificando información...');
        
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

// Inicializar la página
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar variables globales
    montoOriginal = <?= $monto ?>;
    montoTotal = <?= $monto?>; // Incluye envío
    monederoDisponible = <?= $monedero_disponible ?>;
    
    // Configurar el rango máximo del slider de monedero
    const monederoSlider = document.getElementById('monedero_slider');
    monederoSlider.max = Math.min(monederoDisponible, montoOriginal);
    
    // Deshabilitar checkbox de monedero si no hay saldo
    if (monederoDisponible <= 0) {
        document.getElementById('usar_monedero').disabled = true;
    }
});
</script>
</body>
<?php include('../Nav/footer.php'); ?>

</html>