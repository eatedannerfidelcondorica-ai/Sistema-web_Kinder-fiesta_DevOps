<?php
// procesos/logout.php

// 1. Asegúrate de que la sesión está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Destruir TODAS las variables de sesión
// Esto es opcional, pero buena práctica:
$_SESSION = array();

// 3. Destruir la cookie de sesión (si existe)
// Esto requiere eliminar la cookie de la sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finalmente, destruir la sesión
session_destroy();

// 5. Redirigir al usuario a la página de inicio
header('Location: ../index.php'); // Usamos ../ para salir de la carpeta 'procesos'
exit;
?>