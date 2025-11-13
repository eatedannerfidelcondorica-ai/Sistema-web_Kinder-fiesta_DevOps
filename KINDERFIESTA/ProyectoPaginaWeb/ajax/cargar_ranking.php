<?php
require_once __DIR__ . '/../conexion.php'; // Ajusta la ruta si es necesario
$db = new conexion();
$pdo = $db->getConnection();

// Obtener todas las guarderías con promedio de estrellas
$stmt = $pdo->query("
    SELECT s.nombre, s.direccion, s.telefono, 
           IFNULL(AVG(c.estrellas), 0) AS promedio, COUNT(c.id_calificacion) AS totalResenas
    FROM salon s
    LEFT JOIN calificacion c ON s.id_salon = c.id_salon
    GROUP BY s.id_salon
    ORDER BY promedio DESC
");

$guarderias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mostrar tabla
echo '<table style="width:100%; border-collapse: collapse;">';
echo '<thead>';
echo '<tr style="background-color:#0c443a; color:white;">';
echo '<th>#</th>';
echo '<th>Guardería</th>';
echo '<th>Promedio ⭐</th>';
echo '<th>Total Reseñas</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

$pos = 1;
foreach ($guarderias as $g) {
    echo '<tr style="text-align:center; border-bottom:1px solid #ccc;">';
    echo '<td>' . $pos++ . '</td>';
    echo '<td style="text-align:left; padding-left:10px;">' . htmlspecialchars($g['nombre']) . '</td>';
    echo '<td>' . round($g['promedio'], 1) . '</td>';
    echo '<td>' . $g['totalResenas'] . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
?>