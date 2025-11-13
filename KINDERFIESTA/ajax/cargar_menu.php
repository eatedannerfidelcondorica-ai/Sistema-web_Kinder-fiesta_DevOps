<?php
include '../conexion.php';
$id = $_GET['id'];
$result = $conexion->query("SELECT tipo, nombre, imagen FROM menu WHERE id_salon = $id");
$data = [];
while ($row = $result->fetch_assoc()) $data[] = $row;
echo json_encode($data);
?>
