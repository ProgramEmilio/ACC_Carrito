<?php
include('../../BD/ConexionBD.php');

$id_usuario = $_GET['id'];
$conn->begin_transaction();

try {
    // Obtener el ID del cliente relacionado al usuario
    $stmt = $conn->prepare("SELECT id_cliente FROM cliente WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $cliente = $result->fetch_assoc();

    if ($cliente) {
        $id_cliente = $cliente['id_cliente'];

        // Obtener todos los carritos del cliente
        $stmt = $conn->prepare("SELECT id_carrito FROM carrito WHERE id_cliente = ?");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $carritos_result = $stmt->get_result();

        while ($carrito = $carritos_result->fetch_assoc()) {
            $id_carrito = $carrito['id_carrito'];

            // Obtener los pedidos relacionados con el carrito
            $stmt2 = $conn->prepare("SELECT id_pedido FROM pedido WHERE id_carrito = ?");
            $stmt2->bind_param("i", $id_carrito);
            $stmt2->execute();
            $pedidos_result = $stmt2->get_result();

            while ($pedido = $pedidos_result->fetch_assoc()) {
                $id_pedido = $pedido['id_pedido'];

                // Eliminar pagos relacionados con el pedido
                $stmt3 = $conn->prepare("DELETE FROM pago WHERE id_pedido = ?");
                $stmt3->bind_param("i", $id_pedido);
                $stmt3->execute();

                // Eliminar pedido
                $stmt3 = $conn->prepare("DELETE FROM pedido WHERE id_pedido = ?");
                $stmt3->bind_param("i", $id_pedido);
                $stmt3->execute();
            }

            // Eliminar detalles del carrito
            $stmt2 = $conn->prepare("DELETE FROM detalle_carrito WHERE id_carrito = ?");
            $stmt2->bind_param("i", $id_carrito);
            $stmt2->execute();

            // Eliminar carrito
            $stmt2 = $conn->prepare("DELETE FROM carrito WHERE id_carrito = ?");
            $stmt2->bind_param("i", $id_carrito);
            $stmt2->execute();
        }

        // Eliminar compras relacionadas al cliente
        $stmt = $conn->prepare("DELETE FROM compra WHERE id_cliente = ?");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();

        // Eliminar tarjetas
        $stmt = $conn->prepare("DELETE FROM tarjeta WHERE titular = ?");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();

        // Eliminar direcciones
        $stmt = $conn->prepare("DELETE FROM direccion WHERE id_cliente = ?");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();

        // Eliminar cliente
        $stmt = $conn->prepare("DELETE FROM cliente WHERE id_cliente = ?");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
    }

    // Eliminar como proveedor (detalle_articulos)
    $stmt = $conn->prepare("DELETE FROM detalle_articulos WHERE id_proveedor = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    // Eliminar usuario
    $stmt = $conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $conn->commit();
    header("Location: ../Usuarios.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error al eliminar: " . $e->getMessage();
}
?>
