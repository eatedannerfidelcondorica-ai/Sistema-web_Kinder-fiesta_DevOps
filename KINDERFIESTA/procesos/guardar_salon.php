<?php
// procesos/guardar_salon.php
header('Content-Type: application/json; charset=utf8');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Configuración y Verificación de Acceso
if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

require_once __DIR__ . '/../conexion.php'; 

// Directorio donde se guardarán las imágenes (Asegúrate de que esta carpeta exista y tenga permisos de escritura)
$target_dir = __DIR__ . "/../img/"; 
// Asegúrate de que el directorio exista
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}


// 2. Obtener y validar datos
$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
// Se utiliza htmlspecialchars para prevenir XSS al mostrar el nombre en el mensaje de éxito.
$nombre_seguro = htmlspecialchars($nombre); 

// Sanear Latitud y Longitud
$latitud = filter_var($_POST['latitud'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$longitud = filter_var($_POST['longitud'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

// Validar que el archivo de imagen haya sido subido
$file_uploaded = isset($_FILES['imagen_salon']) && $_FILES['imagen_salon']['error'] === UPLOAD_ERR_OK;

if (empty($nombre) || empty($telefono) || empty($direccion) || empty($descripcion) || empty($latitud) || empty($longitud) || !$file_uploaded) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos y la imagen son obligatorios.']);
    exit;
}

try {
    $database = new conexion();
    $pdo = $database->getConnection();
    $pdo->beginTransaction(); // Iniciar transacción para asegurar atomicidad (salón + foto)

    // ====================================================================
    // A. GUARDAR DATOS DEL SALÓN
    // ====================================================================
    $sql_salon = "INSERT INTO salon (nombre, telefono, direccion, descripcion, latitud, longitud) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt_salon = $pdo->prepare($sql_salon);
    
    $exito_salon = $stmt_salon->execute([
        $nombre, 
        $telefono, 
        $direccion, 
        $descripcion, 
        $latitud, 
        $longitud
    ]);

    if (!$exito_salon) {
        throw new Exception("Error al insertar el salón.");
    }

    $id_salon = $pdo->lastInsertId(); // Obtener el ID del salón recién insertado

    // ====================================================================
    // B. PROCESAR Y GUARDAR IMAGEN
    // ====================================================================
    
    $file_tmp_name = $_FILES['imagen_salon']['tmp_name'];
    $file_name = basename($_FILES['imagen_salon']['name']); // Nombre original
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Generar un nombre de archivo único para evitar colisiones
    // (Ej: salon_15_5f8a7e3d1a4b2.jpg)
    $unique_name = "salon" . $id_salon . "." . $file_ext;
    $target_file = $target_dir . $unique_name;
    
    // Ruta que se guardará en la base de datos (relativa a la raíz web)
    $url_foto_db = $unique_name; 

    // Mover el archivo subido de la carpeta temporal al destino final
    if (!move_uploaded_file($file_tmp_name, $target_file)) {
        throw new Exception("Error al mover el archivo de imagen subido.");
    }

    // ====================================================================
    // C. GUARDAR REGISTRO DE LA FOTO EN LA TABLA `foto`
    // ====================================================================

    $sql_foto = "INSERT INTO foto (id_salon, url_foto) VALUES (?, ?)";
    $stmt_foto = $pdo->prepare($sql_foto);
    
    $exito_foto = $stmt_foto->execute([$id_salon, $url_foto_db]);

    if (!$exito_foto) {
        // Opcional: intentar borrar el archivo si falla el registro en BD
        if (file_exists($target_file)) {
            unlink($target_file); 
        }
        throw new Exception("Error al registrar la foto en la base de datos.");
    }

    // Si todo salió bien, confirmar los cambios en la base de datos
    $pdo->commit(); 

    echo json_encode([
        'success' => true, 
        'message' => 'Salón "' . $nombre_seguro . '" y su imagen registrados con éxito.',
        'url_foto' => $url_foto_db // Útil para depuración o actualización en tiempo real
    ]);

} catch(PDOException $e) {
    // Si algo falla, revertir los cambios en la base de datos
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error de BD al registrar salón: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);

} catch(Exception $e) {
    // Si algo falla durante la subida o la lógica, revertir los cambios
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error lógico al registrar salón: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>