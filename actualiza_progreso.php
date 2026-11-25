<?php
ini_set('session.gc_maxlifetime', (string) (30 * 24 * 60 * 60));
session_start();

header("Content-Type: application/json");

$conexion = new mysqli("localhost", "root", "", "duotomi_dulce");

if ($conexion->connect_error) {
    echo json_encode([
        "success" => false,
        "mensaje" => "Error de conexión: " . $conexion->connect_error
    ]);
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        "success" => false,
        "mensaje" => "Debes iniciar sesión"
    ]);
    exit;
}

$datos = json_decode(file_get_contents("php://input"), true);
$puntajeGanado = isset($datos["experienciaGanada"]) ? (int)$datos["experienciaGanada"] : 0;
$idUsuario = (int)$_SESSION["id_usuario"];

// 1. Verificar si el usuario ya tiene un registro en la tabla progreso
$check = $conexion->prepare("SELECT id_usuario FROM progreso WHERE id_usuario = ?");
if (!$check) {
    echo json_encode([
        "success" => false,
        "mensaje" => "Error en consulta SELECT: " . $conexion->error
    ]);
    exit;
}

$check->bind_param("i", $idUsuario);
$check->execute();
$resCheck = $check->get_result();

if ($resCheck->num_rows > 0) {
    // 2A. Si ya existe, actualizamos su progreso
    $sql = "UPDATE progreso 
            SET experiencia = experiencia + ?, 
                racha = racha + 1, 
                porcentaje = LEAST(porcentaje + 5, 100) 
            WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        echo json_encode(["success" => false, "mensaje" => "Error SQL UPDATE: " . $conexion->error]);
        exit;
    }
    $stmt->bind_param("ii", $puntajeGanado, $idUsuario);
} else {
    // 2B. Si no existe, creamos su primer registro en progreso
    $sql = "INSERT INTO progreso (id_usuario, experiencia, racha, porcentaje) 
            VALUES (?, ?, 1, 5)";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        echo json_encode(["success" => false, "mensaje" => "Error SQL INSERT: " . $conexion->error]);
        exit;
    }
    $stmt->bind_param("ii", $idUsuario, $puntajeGanado);
}

$resultado = $stmt->execute();

if ($resultado) {
    echo json_encode([
        "success" => true,
        "mensaje" => "Progreso guardado"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "mensaje" => "Error al ejecutar: " . $stmt->error
    ]);
}

$check->close();
$stmt->close();
$conexion->close();
?>
