<?php
require_once __DIR__ . '/conexion.php';  // Asegúrate de incluir la conexión

$db = new conexion();
$pdo = $db->getConnection();

if (isset($_GET['id'])) {
    $id_salon = $_GET['id'];  // Obtén el ID del salón desde la solicitud
    $stmt = $pdo->prepare("SELECT * FROM salon WHERE id_salon = ?");
    $stmt->execute([$id_salon]);
    $salon = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($salon) {
        echo json_encode($salon);  // Retorna la información del salón en formato JSON
    } else {
        echo json_encode(["error" => "No se encontró el salón."]);  // Si no se encuentra el salón
    }
} else {
    echo json_encode(["error" => "ID del salón no proporcionado."]);  // Si no se pasa el ID
}
?>
