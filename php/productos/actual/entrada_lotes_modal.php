<?php
$codigo = $_GET['codigo'] ?? '';
$nombre = $_GET['nombre'] ?? '';
?>
<div>
    <h4 style="margin-top:0; color:#0d6efd; text-align:center;">
        <i class="bi bi-plus-circle"></i> Agregar Lote a: <?= htmlspecialchars($nombre) ?>
    </h4>
    
    <form id="formAgregarLote">
        <input type="hidden" name="codigo_barra" value="<?= htmlspecialchars($codigo) ?>">
        
        <div style="margin:15px 0;">
            <label style="display:block; margin-bottom:8px; font-weight:bold; color:#333;">
                📦 Cantidad:
            </label>
            <input type="number" name="cantidad" min="1" required
                   style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; 
                          font-size:14px;" value="10">
        </div>
        
        <div style="margin:15px 0;">
            <label style="display:block; margin-bottom:8px; font-weight:bold; color:#333;">
                📅 Fecha de Caducidad (opcional):
            </label>
            <input type="date" name="fecha_caducidad"
                   style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; 
                          font-size:14px;" value="<?= date('Y-m-d') ?>">
        </div>
        
        <div style="margin-top:20px; text-align:right; display:flex; gap:10px; justify-content:flex-end;">
            <button type="submit"
                    style="background:#28a745; color:white; border:none; padding:10px 20px; 
                           border-radius:6px; cursor:pointer; font-size:14px; font-weight:bold;">
                <i class="bi bi-check-lg"></i> Agregar
            </button>
            <button type="button" onclick="cerrarFormularioLote()"
                    style="background:#6c757d; color:white; border:none; padding:10px 20px; 
                           border-radius:6px; cursor:pointer; font-size:14px; font-weight:bold;">
                <i class="bi bi-x-lg"></i> Cancelar
            </button>
        </div>
    </form>
</div>