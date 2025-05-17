<?php
include '../BD/ConexionBD.php';
include ('../Nav/header.php');

$sql = "SELECT 
            p.id_pedido,
            pq.nombre_paqueteria,
            pq.descripcion,
            e.tipo_envio,
            e.fecha_estimada,
            c.fecha AS fecha_carrito,
            p.precio_total_pedido,
            cl.nom_persona,
            cl.apellido_paterno,
            cl.apellido_materno,
            cl.telefono
        FROM pedido p
        JOIN paqueteria pq ON p.id_paqueteria = pq.id_paqueteria
        JOIN envio e ON p.id_envio = e.id_envio
        JOIN carrito c ON p.id_carrito = c.id_carrito
        JOIN cliente cl ON c.id_cliente = cl.id_cliente
        ORDER BY p.id_pedido DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos - Paquetería</title>
    <link rel="stylesheet" href="estilos_pedidos.css">
</head>
<body>
    <h2>Listado de Pedidos para Paquetería</h2>
    <table>
        <tr>
            <th>ID Pedido</th>
            <th class="descripcion">Paquetería</th>
            <th class="descripcion">Descripción</th>
            <th>Tipo Envío</th>
            <th>Fecha Estimada</th>
            <th>Fecha del Carrito</th>
            <th class="cliente">Cliente</th>
            <th class="telefono">Teléfono</th>
            <th>Total Pedido</th>
        </tr>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id_pedido']}</td>
                        <td class='descripcion'>{$row['nombre_paqueteria']}</td>
                        <td class='descripcion'>{$row['descripcion']}</td>
                        <td>{$row['tipo_envio']}</td>
                        <td>{$row['fecha_estimada']}</td>
                        <td>{$row['fecha_carrito']}</td>
                        <td class='cliente'>{$row['nom_persona']} {$row['apellido_paterno']} {$row['apellido_materno']}</td>
                        <td class='telefono'>{$row['telefono']}</td>
                        <td>\${$row['precio_total_pedido']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='9'>No hay pedidos registrados</td></tr>";
        }
        $conn->close();
        ?>
    </table>

    <?php
include '../Nav/footer.php';
?>
</body>
</html>
