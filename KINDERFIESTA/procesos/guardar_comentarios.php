<?php
require_once __DIR__ . '/conexion.php';
$db = new conexion();
$pdo = $db->getConnection();

// Obtén los datos enviados desde la solicitud AJAX
$data = json_decode(file_get_contents('php://input'), true);

// Validar los datos
if (!isset($data['salon_id'], $data['tipo_comentario'], $data['comentario'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

$salon_id = $data['salon_id'];
$tipo_comentario = $data['tipo_comentario'];
$comentario = $data['comentario'];

// Inserta el comentario en la base de datos
$stmt = $pdo->prepare("INSERT INTO comentarios (salon_id, tipo_comentario, comentario) VALUES (?, ?, ?)");
$stmt->execute([$salon_id, $tipo_comentario, $comentario]);

// Responde con éxito
echo json_encode(['success' => true]);
?>
