<?php
include("../conexion.php");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = $_POST['codigo_barra'];
    $nombre = $_POST['nombre_producto'];
    $marca = $_POST['marca'];
    $categoria = $_POST['categoria'];
    $cantidad = $_POST['cantidad'];
    $precio_compra = $_POST['precio_compra'];
    $precio_venta = $_POST['precio_venta'];
    $estado = $_POST['estado'];
    $stock = $_POST['stock_minimo'];
    $modo = $_POST['modo'];
    
    // 🔥 NUEVAS VARIABLES: proveedor, imagen
    $proveedor = isset($_POST['proveedor']) && !empty($_POST['proveedor']) ? $_POST['proveedor'] : NULL;
    
    // 🔥 MANEJO DE IMAGEN
    $imagen = NULL;
    $imagen_nueva = false; // 🆕 Bandera para saber si hay nueva imagen
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $directorio_destino = "../../img/PRODUCTOS/";
        // Crear directorio si no existe
        if (!file_exists($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }
        $nombre_archivo = basename($_FILES['imagen']['name']);
        $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
        $nombre_unico = uniqid() . '_' . time() . '.' . $extension;
        $ruta_destino = $directorio_destino . $nombre_unico;
        
        // Validar tipo de archivo
        $tipos_permitidos = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if (in_array($extension, $tipos_permitidos)) {
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                $imagen = $nombre_unico;
                $imagen_nueva = true; // 🆕 Hay nueva imagen
            }
        }
    }
    
    // 🔥 Si es modo editar y no hay nueva imagen, mantener la existente
    if ($modo === "editar" && empty($imagen)) {
        $sql_verificar = "SELECT imagen FROM productos WHERE codigo_barra = '" . mysqli_real_escape_string($conn, $codigo) . "'";
        $resultado = mysqli_query($conn, $sql_verificar);
        if ($fila = mysqli_fetch_assoc($resultado)) {
            $imagen = $fila['imagen'];
        }
    }
    
    $total_compra = $cantidad * $precio_compra;
    $total_venta = $cantidad * $precio_venta;
    
    // 🔥 VALIDAR QUE PRECIO VENTA SEA MAYOR
    if ($precio_venta <= $precio_compra && $precio_venta > 0) {
        echo "<script>
        alert('⚠ El precio de venta debe ser mayor que el precio de compra.');
        window.history.back();
        </script>";
        exit();
    }
    
    /* ==============================
    🔹 MODO EDITAR (UPDATE)
    ============================== */
    if ($modo === "editar") {
        // 🔥 OBTENER IMAGEN ANTES DE ACTUALIZAR (para eliminarla si hay nueva)
        $imagen_anterior = NULL;
        if ($imagen_nueva) {
            $sql_imagen_anterior = "SELECT imagen FROM productos WHERE codigo_barra = '" . mysqli_real_escape_string($conn, $codigo) . "'";
            $resultado_anterior = mysqli_query($conn, $sql_imagen_anterior);
            if ($fila_anterior = mysqli_fetch_assoc($resultado_anterior)) {
                $imagen_anterior = $fila_anterior['imagen'];
            }
        }
        
        $sql = "UPDATE productos SET
            nombre_producto = '" . mysqli_real_escape_string($conn, $nombre) . "',
            marca = '" . mysqli_real_escape_string($conn, $marca) . "',
            categoria = '" . mysqli_real_escape_string($conn, $categoria) . "',
            cantidad = '$cantidad',
            precio_compra = '$precio_compra',
            total_compra = '$total_compra',
            precio_venta = '$precio_venta',
            total_venta = '$total_venta',
            estado = '$estado',
            stock_minimo = '$stock',
            proveedor = " . ($proveedor === NULL ? "NULL" : "'" . mysqli_real_escape_string($conn, $proveedor) . "'") . ",
            imagen = " . ($imagen === NULL ? "NULL" : "'" . mysqli_real_escape_string($conn, $imagen) . "'") . "
            WHERE codigo_barra = '" . mysqli_real_escape_string($conn, $codigo) . "'";
        
        if (mysqli_query($conn, $sql)) {
            // 🔥 ELIMINAR IMAGEN ANTERIOR SI HAY NUEVA IMAGEN
            if ($imagen_nueva && !empty($imagen_anterior)) {
                $ruta_imagen_anterior = "../../img/PRODUCTOS/" . $imagen_anterior;
                if (file_exists($ruta_imagen_anterior)) {
                    unlink($ruta_imagen_anterior); // ✅ Eliminar archivo anterior
                }
            }
            
            header("Location: CRUD-Product.php?actualizado=1");
            exit();
        } else {
            echo "<script>
            alert('❌ Error al actualizar: " . mysqli_error($conn) . "');
            window.history.back();
            </script>";
            exit();
        }
    }
    
    /* ==============================
    🔹 MODO CREAR (INSERT)
    ============================== */
    else {
        // 🔥 Validar si ya existe el código
        $verificar = mysqli_query($conn, "SELECT codigo_barra FROM productos WHERE codigo_barra = '" . mysqli_real_escape_string($conn, $codigo) . "'");
        if (mysqli_num_rows($verificar) > 0) {
            echo "<script>
            alert('❌ El código de barras ya existe');
            window.location.href='CRUD-Product.php';
            </script>";
            exit();
        }
        
        $sql = "INSERT INTO productos
            (codigo_barra, nombre_producto, marca, categoria, cantidad, precio_compra, total_compra, 
             precio_venta, total_venta, estado, stock_minimo, proveedor, imagen, fecha_ingreso)
            VALUES
            ('$codigo',
            '" . mysqli_real_escape_string($conn, $nombre) . "',
            '" . mysqli_real_escape_string($conn, $marca) . "',
            '" . mysqli_real_escape_string($conn, $categoria) . "',
            '$cantidad',
            '$precio_compra',
            '$total_compra',
            '$precio_venta',
            '$total_venta',
            '$estado',
            '$stock',
            " . ($proveedor === NULL ? "NULL" : "'" . mysqli_real_escape_string($conn, $proveedor) . "'") . ",
            " . ($imagen === NULL ? "NULL" : "'" . mysqli_real_escape_string($conn, $imagen) . "'") . ",
            NOW())";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: CRUD-Product.php?agregado=1");
            exit();
        } else {
            echo "<script>
            alert('❌ Error al crear: " . mysqli_error($conn) . "');
            window.history.back();
            </script>";
            exit();
        }
    }
}
?>