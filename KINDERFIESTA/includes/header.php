<?php
//includes/header.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KINDERfiesta - Reseñas de Salones Infantiles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="estilos.css">


</head>
<body>
    <header>
        <a href="index.php" class="logo">
            <span>KINDER</span>fiesta</a>

        <input type="checkbox" id="menu-bar">
        <label for="menu-bar" class="fas fa-bars"></label>
        <nav class="navbar">
            <a href="#inicio">inicio</a>
            <a href="#salones">salones</a>
            <a href="#acerca-de">acerca de</a>
            <a href="#contactos">contactos</a>
            <a href="#" onclick="mostrarLogin()">login</a>
        </nav>
    </header>

  