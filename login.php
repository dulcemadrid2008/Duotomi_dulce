<?php
session_start();

// Datos de conexión
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "duotomi_dulce";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Datos del formulario
$email = $_POST['email'] ?? '';
$pass = $_POST['contrasena'] ?? '';

// Consulta segura
$stmt = $conn->prepare("SELECT * FROM usuario WHERE email = ? AND contrasena = ?");
$stmt->bind_param("ss", $email, $pass);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();

    $_SESSION['usuario'] = $usuario['email'];
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nombre'] = $usuario['nombre'];

    $stmt->close();
    $conn->close();
    header("Location: unidad_animales.html");
    exit();
} else {
    $stmt->close();
    $conn->close();
    header("Location: login.html?error=1");
    exit();
}
?>
