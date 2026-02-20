<?php
include("../conexion.php");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = $_POST['codigo_barra'] ?? '';
    $cantidad = (int) ($_POST['cantidad'] ?? 0);
    $caducidad = !empty($_POST['fecha_caducidad']) ? $_POST['fecha_caducidad'] : NULL;
    
    // 🔥 VALIDAR CANTIDAD POSITIVA
    if ($cantidad <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La cantidad debe ser mayor a 0']);
        exit();
    }
    
    // Validar que el producto exista
    $escaped_codigo = mysqli_real_escape_string($conn, $codigo);
    $existe = mysqli_query($conn, "SELECT precio_compra, precio_venta FROM productos WHERE codigo_barra = '$escaped_codigo'");
    if (mysqli_num_rows($existe) === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        exit();
    }
    $producto = mysqli_fetch_assoc($existe);
    $precio_compra = (float) $producto['precio_compra'];
    $precio_venta = (float) $producto['precio_venta'];
    
    // Insertar lote
    $sql_insert = "INSERT INTO lotes (codigo_barra, cantidad, fecha_caducidad) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt, "sis", $codigo, $cantidad, $caducidad);
    $insert_ok = mysqli_stmt_execute($stmt);
    if (!$insert_ok) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al insertar lote']);
        exit();
    }
    
    // Calcular nuevos valores
    $nuevo_total_compra = $cantidad * $precio_compra;
    $nuevo_total_venta = $cantidad * $precio_venta;
    
    // 🔥 USAR TRANSACCIÓN PARA SEGURIDAD
    mysqli_begin_transaction($conn);
    
    try {
        // Actualizar productos: sumar los nuevos valores
        $sql_update = "
        UPDATE productos
        SET
        cantidad = cantidad + ?,
        total_compra = total_compra + ?,
        total_venta = total_venta + ?
        WHERE codigo_barra = ?
        ";
        $stmt2 = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt2, "ddds", $cantidad, $nuevo_total_compra, $nuevo_total_venta, $codigo);
        $update_ok = mysqli_stmt_execute($stmt2);
        
        if (!$update_ok) {
            throw new Exception('Error al actualizar producto');
        }
        
        mysqli_commit($conn);
        
        // 🔥 OBTENER VALORES TOTALES ACTUALIZADOS DEL PRODUCTO
        $stmt3 = mysqli_prepare($conn, "SELECT cantidad, total_compra, total_venta FROM productos WHERE codigo_barra = ?");
        mysqli_stmt_bind_param($stmt3, "s", $codigo);
        mysqli_stmt_execute($stmt3);
        $result3 = mysqli_stmt_get_result($stmt3);
        $producto_actualizado = mysqli_fetch_assoc($result3);
        
        // ✅ Éxito - Retornar valores TOTALES
        echo json_encode([
            'success' => true,
            'message' => 'Lote agregado correctamente',
            'tipo' => 'agregado',
            'codigo' => $codigo,
            'cantidad_agregada' => $cantidad,
            'cantidad_total' => (int) $producto_actualizado['cantidad'],
            'total_compra' => (float) $producto_actualizado['total_compra'],
            'total_venta' => (float) $producto_actualizado['total_venta']
        ]);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
    exit();
}
?>