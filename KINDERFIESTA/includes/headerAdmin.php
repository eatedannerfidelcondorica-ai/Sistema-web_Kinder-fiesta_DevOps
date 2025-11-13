<?php
// includes/header.php
// Asegúrate de iniciar la sesión aquí si no lo haces al principio del index.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$esAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador';
$nombreUsuario = $esAdmin ? htmlspecialchars($_SESSION['usuario']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>KINDERfiesta</title>
    <link rel="stylesheet" href="estilos.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">
            <span>KINDER</span>fiesta</a>
    <nav class="navbar">
        <a href="#inicio">Inicio</a>
        <a href="#salones">Salones</a>
        <?php if ($esAdmin): ?>
            <a href="admin.php" class="admin-link">Panel Admin (<?php echo $nombreUsuario; ?>)</a>
            <a href="procesos/logout.php" class="logout-link">Cerrar Sesión</a>
        <?php else: ?>
            <a href="#" onclick="mostrarModal('modalLogin')">Acceder</a>
        <?php endif; ?>
    </nav>
</header>