<?php
include '../conexion.php';
$id = $_GET['id'];
$result = $conexion->query("SELECT nombre, imagen FROM snack WHERE id_salon = $id");
$data = [];
while ($row = $result->fetch_assoc()) $data[] = $row;
echo json_encode($data);
?>
