<?php
require_once __DIR__ . '/../conexion.php';
$db = new conexion();
$pdo = $db->getConnection();

$id = $_GET['id'] ?? 0;

// Info básica
$stmt = $pdo->prepare("SELECT * FROM salon WHERE id_salon = ?");
$stmt->execute([$id]);
$salon = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$salon) {
    echo json_encode(['error' => 'Salón no encontrado']);
    exit;
}

// Decoración
$stmtDeco = $pdo->prepare("SELECT tipo FROM decoracion WHERE id_salon = ?");
$stmtDeco->execute([$id]);
$decoraciones = $stmtDeco->fetchAll(PDO::FETCH_COLUMN);

// Comidas
$stmtComida = $pdo->prepare("SELECT categoria, nombre, imagen FROM comidas WHERE id_salon = ?");
$stmtComida->execute([$id]);
$comidas = $stmtComida->fetchAll(PDO::FETCH_ASSOC);

// Snacks
$stmtSnack = $pdo->prepare("SELECT nombre, imagen FROM snacks WHERE id_salon = ?");
$stmtSnack->execute([$id]);
$snacks = $stmtSnack->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'capacidad' => $salon['capacidad_maxima'],
    'decoracion' => $decoraciones,
    'comidas' => $comidas,
    'snacks' => $snacks
]);