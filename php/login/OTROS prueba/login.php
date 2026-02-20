<?php
session_start();

$host = "localhost";
$dbname = "losnietos";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);

        $stmt = $pdo->prepare("SELECT id, username, password, rol FROM usuarios WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            // Guardar datos en sesión
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["rol"] = $user["rol"];

            // Redirigir según rol
            if ($user["rol"] === "admin") {
                header("Location: dashboard_admin.php");
            } else {
                header("Location: dashboard_empleado.php");
            }
            exit;
        } else {
            echo "<p style='color:red; text-align:center;'>Usuario o contraseña incorrectos.</p>";
        }
    }
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}
