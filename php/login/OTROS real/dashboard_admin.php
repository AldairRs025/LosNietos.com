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
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Administrador</title>

    <!-- CSS PRINCIPAL -->
    <link rel="stylesheet" href="../../css/AdminPanel-STYLE/styles2.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    <!-- BARRA SUPERIOR -->
    <div class="barra-superior">
        <div class="logo">Los Nietos&#174;</div>

        <div class="iconos">
            <a href="../login/dashboard_admin.php" class="icon-btn">
                <i class="bi bi-house-fill"></i>
                <span class="tooltip">INICIO</span>
            </a>

            <a href="usuarios.php" class="icon-btn">
                <i class="bi bi-people-fill"></i>
                <span class="tooltip">USUARIOS</span>
            </a>

            <!-- <a href="proveedores.php" class="icon-btn">
                <i class="bi bi-truck"></i>
                <span class="tooltip">PROVEEDORES</span>
            </a> -->

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

    <!-- CONTENIDO -->
    <div class="contenedor">

        <div class="tarjeta">
            <div class="titulo">Productos</div>

            <p>Administrar productos.</p>
            <a class="btn" href="../productos/CRUD-Product.php">IR</a>

            <p>Traspasos.</p>
            <a class="btn" href="#">IR</a>
        </div>

        <div class="tarjeta">
            <div class="titulo">Proveedores</div>

            <p>Registros de cambios.</p>
            <a class="btn" href="../ventas/CRUD-Ventas.php">IR</a>
        </div>

        <div class="tarjeta">
            <div class="titulo">Ventas</div>

            <p>Administrar ventas.</p>
            <a class="btn" href="../ventas/CRUD-Ventas.php">IR</a>
        </div>

        <div class="tarjeta">
            <div class="titulo">Corte de Caja</div>

            <p>Realizar corte de hoy</p>
            <a class="btn" href="#">IR</a>

            <p>Buscar otros...</p>
            <a class="btn" href="#">IR</a>
        </div>

    </div>

    <!-- BARRA INFERIOR -->
    <div class="barra-inferior"></div>

</body>

</html>