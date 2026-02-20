<?php
include("../conexion.php");
$codigo = $_GET['codigo'] ?? '';
$nombre = $_GET['nombre'] ?? '';

// Obtener precio de compra y proveedor del producto
$producto_info = mysqli_query($conn, "SELECT precio_compra, proveedor FROM productos WHERE codigo_barra = '" . mysqli_real_escape_string($conn, $codigo) . "'");
$producto = mysqli_fetch_assoc($producto_info);
$precio_default = $producto ? $producto['precio_compra'] : 0;
$proveedor_default = $producto ? $producto['proveedor'] : '';
?>
<div style="padding: 20px; background: #f8f9fa; border-radius: 8px;">
<h4 style="margin-top:0; color:#0d6efd; text-align:center; font-size: 18px;">
<i class="bi bi-plus-circle"></i> Agregar Compras a: <?= htmlspecialchars($nombre) ?>
</h4>
<form id="formAgregarLote">
<input type="hidden" name="codigo_barra" value="<?= htmlspecialchars($codigo) ?>">

<div style="margin:15px 0;">
    <label style="display:block; margin-bottom:8px; font-weight:bold; color:#333; font-style: italic;">
        🏪 Proveedor:
    </label>
    <input type="text" name="proveedor"
           style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:14px;"
           value="<?= htmlspecialchars($proveedor_default) ?>"
           placeholder="Seleccionar proveedor">
</div>

<div style="margin:15px 0;">
    <label style="display:block; margin-bottom:8px; font-weight:bold; color:#333; font-style: italic;">
        📦 Cantidad:
    </label>
    <input type="text" name="cantidad" min="0.01" step="0.01" required
           style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:14px;"
           value="10" 
           oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
           placeholder="0.00">
</div>

<div style="margin:15px 0;">
    <label style="display:block; margin-bottom:8px; font-weight:bold; color:#333; font-style: italic;">
        💰 Precio de compra:
    </label>
    <input type="text" name="precio_compra" min="0" step="0.01" required
           style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:14px;"
           placeholder="0.00" 
           oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
</div>

<div style="margin-top:20px; text-align:right; display:flex; gap:10px; justify-content:flex-end;">
    <button type="submit"
            style="background:#28a745; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-size:14px; font-weight:bold;">
        <i class="bi bi-check-lg"></i> Agregar
    </button>
    <button type="button" onclick="cerrarFormularioLote()"
            style="background:#6c757d; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-size:14px; font-weight:bold;">
        <i class="bi bi-x-lg"></i> Cancelar
    </button>
</div>
</form>
</div>