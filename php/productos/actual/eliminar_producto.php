<?php
include("../conexion.php");

// 🔥 VALIDAR QUE EXISTA EL ID
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $codigo_barra = mysqli_real_escape_string($conn, $_GET['id']);
    
    // 🔥 1. OBTENER LA IMAGEN ANTES DE ELIMINAR
    $sql_imagen = "SELECT imagen FROM productos WHERE codigo_barra = '$codigo_barra'";
    $resultado_imagen = mysqli_query($conn, $sql_imagen);
    
    if ($resultado_imagen && mysqli_num_rows($resultado_imagen) > 0) {
        $fila = mysqli_fetch_assoc($resultado_imagen);
        $imagen = $fila['imagen'];
        
        // 🔥 2. ELIMINAR EL ARCHIVO DE IMAGEN SI EXISTE
        if (!empty($imagen)) {
            $ruta_imagen = "../../img/PRODUCTOS/" . $imagen;
            
            // Verificar si el archivo existe físicamente
            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen); // ✅ Eliminar archivo
            }
        }
        
        // 🔥 3. ELIMINAR EL REGISTRO DE LA BASE DE DATOS
        $sql_delete = "DELETE FROM productos WHERE codigo_barra = '$codigo_barra'";
        if (mysqli_query($conn, $sql_delete)) {
            // ✅ Éxito
            header("Location: CRUD-Product.php?eliminado=1");
            exit();
        } else {
            echo "<script>
            alert('❌ Error al eliminar: " . mysqli_error($conn) . "');
            window.history.back();
            </script>";
            exit();
        }
    } else {
        echo "<script>
        alert('❌ Producto no encontrado');
        window.location.href='CRUD-Product.php';
        </script>";
        exit();
    }
} else {
    echo "<script>
    alert('❌ Código de producto inválido');
    window.location.href='CRUD-Product.php';
    </script>";
    exit();
}
?>