<?php
// procesos/guardar_resena.php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/../conexion.php';

try {
    $database = new conexion();
    $pdo = $database->getConnection();

    // Validar y sanitizar datos
    $idSalon = isset($_POST['id_salon']) ? (int)$_POST['id_salon'] : 0;
    $nombreUsuario = isset($_POST['nombreUsuario']) ? trim($_POST['nombreUsuario']) : '';
    $estrellas = isset($_POST['estrellas']) ? (int)$_POST['estrellas'] : 0;
    $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

    // Validaciones básicas
    if ($idSalon <= 0) {
        throw new Exception('ID de salón inválido');
    }

    if (empty($nombreUsuario) || strlen($nombreUsuario) > 100) {
        throw new Exception('Nombre inválido');
    }

    if ($estrellas < 1 || $estrellas > 5) {
        throw new Exception('Calificación inválida');
    }

    if (empty($comentario) || strlen($comentario) < 10 || strlen($comentario) > 1000) {
        throw new Exception('Comentario debe tener entre 10 y 1000 caracteres');
    }

    // Lista de palabras prohibidas
    //$prohibidas = ['mierda', 'carajo', 'mrd', 'puta']; // agrega las que quieras
    //foreach ($prohibidas as $palabra) {
      //  if (stripos($nombreUsuario, $palabra) !== false || stripos($comentario, $palabra) !== false) {
        //    throw new Exception('El comentario contiene palabras no permitidas.');
       // }
   // }

    // Verificar que el salón exista
    $stmt = $pdo->prepare("SELECT id_salon FROM salon WHERE id_salon = ?");
    $stmt->execute([$idSalon]);
    if (!$stmt->fetch()) {
        throw new Exception('Salón no encontrado');
    }

    // Evitar reseñas con el mismo nombre
    $stmt = $pdo->prepare("SELECT id_calificacion FROM calificacion WHERE id_salon = ? AND nombreUsuario = ?");
    $stmt->execute([$idSalon, $nombreUsuario]);
    if ($stmt->fetch()) {
        throw new Exception('Ya existe una reseña con este nombre. Usa otro nombre.');
    }

    // Insertar la reseña
    $stmt = $pdo->prepare("INSERT INTO calificacion (id_salon, nombreUsuario, comentario, estrellas) VALUES (?, ?, ?, ?)");
    $stmt->execute([$idSalon, $nombreUsuario, $comentario, $estrellas]);

    echo json_encode(['success' => true, 'message' => 'Reseña guardada correctamente']);

} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
