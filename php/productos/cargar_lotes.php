<?php
include("../conexion.php");
$codigo = $_GET['codigo'] ?? '';
$lotes = mysqli_query($conn, "SELECT * FROM lotes WHERE codigo_barra = '" . mysqli_real_escape_string($conn, $codigo) . "' ORDER BY fecha_ingreso DESC");

if (mysqli_num_rows($lotes) === 0) {
    echo '<tr><td colspan="5" style="text-align:center; padding:30px; color:#666;">
    <i class="bi bi-inbox" style="font-size:40px; display:block; margin-bottom:10px;"></i>
    No hay lotes registrados
    </td></tr>';
} else {
    while ($lote = mysqli_fetch_assoc($lotes)) {
        echo "<tr style='transition:0.2s;'>
        <td style='padding:10px 8px; text-align:center; font-weight:bold;'>{$lote['cantidad']}</td>
        <td style='padding:10px 8px; text-align:center; font-weight:bold;'>$" . number_format($lote['precio_compra'], 2) . "</td>
        <td style='padding:10px 8px; text-align:center;'>" . htmlspecialchars($lote['proveedor'] ?? 'N/A') . "</td>
        <td style='padding:10px 8px; text-align:center;'>" . date('d/m/Y h:i A', strtotime($lote['fecha_ingreso'])) . "</td>
        <td style='padding:10px 8px; text-align:center; white-space:nowrap;'>
        <i class='bi bi-pencil-square btn-editar-lote'
        data-id='{$lote['id']}'
        data-cantidad='{$lote['cantidad']}'
        data-precio_compra='{$lote['precio_compra']}'
        data-proveedor='" . htmlspecialchars($lote['proveedor']) . "'
        style='font-size:16px; margin:0 5px; cursor:pointer; color:#0d6efd;
        transition:0.2s; padding:5px;'
        title='Editar Lote'></i>
        <i class='bi bi-trash btn-eliminar-lote'
        data-id='{$lote['id']}'
        style='font-size:16px; margin:0 5px; cursor:pointer; color:#dc3545;
        transition:0.2s; padding:5px;'
        title='Eliminar Lote'></i>
        </td>
        </tr>";
    }
}
?>