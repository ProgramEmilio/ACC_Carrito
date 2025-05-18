<?php
// Incluir archivo de conexión a la base de datos
include('../BD/ConexionBD.php');// Ajusta la ruta según tu estructura

// Iniciar sesión si no está iniciada
session_start();

$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    echo "Usuario no autenticado.";
    exit;
}

// Obtener cliente
$queryCliente = "SELECT id_cliente FROM cliente WHERE id_usuario = ?";
$stmtCliente = $conn->prepare($queryCliente);
$stmtCliente->bind_param('i', $id_usuario);
$stmtCliente->execute();
$resultCliente = $stmtCliente->get_result();
$cliente = $resultCliente->fetch_assoc();
$id_cliente = $cliente['id_cliente'] ?? null;

if (!$id_cliente) {
    echo "Cliente no encontrado.";
    exit;
}

// Verificar que se recibieron los datos por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validar y sanitizar datos recibidos
    $id_articulo = isset($_POST['id_articulo']) ? trim($_POST['id_articulo']) : '';
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;
    $personalizacion = isset($_POST['personalizacion']) ? trim($_POST['personalizacion']) : '';
    
    // Validar datos obligatorios
    if (empty($id_articulo) || $precio <= 0 || $cantidad <= 0) {
        $_SESSION['mensaje_error'] = "Datos inválidos. Por favor, intenta nuevamente.";
        header("Location: Home.php");
        exit();
    }
    
    try {
        // Comenzar transacción
        $conn->begin_transaction();
        
        // Verificar existencia del artículo y stock disponible
        $sql_verificar = "SELECT d.existencia, d.precio, d.estatus 
                         FROM articulos a
                         INNER JOIN detalle_articulos d ON a.id_detalle_articulo = d.id_detalle_articulo
                         WHERE a.id_articulo = ? AND d.estatus = 'Disponible'";
        
        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->bind_param("s", $id_articulo);
        $stmt_verificar->execute();
        $resultado_verificar = $stmt_verificar->get_result();
        
        if ($resultado_verificar->num_rows === 0) {
            throw new Exception("El artículo no está disponible.");
        }
        
        $articulo_info = $resultado_verificar->fetch_assoc();
        
        // Verificar stock suficiente
        if ($cantidad > $articulo_info['existencia']) {
            throw new Exception("No hay suficiente stock disponible. Stock actual: " . $articulo_info['existencia']);
        }
        
        // Verificar si el cliente ya tiene un carrito activo
        $sql_carrito = "SELECT id_carrito FROM carrito WHERE id_cliente = ? ORDER BY fecha DESC LIMIT 1";
        $stmt_carrito = $conn->prepare($sql_carrito);
        $stmt_carrito->bind_param("i", $id_cliente);
        $stmt_carrito->execute();
        $resultado_carrito = $stmt_carrito->get_result();
        
        if ($resultado_carrito->num_rows > 0) {
            // Usar carrito existente
            $carrito_info = $resultado_carrito->fetch_assoc();
            $id_carrito = $carrito_info['id_carrito'];
        } else {
            // Crear nuevo carrito
            $sql_nuevo_carrito = "INSERT INTO carrito (id_cliente, fecha, total) VALUES (?, NOW(), 0)";
            $stmt_nuevo_carrito = $conn->prepare($sql_nuevo_carrito);
            $stmt_nuevo_carrito->bind_param("i", $id_cliente);
            
            if (!$stmt_nuevo_carrito->execute()) {
                throw new Exception("Error al crear el carrito.");
            }
            
            $id_carrito = $conn->insert_id;
            $stmt_nuevo_carrito->close();
        }
        
        // Verificar si el artículo con la misma personalización ya está en el carrito
        // Consideramos NULL y cadena vacía como equivalentes para personalización
        if (empty($personalizacion)) {
            $sql_existe_articulo = "SELECT id_detalle_carrito, cantidad FROM detalle_carrito 
                                   WHERE id_carrito = ? AND id_articulo = ? AND (personalizacion IS NULL OR personalizacion = '')";
            $stmt_existe_articulo = $conn->prepare($sql_existe_articulo);
            $stmt_existe_articulo->bind_param("is", $id_carrito, $id_articulo);
        } else {
            $sql_existe_articulo = "SELECT id_detalle_carrito, cantidad FROM detalle_carrito 
                                   WHERE id_carrito = ? AND id_articulo = ? AND personalizacion = ?";
            $stmt_existe_articulo = $conn->prepare($sql_existe_articulo);
            $stmt_existe_articulo->bind_param("iss", $id_carrito, $id_articulo, $personalizacion);
        }
        $stmt_existe_articulo->execute();
        $resultado_existe = $stmt_existe_articulo->get_result();
        
        $importe = $precio * $cantidad;
        
        if ($resultado_existe->num_rows > 0) {
            // Actualizar cantidad existente
            $detalle_existente = $resultado_existe->fetch_assoc();
            $nueva_cantidad = $detalle_existente['cantidad'] + $cantidad;
            $nuevo_importe = $precio * $nueva_cantidad;
            
            $sql_actualizar = "UPDATE detalle_carrito 
                              SET cantidad = ?, importe = ?
                              WHERE id_detalle_carrito = ?";
            $stmt_actualizar = $conn->prepare($sql_actualizar);
            $stmt_actualizar->bind_param("ddi", $nueva_cantidad, $nuevo_importe, $detalle_existente['id_detalle_carrito']);
            
            if (!$stmt_actualizar->execute()) {
                throw new Exception("Error al actualizar el artículo en el carrito.");
            }
            $stmt_actualizar->close();
        } else {
            // Insertar nuevo artículo al carrito
            $sql_insertar = "INSERT INTO detalle_carrito (id_carrito, id_articulo, cantidad, precio, importe, personalizacion) 
                            VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insertar = $conn->prepare($sql_insertar);
            $personalizacion_param = empty($personalizacion) ? null : $personalizacion;
            $stmt_insertar->bind_param("isddds", $id_carrito, $id_articulo, $cantidad, $precio, $importe, $personalizacion_param);
            
            if (!$stmt_insertar->execute()) {
                throw new Exception("Error al agregar el artículo al carrito.");
            }
            $stmt_insertar->close();
        }
        
        // Actualizar total del carrito
        $sql_actualizar_total = "UPDATE carrito 
                                SET total = (
                                    SELECT SUM(importe) 
                                    FROM detalle_carrito 
                                    WHERE id_carrito = ?
                                )
                                WHERE id_carrito = ?";
        $stmt_actualizar_total = $conn->prepare($sql_actualizar_total);
        $stmt_actualizar_total->bind_param("ii", $id_carrito, $id_carrito);
        
        if (!$stmt_actualizar_total->execute()) {
            throw new Exception("Error al actualizar el total del carrito.");
        }
        $stmt_actualizar_total->close();
        
        // Cerrar statements
        $stmt_verificar->close();
        $stmt_carrito->close();
        $stmt_existe_articulo->close();
        
        // Confirmar transacción
        $conn->commit();
        
        // Mensaje de éxito especificando la personalización si aplica
        if (!empty($personalizacion)) {
            $_SESSION['mensaje_exito'] = "Artículo agregado al carrito exitosamente con personalización: " . $personalizacion . ".";
        } else {
            $_SESSION['mensaje_exito'] = "Artículo agregado al carrito exitosamente.";
        }
        
        // Redirigir de vuelta a la página anterior
        header("Location: Home.php");
        exit();
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        
        // Mensaje de error
        $_SESSION['mensaje_error'] = "Error: " . $e->getMessage();
        
        // Redirigir de vuelta a la página anterior
        header("Location: Home.php");
        exit();
    }
    
} else {
    // Si no se recibieron datos por POST, redirigir
    header("Location: Home.php");
    exit();
}

// Cerrar conexión
$conn->close();
?>