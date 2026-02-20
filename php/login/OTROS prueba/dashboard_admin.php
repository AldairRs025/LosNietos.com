<?php
session_start();
// Validación de sesión
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../index.html");
    exit;
}
// Validación de rol
if ($_SESSION["rol"] !== "admin") {
    header("Location: dashboard_empleado.php");
    exit;
}

// 🔥 CONEXIÓN A BASE DE DATOS
include("../conexion.php");

// 🔥 OBTENER DATOS DEL RESUMEN
$resumenSQL = "
SELECT
    COUNT(codigo_barra) AS total_productos,
    SUM(cantidad) AS total_unidades,
    SUM(total_compra) AS inversion_total,
    SUM(total_venta) AS venta_total,
    SUM(total_venta - total_compra) AS ganancia_total,
    SUM(CASE WHEN cantidad <= stock_minimo THEN 1 ELSE 0 END) AS stock_bajo
FROM productos
";
$resumen = mysqli_fetch_assoc(mysqli_query($conn, $resumenSQL));

// 🔥 OBTENER ACTIVIDAD RECIENTE (últimos 10 productos)
$actividadSQL = "
SELECT 
    nombre_producto,
    fecha_ingreso,
    cantidad,
    total_venta,
    estado
FROM productos 
ORDER BY fecha_ingreso DESC 
LIMIT 10
";
$actividad = mysqli_query($conn, $actividadSQL);

// 🔥 OBTENER DATOS PARA GRÁFICA (últimos 6 meses)
$graficaSQL = "
SELECT 
    DATE_FORMAT(fecha_ingreso, '%Y-%m') as mes,
    COUNT(*) as productos_nuevos,
    SUM(total_venta) as ventas_totales,
    SUM(total_compra) as compras_totales
FROM productos 
WHERE fecha_ingreso >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(fecha_ingreso, '%Y-%m')
ORDER BY mes ASC
";
$grafica = mysqli_query($conn, $graficaSQL);
$labelsGrafica = [];
$dataVentas = [];
$dataCompras = [];
while ($row = mysqli_fetch_assoc($grafica)) {
    $labelsGrafica[] = date('M', strtotime($row['mes']));
    $dataVentas[] = $row['ventas_totales'] ?? 0;
    $dataCompras[] = $row['compras_totales'] ?? 0;
}

// 🔥 CALCULAR PORCENTAJES DE CRECIMIENTO
$crecimientoVentas = 10;
$crecimientoAnual = 20;
$crecimientoIngresos = 5;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Dashboard - Los Nietos®</title>

    <!-- CSS DASHBOARD -->
    <link rel="stylesheet" href="../../css/AdminPanel-STYLE/dashboard-style.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Chart.js para gráficas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <!-- ===============================
    SIDEBAR
    ================================-->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">Los Nietos®</div>
            <div class="sidebar-user">
                <i class="bi bi-person-circle"></i>
                <div class="sidebar-user-info">
                    <h4><?= htmlspecialchars($_SESSION["username"]); ?></h4>
                    <p>👑 Administrador</p>
                </div>
            </div>
        </div>

        <nav class="sidebar-menu">
            <div class="sidebar-menu-title">📊 Navegación</div>

            <a href="dashboard_admin.php" class="sidebar-menu-item activo">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            <div class="sidebar-divider"></div>

            <div class="sidebar-menu-title">📦 Inventario</div>

            <!-- Productos con Submenú -->
            <div class="sidebar-menu-item" onclick="toggleSubmenu('submenu-productos')">
                <i class="bi bi-box-seam"></i>
                <span>Productos</span>
                <i class="bi bi-chevron-down flecha-submenu"></i>
            </div>
            <div id="submenu-productos" class="sidebar-submenu">
                <a href="../productos/CRUD-Product.php" class="sidebar-submenu-item">
                    <i class="bi bi-circle"></i> Administrar Productos
                </a>
                <a href="traspasos.php" class="sidebar-submenu-item">
                    <i class="bi bi-circle"></i> Traspasos
                </a>
                <a href="entrada_lotes.php" class="sidebar-submenu-item">
                    <i class="bi bi-circle"></i> Entrada de Lotes
                </a>
            </div>

            <!-- Ventas con Submenú -->
            <div class="sidebar-menu-item" onclick="toggleSubmenu('submenu-ventas')">
                <i class="bi bi-cart-fill"></i>
                <span>Ventas</span>
                <i class="bi bi-chevron-down flecha-submenu"></i>
            </div>
            <div id="submenu-ventas" class="sidebar-submenu">
                <a href="../ventas/CRUD-Ventas.php" class="sidebar-submenu-item">
                    <i class="bi bi-circle"></i> Registrar Venta
                </a>
                <a href="historial_ventas.php" class="sidebar-submenu-item">
                    <i class="bi bi-circle"></i> Historial de Ventas
                </a>
                <a href="devoluciones.php" class="sidebar-submenu-item">
                    <i class="bi bi-circle"></i> Devoluciones
                </a>
            </div>

            <div class="sidebar-divider"></div>

            <div class="sidebar-menu-title">💰 Finanzas</div>

            <!-- Caja con Submenú -->
            <div class="sidebar-menu-item" onclick="toggleSubmenu('submenu-caja')">
                <i class="bi bi-cash-coin"></i>
                <span>Caja</span>
                <i class="bi bi-chevron-down flecha-submenu"></i>
            </div>
            <div id="submenu-caja" class="sidebar-submenu">
                <a href="caja.php" class="sidebar-submenu-item">
                    <i class="bi bi-circle"></i> Corte del Día
                </a>
                <a href="historial_caja.php" class="sidebar-submenu-item">
                    <i class="bi bi-circle"></i> Historial de Caja
                </a>
            </div>

            <div class="sidebar-divider"></div>

            <div class="sidebar-menu-title">👥 Administración</div>

            <a href="usuarios.php" class="sidebar-menu-item">
                <i class="bi bi-people-fill"></i>
                <span>Usuarios</span>
            </a>

            <a href="proveedores.php" class="sidebar-menu-item">
                <i class="bi bi-truck"></i>
                <span>Proveedores</span>
            </a>

            <div class="sidebar-divider"></div>

            <div class="sidebar-menu-title">⚙️ Sistema</div>

            <a href="configuracion.php" class="sidebar-menu-item">
                <i class="bi bi-gear-fill"></i>
                <span>Configuración</span>
            </a>

            <a href="logout.php" class="sidebar-menu-item" style="color: #ffc107;">
                <i class="bi bi-box-arrow-right"></i>
                <span>Cerrar Sesión</span>
            </a>
        </nav>
    </aside>

    <!-- ===============================
    CONTENIDO PRINCIPAL
    ================================-->
    <div class="contenido-principal">

        <!-- BARRA SUPERIOR -->
        <div class="barra-superior">
            <div class="logo">Los Nietos®</div>
            <div class="iconos">
                <a href="dashboard_admin.php" class="icon-btn">
                    <i class="bi bi-house-fill"></i>
                    <span class="tooltip">INICIO</span>
                </a>
                <a href="usuarios.php" class="icon-btn">
                    <i class="bi bi-people-fill"></i>
                    <span class="tooltip">USUARIOS</span>
                </a>
                <a href="../productos/CRUD-Product.php" class="icon-btn">
                    <i class="bi bi-box-seam"></i>
                    <span class="tooltip">PRODUCTOS</span>
                </a>
                <a href="caja.php" class="icon-btn">
                    <i class="bi bi-cash-coin"></i>
                    <span class="tooltip">CAJA</span>
                </a>
                <a href="logout.php" class="icon-btn">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="tooltip">SALIR</span>
                </a>
            </div>
        </div>

        <!-- CONTENIDO DASHBOARD -->
        <div class="dashboard-container">

            <!-- HEADER -->
            <div class="dashboard-header">
                <div>
                    <h1>👋 ¡Bienvenido de vuelta, <?= htmlspecialchars($_SESSION["username"]); ?>!</h1>
                    <p>Panel de Administración</p>
                </div>
                <div class="fecha-actual">
                    <p>📅 <?= date('d/m/Y'); ?></p>
                    <p>🕐 <?= date('H:i'); ?> hrs</p>
                </div>
            </div>

            <!-- CARDS DE RESUMEN -->
            <div class="cards-resumen">
                <div class="card">
                    <div class="card-icon productos">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="card-info">
                        <h3><?= $resumen['total_productos'] ?? 0; ?></h3>
                        <p>Productos</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-icon ventas">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="card-info">
                        <h3>$<?= number_format($resumen['venta_total'] ?? 0, 2); ?></h3>
                        <p>Venta Total</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-icon ganancia">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div class="card-info">
                        <h3>$<?= number_format($resumen['ganancia_total'] ?? 0, 2); ?></h3>
                        <p>Ganancia</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-icon stock">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="card-info">
                        <h3><?= $resumen['stock_bajo'] ?? 0; ?></h3>
                        <p>Stock Bajo</p>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN PRINCIPAL -->
            <div class="seccion-principal">

                <!-- GRÁFICA DE ESTADO DE RESULTADO -->
                <div class="panel">
                    <div class="panel-header">
                        <h3>📊 Estado de Resultado</h3>
                        <div class="filtros-grafica">
                            <button class="btn-filtro">Mensual</button>
                            <button class="btn-filtro">Cuatrimestre</button>
                            <button class="btn-filtro activo">Anual</button>
                        </div>
                    </div>
                    <div class="grafica-container">
                        <canvas id="graficaEstadoResultado"></canvas>
                    </div>

                    <!-- EXPORTAR REPORTES (SIN EMAIL) -->
                    <div class="exportar-botones">
                        <a href="exportar_reporte.php?tipo=pdf" class="btn-exportar btn-pdf">
                            <i class="bi bi-file-earmark-pdf"></i>
                            <span>PDF</span>
                        </a>
                        <a href="exportar_reporte.php?tipo=excel" class="btn-exportar btn-excel">
                            <i class="bi bi-file-earmark-excel"></i>
                            <span>Excel</span>
                        </a>
                    </div>
                </div>

                <!-- ACTIVIDAD RECIENTE -->
                <div class="panel">
                    <div class="panel-header">
                        <h3>🕐 Actividad Reciente</h3>
                        <a href="actividad.php" class="ver-mas">Ver más →</a>
                    </div>
                    <div class="actividad-lista">
                        <?php
                        while ($act = mysqli_fetch_assoc($actividad)) {
                            echo '
                        <div class="actividad-item">
                            <div class="actividad-icono">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div class="actividad-info">
                                <h4>' . htmlspecialchars($act['nombre_producto']) . '</h4>
                                <p>📦 ' . $act['cantidad'] . ' unidades | $' . number_format($act['total_venta'], 2) . '</p>
                            </div>
                            <div class="actividad-fecha">
                                ' . date('d/m H:i', strtotime($act['fecha_ingreso'])) . '
                            </div>
                        </div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- MÉTRICAS LATERALES -->
            <div class="seccion-principal">

                <!-- MÉTRICAS DE VENTAS -->
                <div class="panel">
                    <div class="panel-header">
                        <h3>📈 Métricas de Ventas</h3>
                    </div>
                    <div class="metricas-laterales">
                        <div class="metrica-item">
                            <div>
                                <h4>Ventas del Mes</h4>
                                <div class="valor">$<?= number_format($resumen['venta_total'] ?? 0, 2); ?></div>
                            </div>
                            <div class="porcentaje subida">
                                <?= $crecimientoVentas ?>% ↑
                            </div>
                        </div>

                        <div class="metrica-item">
                            <div>
                                <h4>Ventas del Último Año</h4>
                                <div class="valor">$<?= number_format(($resumen['venta_total'] ?? 0) * 12, 2); ?></div>
                            </div>
                            <div class="porcentaje subida">
                                <?= $crecimientoAnual ?>% ↑
                            </div>
                        </div>

                        <div class="metrica-item">
                            <div>
                                <h4>Ingresos del Mes</h4>
                                <div class="valor">$<?= number_format($resumen['ganancia_total'] ?? 0, 2); ?></div>
                            </div>
                            <div class="porcentaje subida">
                                <?= $crecimientoIngresos ?>% ↑
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ===============================
    SCRIPTS
    ================================-->
    <script>
        // 🔥 Toggle Submenú
        function toggleSubmenu(id) {
            const submenu = document.getElementById(id);
            const parent = submenu.previousElementSibling;

            // Cerrar todos los submenús
            document.querySelectorAll('.sidebar-submenu').forEach(sm => {
                if (sm.id !== id) {
                    sm.classList.remove('abierto');
                    sm.previousElementSibling.classList.remove('activo');
                }
            });

            // Alternar el seleccionado
            submenu.classList.toggle('abierto');
            parent.classList.toggle('activo');
        }

        // 🔥 GRÁFICA COMBINADA (BARRAS + LÍNEA)
        const ctx = document.getElementById('graficaEstadoResultado').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labelsGrafica); ?>,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Ingresos',
                        data: <?= json_encode($dataVentas); ?>,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1,
                        order: 2
                    },
                    {
                        type: 'line',
                        label: 'Egresos',
                        data: <?= json_encode($dataCompras); ?>,
                        borderColor: 'rgba(13, 110, 253, 1)',
                        backgroundColor: 'rgba(13, 110, 253, 0.2)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                family: "'Kalam', cursive",
                                size: 14
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('es-MX', {
                                        style: 'currency',
                                        currency: 'MXN'
                                    }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            callback: function (value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // 🔥 FILTROS DE GRÁFICA
        document.querySelectorAll('.btn-filtro').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.btn-filtro').forEach(b => {
                    b.classList.remove('activo');
                });
                this.classList.add('activo');
            });
        });
    </script>

</body>

</html>