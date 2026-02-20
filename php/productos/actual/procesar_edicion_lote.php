<?php
include("../conexion.php");
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_lote = (int) ($_POST['id_lote'] ?? 0);
    $codigo_barra = $_POST['codigo_barra'] ?? '';
    $cantidad_anterior = (int) ($_POST['cantidad_anterior'] ?? 0);
    $cantidad_nueva = (int) ($_POST['cantidad'] ?? 0);
    $fecha_caducidad = !empty($_POST['fecha_caducidad']) ? $_POST['fecha_caducidad'] : NULL;
    
    // 🔥 VALIDACIONES
    if ($id_lote <= 0 || empty($codigo_barra)) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit();
    }
    
    if ($cantidad_nueva < 0) {
        echo json_encode(['success' => false, 'message' => 'La cantidad no puede ser negativa']);
        exit();
    }
    
    // Calcular diferencia de cantidad
    $diferencia = $cantidad_nueva - $cantidad_anterior;
    
    // Obtener precios del producto
    $stmt = mysqli_prepare($conn, "SELECT precio_compra, precio_venta, cantidad FROM productos WHERE codigo_barra = ?");
    mysqli_stmt_bind_param($stmt, "s", $codigo_barra);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $producto = mysqli_fetch_assoc($result);
    
    if (!$producto) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        exit();
    }
    
    $precio_compra = (float) $producto['precio_compra'];
    $precio_venta = (float) $producto['precio_venta'];
    $cantidad_actual = (int) $producto['cantidad'];
    
    // 🔥 VALIDAR QUE NO QUEDE STOCK NEGATIVO
    if (($cantidad_actual + $diferencia) < 0) {
        echo json_encode(['success' => false, 'message' => 'No hay suficiente stock para esta operación']);
        exit();
    }
    
    // Calcular totales a actualizar
    $total_compra_diferencia = $diferencia * $precio_compra;
    $total_venta_diferencia = $diferencia * $precio_venta;
    
    // 🔥 USAR TRANSACCIÓN
    mysqli_begin_transaction($conn);
    
    try {
        // Actualizar lote
        $stmt2 = mysqli_prepare($conn, "UPDATE lotes SET cantidad = ?, fecha_caducidad = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt2, "isi", $cantidad_nueva, $fecha_caducidad, $id_lote);
        $update_lote_ok = mysqli_stmt_execute($stmt2);
        
        if (!$update_lote_ok) {
            throw new Exception('Error al actualizar lote');
        }
        
        // Actualizar producto con GREATEST para evitar negativos
        $stmt3 = mysqli_prepare($conn, "
            UPDATE productos 
            SET 
                cantidad = GREATEST(0, cantidad + ?),
                total_compra = GREATEST(0, total_compra + ?),
                total_venta = GREATEST(0, total_venta + ?)
            WHERE codigo_barra = ?
        ");
        mysqli_stmt_bind_param($stmt3, "ddds", $diferencia, $total_compra_diferencia, $total_venta_diferencia, $codigo_barra);
        $update_producto_ok = mysqli_stmt_execute($stmt3);
        
        if (!$update_producto_ok) {
            throw new Exception('Error al actualizar producto');
        }
        
        mysqli_commit($conn);
        
        // Obtener valores actualizados
        $stmt4 = mysqli_prepare($conn, "SELECT cantidad, total_compra, total_venta FROM productos WHERE codigo_barra = ?");
        mysqli_stmt_bind_param($stmt4, "s", $codigo_barra);
        mysqli_stmt_execute($stmt4);
        $result4 = mysqli_stmt_get_result($stmt4);
        $producto_actualizado = mysqli_fetch_assoc($result4);
        
        // ✅ ÉXITO
        echo json_encode([
            'success' => true,
            'message' => 'Lote actualizado correctamente',
            'tipo' => 'actualizado',
            'codigo' => $codigo_barra,
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