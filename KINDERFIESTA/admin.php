<?php
// admin.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/conexion.php'; 
include 'includes/headerAdmin.php'; // Incluye el header con el link de admin
?>

<section class="admin-panel">
    <h1 class="titulo-admin"><span style="color:red;">Panel de </span> Administración</h1>
    <p style="font-size: 15px;">Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?>. Aquí puedes gestionar los salones.</p>

    <div class="form-registro-salon">
        <center><h1>Registrar Nuevo Salón</h1></center>
        <form id="formNuevoSalon" onsubmit="registrarSalon(event)" enctype="multipart/form-data"> <div class="form-resena" >
                <input type="text" name="nombre" placeholder="Nombre del Salón" required>
                <input type="tel" name="telefono" placeholder="Teléfono" required>
                <input type="text" name="direccion" placeholder="Dirección" required>
                <textarea name="descripcion" placeholder="Descripción breve..." required></textarea>
                <input type="text" name="latitud" placeholder="Latitud (ej: -16.531234)" required>
                <input type="text" name="longitud" placeholder="Longitud (ej: -68.151234)" required>
                
                <label for="imagen_salon" style="display: block; margin-top: 10px; margin-bottom: 5px; font-weight: bold;">
                    Seleccionar Imagen del Salón (Máx. 2MB, JPG/PNG):
                </label>
                <input type="file" id="imagen_salon" name="imagen_salon" accept="image/jpeg, image/png" required style="padding: 10px; border: 1px solid #ccc; width: 100%; box-sizing: border-box;">
                <button type="submit">Guardar Salón</button>
            </div>
        </form>
        <p id="registroSalonMessage" class="mensaje"></p>
    </div>

</section>

<script>
/**
 * Función AJAX para registrar un nuevo salón, incluyendo la imagen.
 */
function registrarSalon(e) {
    e.preventDefault();
    const form = e.target;
    // FormData automáticamente captura los datos del formulario, incluyendo el archivo
    const formData = new FormData(form); 
    const messageElement = document.getElementById('registroSalonMessage');
    
    // Validación básica del archivo antes de enviar
    const imagenInput = document.getElementById('imagen_salon');
    if (imagenInput.files.length > 0) {
        const file = imagenInput.files[0];
        // Ejemplo de validación de tamaño (2MB = 2 * 1024 * 1024 bytes)
        const MAX_SIZE = 2097152; 

        if (file.size > MAX_SIZE) {
            messageElement.className = 'mensaje mensaje-error';
            messageElement.textContent = 'El archivo es demasiado grande. Máximo 2MB.';
            return; // Detener el envío
        }
    } else {
         messageElement.className = 'mensaje mensaje-error';
         messageElement.textContent = 'Debe seleccionar una imagen para el salón.';
         return;
    }

    messageElement.className = 'mensaje mensaje-info';
    messageElement.textContent = 'Guardando salón...';

    // Para peticiones con FormData que incluyen archivos, 
    // fetch se encargará de establecer el Content-Type correcto (multipart/form-data)
    fetch('procesos/guardar_salon.php', {
        method: 'POST',
        body: formData // Aquí va el objeto FormData que incluye el archivo
    })
    .then(resp => {
        // Verificar si la respuesta fue un JSON válido antes de parsear
        const contentType = resp.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            return resp.json();
        } else {
             // Si no es JSON, lanza un error para el bloque catch
            throw new Error('Respuesta del servidor no es JSON o hubo un error inesperado. Revisa guardar_salon.php.');
        }
    })
    .then(data => {
        if (data.success) {
            messageElement.className = 'mensaje mensaje-exito';
            messageElement.textContent = data.message || 'Salón registrado con éxito.';
            form.reset(); // Limpiar el formulario
        } else {
            messageElement.className = 'mensaje mensaje-error';
            messageElement.textContent = data.message || 'Error al registrar el salón.';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        messageElement.className = 'mensaje mensaje-error';
        messageElement.textContent = error.message || 'Error de conexión al servidor.';
    });
}
</script>

<?php include 'includes/footer.php'; ?>