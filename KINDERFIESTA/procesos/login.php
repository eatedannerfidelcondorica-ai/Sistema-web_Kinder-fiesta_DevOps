<?php
// procesos/login.php
header('Content-Type: application/json; charset=utf8');
// Permite solicitudes POST únicamente
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// Inicia la sesión de PHP si aún no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Incluir la conexión a la base de datos
require_once __DIR__ . '/../conexion.php'; 

// 2. Obtener y sanear los datos del formulario
$inputUsuario = $_POST['usuario'] ?? '';
$inputClave = $_POST['password'] ?? ''; // El campo del formulario es 'password'

// Verificar que se hayan enviado los datos
if (empty($inputUsuario) || empty($inputClave)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, introduce usuario y contraseña.']);
    exit;
}

try {
    $database = new conexion();
    $pdo = $database->getConnection();

    // 3. Preparar la consulta SQL para la tabla 'administrador'
    // Se usa 'usuario' y 'clave' según tu tabla de la respuesta anterior.
    $stmt = $pdo->prepare("SELECT id_adm, usuario, clave FROM administrador WHERE usuario = ?");
    $stmt->execute([$inputUsuario]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. Verificar el administrador y la clave
    if ($admin && ($inputClave === $admin['clave'] /* O usa password_verify para seguridad */)) {
        // Credenciales correctas
        
        // 5. Crear la sesión del administrador
        $_SESSION['loggedin'] = true;
        $_SESSION['id_adm'] = $admin['id_adm'];
        $_SESSION['usuario'] = $admin['usuario'];
        $_SESSION['rol'] = 'administrador'; // Rol clave para el header
        
        // Retornar éxito y la URL de redirección
        echo json_encode([
            'success' => true,
            'message' => '¡Bienvenido administrador!',
            'redirect' => 'admin.php' // URL a la que se redirigirá el JS
        ]);

    } else {
        // Usuario o contraseña incorrectos
        echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos.']);
    }

} catch(PDOException $e) {
    // Manejo de errores de conexión o consulta a la BD
    error_log("Error en login: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor. Inténtalo más tarde.']);
}
?>