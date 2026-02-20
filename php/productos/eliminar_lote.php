<?php
include("../conexion.php");
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_lote = (int) ($_POST['id_lote'] ?? 0);
    
    if ($id_lote <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de lote inválido']);
        exit();
    }

    // 🔥 USAR TRANSACCIÓN
    mysqli_begin_transaction($conn);
    
    try {
        // Obtener datos del lote antes de eliminar
        $stmt = mysqli_prepare($conn, "SELECT codigo_barra, cantidad, precio_compra FROM lotes WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_lote);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $lote = mysqli_fetch_assoc($result);
        
        if (!$lote) {
            throw new Exception('Lote no encontrado');
        }
        
        $codigo_barra = $lote['codigo_barra'];
        $cantidad_lote = (int) $lote['cantidad'];
        $precio_compra_lote = (float) $lote['precio_compra'];

        // Obtener precios del producto
        $stmt2 = mysqli_prepare($conn, "SELECT precio_venta, cantidad FROM productos WHERE codigo_barra = ?");
        mysqli_stmt_bind_param($stmt2, "s", $codigo_barra);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);
        $producto = mysqli_fetch_assoc($result2);
        
        if (!$producto) {
            throw new Exception('Producto no encontrado');
        }
        
        $precio_venta = (float) $producto['precio_venta'];
        $cantidad_actual = (int) $producto['cantidad'];

        // 🔥 VALIDAR QUE NO QUEDE NEGATIVO
        if (($cantidad_actual - $cantidad_lote) < 0) {
            throw new Exception('No se puede eliminar: quedaría stock negativo');
        }

        // Calcular totales a restar
        $total_compra_restar = $cantidad_lote * $precio_compra_lote;
        $total_venta_restar = $cantidad_lote * $precio_venta;

        // Eliminar lote
        $stmt3 = mysqli_prepare($conn, "DELETE FROM lotes WHERE id = ?");
        mysqli_stmt_bind_param($stmt3, "i", $id_lote);
        $delete_ok = mysqli_stmt_execute($stmt3);
        
        if (!$delete_ok) {
            throw new Exception('Error al eliminar lote');
        }

        // Actualizar producto con GREATEST para evitar negativos
        $stmt4 = mysqli_prepare($conn, "
            UPDATE productos
            SET
            cantidad = GREATEST(0, cantidad - ?),
            total_compra = GREATEST(0, total_compra - ?),
            total_venta = GREATEST(0, total_venta - ?)
            WHERE codigo_barra = ?
        ");
        mysqli_stmt_bind_param($stmt4, "ddds", $cantidad_lote, $total_compra_restar, $total_venta_restar, $codigo_barra);
        $update_ok = mysqli_stmt_execute($stmt4);
        
        if (!$update_ok) {
            throw new Exception('Error al actualizar producto');
        }

        mysqli_commit($conn);

        // Obtener valores actualizados
        $stmt5 = mysqli_prepare($conn, "SELECT cantidad, total_compra, total_venta FROM productos WHERE codigo_barra = ?");
        mysqli_stmt_bind_param($stmt5, "s", $codigo_barra);
        mysqli_stmt_execute($stmt5);
        $result5 = mysqli_stmt_get_result($stmt5);
        $producto_actualizado = mysqli_fetch_assoc($result5);

        // ✅ ÉXITO
        echo json_encode([
            'success' => true,
            'message' => 'Lote eliminado correctamente',
            'tipo' => 'eliminado',
            'codigo' => $codigo_barra,
            'cantidad_eliminada' => $cantidad_lote,
            'cantidad_total' => (int) $producto_actualizado['cantidad'],
            'total_compra' => (float) $producto_actualizado['total_compra'],
            'total_venta' => (float) $producto_actualizado['total_venta']
        ]);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Método no permitido']);
?>