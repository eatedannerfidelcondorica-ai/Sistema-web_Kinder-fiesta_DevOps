<?php
// ajax/cargar_resenas.php
header('Content-Type: text/html; charset=utf8');

// Lista de palabras a censurar (puedes expandir esta lista)
// Se usa un array simple para un ejemplo básico, para algo más robusto se usaría regex y un diccionario más grande.
const PALABRAS_MALSONANTES = [
    'carajo',
    'puta',
    'mierda',
    'caca',
    'cabrón',
    'gilipollas',
    'coño',
    'joder',
    'chingada',
    'pendejo',
    'pene',
    'perro',
    'cojudo',
    'cojuda',
    'puto',
    // Agrega más palabras en español aquí
];

/**
 * Función para censurar palabras malsonantes en una cadena de texto.
 * Reemplaza cada palabra por una cadena de asteriscos (*) de la misma longitud.
 *
 * @param string $texto El texto a filtrar.
 * @return string El texto filtrado.
 */
function censurarTexto(string $texto): string {
    $textoCensurado = $texto;
    $palabras = PALABRAS_MALSONANTES;

    // Crear un patrón de búsqueda para expresiones regulares
    // i: case-insensitive (ignorar mayúsculas/minúsculas)
    // \b: word boundary (limita a palabras completas)
    $patrones = [];
    $reemplazos = [];

    foreach ($palabras as $palabra) {
        // La expresión regular busca la palabra completa
        $patron = '/\b' . preg_quote($palabra, '/') . '\b/i';
        
        // Crear la cadena de asteriscos de la misma longitud
        $reemplazo = str_repeat('*', mb_strlen($palabra));

        $patrones[] = $patron;
        $reemplazos[] = $reemplazo;
    }

    // Usar preg_replace para reemplazar todas las ocurrencias
    return preg_replace($patrones, $reemplazos, $textoCensurado);
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<p class="mensaje mensaje-error">Salón no válido.</p>';
    exit;
}

$idSalon = (int)$_GET['id'];
require_once __DIR__ . '/../conexion.php';

try {
    $database = new conexion();
    $pdo = $database->getConnection();

    // ... (El código de verificación de salón y cálculo de promedio se mantiene igual)

    // Verificar que el salón exista
    $stmt = $pdo->prepare("SELECT nombre FROM salon WHERE id_salon = ?");
    $stmt->execute([$idSalon]);
    $salon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$salon) {
        echo '<p class="mensaje mensaje-error">Salón no encontrado.</p>';
        exit;
    }

    // Calcular promedio
    $stmt = $pdo->prepare("SELECT AVG(estrellas) as promedio, COUNT(*) as total FROM calificacion WHERE id_salon = ?");
    $stmt->execute([$idSalon]);
    $califInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    $promedio = $califInfo['promedio'] ? round($califInfo['promedio'], 1) : 0;
    $totalReseñas = $califInfo['total'];

    // Generar estrellas
    $estrellasHTML = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $promedio) {
            $estrellasHTML .= '<i class="fas fa-star"></i>';
        } elseif ($i - 0.5 <= $promedio) {
            $estrellasHTML .= '<i class="fas fa-star-half-alt"></i>';
        } else {
            $estrellasHTML .= '<i class="far fa-star"></i>';
        }
    }

    echo '<div style="text-align: center; margin-bottom: 20px;">';
    echo '<div class="promedio-estrellas">' . $estrellasHTML . '</div>';
    echo '<div class="promedio-calificacion">' . $promedio . '/5</div>';
    echo '<div style="color: #666;">(' . $totalReseñas . ' reseñas)</div>';
    echo '</div>';

    // Obtener reseñas
    $stmt = $pdo->prepare("SELECT nombreUsuario, comentario, estrellas, fechaRegistro FROM calificacion WHERE id_salon = ? ORDER BY fechaRegistro DESC");
    $stmt->execute([$idSalon]);
    $resenas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($resenas)) {
        echo '<div class="resenas-container">';
        echo '<p style="text-align: center; color: #666;">No hay reseñas aún. ¡Sé el primero en dejar una!</p>';
        echo '</div>';
    } else {
        echo '<div class="resenas-container">';
        foreach ($resenas as $resena) {
            // APLICAR CENSURA AL NOMBRE Y COMENTARIO
            $nombreCensurado = censurarTexto($resena['nombreUsuario']);
            $comentarioCensurado = censurarTexto($resena['comentario']);

            // Formatear fecha
            $fecha = new DateTime($resena['fechaRegistro']);
            $fechaFormateada = $fecha->format('d/m/Y H:i');

            // Generar estrellas para esta reseña
            $estrellasResena = '';
            for ($i = 1; $i <= 5; $i++) {
                $estrellasResena .= ($i <= $resena['estrellas']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
            }

            echo '<div class="resena">';
            echo '<div class="resena-header">';
            // Mostrar el nombre CENSURADO y aplicar htmlspecialchars
            echo '<span class="resena-nombre">' . htmlspecialchars($nombreCensurado) . '</span>'; 
            echo '<span class="resena-estrellas">' . $estrellasResena . '</span>';
            echo '</div>';
            // Mostrar el comentario CENSURADO y aplicar htmlspecialchars
            echo '<div class="resena-comentario">' . htmlspecialchars($comentarioCensurado) . '</div>'; 
            echo '<div style="font-size: 1.2rem; color: #999; margin-top: 5px;">' . $fechaFormateada . '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    // ... (El formulario para nueva reseña se mantiene igual)
    
    // Formulario para nueva reseña
    echo '<div class="form-resena">';
    echo '<h3 style="color: #0c443a; margin-bottom: 15px;">Deja tu reseña</h3>';
    echo '<form onsubmit="enviarResena(event, ' . $idSalon . ')" data-nombre="' . htmlspecialchars($salon['nombre']) . '">';
    echo '<input type="hidden" name="id_salon" value="' . $idSalon . '">';
    echo '<input type="text" name="nombreUsuario" placeholder="Tu nombre" required>';
    echo '<select name="estrellas" required>';
    echo '<option value="">Selecciona tu calificación</option>';
    for ($i = 5; $i >= 1; $i--) {
        echo '<option value="' . $i . '">' . $i . ' estrellas</option>';
    }
    echo '</select>';
    echo '<textarea name="comentario" placeholder="Escribe tu comentario..." required></textarea>';
    echo '<button type="submit">Enviar reseña</button>';
    echo '</form>';
    echo '</div>';

} catch(PDOException $e) {
    echo '<p class="mensaje mensaje-error">Error al cargar las reseñas: ' . $e->getMessage() . '</p>';
}
?>