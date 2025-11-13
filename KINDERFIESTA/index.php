<?php
require_once __DIR__ . '/conexion.php';
$db = new conexion();
$pdo = $db->getConnection();

$stmt = $pdo->query("SELECT * FROM salon");
$salones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'includes/header.php'; ?>

<section class="inicio" id="inicio">
    <div class="image">
        <img src="img/logo.png" alt="KINDERfiesta" onerror="this.src='https://placehold.co/400x180/25d1b2/0c443a?text=LOGO'">
    </div>
</section>

<section class="salones" id="salones">
    <h2 class="titulo-salones">Salones de Kinder Fiesta</h2>

    <!-- **Campo de búsqueda - Colócalo justo aquí en la sección de salones** -->
    <div style="text-align:center; margin-bottom:20px;">
        <input type="text" id="buscadorSalones" placeholder="Buscar salón por nombre..." 
               style="padding:8px 12px; width:300px; border-radius:5px; border:1px solid #0c443a;">
    </div>

    <div class="salones-container">
        <?php foreach ($salones as $salon): ?>
            <?php
            $stmtCalif = $pdo->prepare("SELECT AVG(estrellas) as promedio, COUNT(*) as total FROM calificacion WHERE id_salon = ?");
            $stmtCalif->execute([$salon['id_salon']]);
            $califInfo = $stmtCalif->fetch(PDO::FETCH_ASSOC);
            $promedio = $califInfo['promedio'] ? round($califInfo['promedio'], 1) : 0;
            $totalReseñas = $califInfo['total'];

            $urlFoto = 'img/salon' . $salon['id_salon'] . '.jpg';

            $estrellasHTML = '';
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $promedio) $estrellasHTML .= '<i class="fas fa-star"></i>';
                elseif ($i - 0.5 <= $promedio) $estrellasHTML .= '<i class="fas fa-star-half-alt"></i>';
                else $estrellasHTML .= '<i class="far fa-star"></i>';
            }
            ?>
            <div class="salon-card">
                <img src="<?php echo htmlspecialchars($urlFoto); ?>" 
                     alt="<?php echo htmlspecialchars($salon['nombre']); ?>" 
                     onerror="this.src='https://placehold.co/280x180/25d1b2/0c443a?text=<?php echo urlencode($salon['nombre']); ?>'">
                <h3><?php echo htmlspecialchars($salon['nombre']); ?></h3>
                <p>📞 <?php echo htmlspecialchars($salon['telefono']); ?></p>
                <p> <?php echo htmlspecialchars('DESCRIPCION:'); ?></p>
                <p> <?php echo htmlspecialchars($salon['descripcion']); ?></p>
                <p>📍 <?php echo htmlspecialchars(substr($salon['direccion'],0,30)) . (strlen($salon['direccion'])>30?'...':''); ?></p>
                <div class="rating">
                    <div class="promedio-estrellas"><?php echo $estrellasHTML; ?></div>
                    <div class="promedio-calificacion"><?php echo $promedio; ?>/5 (<?php echo $totalReseñas; ?> reseñas)</div>
                </div>
                <a href="https://www.google.com/maps?q=<?php echo $salon['latitud']; ?>,<?php echo $salon['longitud']; ?>" target="_blank" class="btn-ubicacion">Ubicación</a>
                <button class="btn-resenas" onclick="mostrarResenas(<?php echo $salon['id_salon']; ?>)">Ver reseñas</button>
                <button class="btn-mas-info" onclick="mostrarInfoSalon(<?php echo $salon['id_salon']; ?>)"> Más Información</button>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div style="text-align:center; margin-top:30px;">
    <button onclick="mostrarRanking()" 
            style="background-color:#0c443a; color:white; border:none; 
                   padding:10px 20px; border-radius:10px; font-size:16px; cursor:pointer;">
         Ver Ranking de salones infantiles
    </button>
</div>

<!-- Modal de reseñas -->
<div id="modalResenas" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal('modalResenas')">×</span>
        <h2 id="tituloResenas"></h2>
        <div id="contenidoResenas"></div>
    </div>
</div>

<!-- Modal: Información del Salón -->
<div id="modalInfoSalon" class="modal">
    <div class="modal-content" style="width:80%; max-width:900px; font-size:18px;">
        <span class="close" onclick="cerrarModal('modalInfoSalon')">×</span>
        <h2 id="tituloInfoSalon" style="font-size:24px; color:#0c443a;">Nombre del Salón</h2>
        <p id="capacidadSalon" style="font-size:18px; font-weight:bold; margin-bottom:15px;"></p>

        <!-- Botones principales para seleccionar la sección -->
        <div class="info-buttons" style="margin-top:20px;">
            <button onclick="mostrarDecoracion()" style="padding:10px 15px; margin:5px;">Decoración</button>
            <button onclick="mostrarMenu()" style="padding:10px 15px; margin:5px;">Menú de Comidas</button>
            <button onclick="mostrarSnacks()" style="padding:10px 15px; margin:5px;">Snacks</button>
        </div>

        <!-- Contenido principal para mostrar detalles -->
        <div id="contenidoInfoSalon" style="margin-top:15px;">
            <p>Selecciona una opción para ver detalles.</p>
        </div>

        <!-- Contenedores dinámicos para cada sección -->
        <div id="panelDecoracion" style="display:none; margin-top:15px;"></div>
        <div id="panelMenu" style="display:none; margin-top:15px;"></div>
        <div id="panelSnacks" style="display:none; margin-top:15px;"></div>
    </div>
</div>



<!-- Modal ranking -->
<div id="modalRanking" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal('modalRanking')">×</span>
        <h2 style="color:#0c443a;">Ranking de salones infantiles con mas estrellas</h2>
        <div id="contenidoRanking">
            <p>Cargando ranking...</p>
        </div>
    </div>
</div>

<!-- Modallogin -->
<div id="modalLogin" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal('modalLogin')">×</span>
        <h3 style="color: #0c443a; margin-bottom: 20px;">Iniciar Sesión</h3>
        <form id="loginForm" onsubmit="iniciarSesion(event)">
            <div class="form-resena">
                <input type="text" name="usuario" placeholder="Usuario" required style="margin-bottom: 15px;">
                <input type="password" name="password" placeholder="Contraseña" required style="margin-bottom: 20px;">
                <button type="submit" style="width: 100%;">Acceder</button>
            </div>
        </form>
        <p id="loginMessage" style="text-align: center; margin-top: 15px; color: red;"></p>
    </div>
</div>

<script>
    // **Función de búsqueda - Colócalo al final de tu archivo (antes de `</body>`)**

    document.getElementById('buscadorSalones').addEventListener('input', function() {
        const filtro = this.value.toLowerCase();  // Obtener el valor del input de búsqueda y convertirlo a minúsculas
        const tarjetas = document.querySelectorAll('.salon-card');  // Obtener todas las tarjetas de salones

        // **Itera sobre todas las tarjetas y las filtra según el nombre**
        tarjetas.forEach(tarjeta => {
            const nombre = tarjeta.querySelector('h3').textContent.toLowerCase();  // Obtener el nombre del salón y convertirlo a minúsculas
            if (nombre.includes(filtro)) {  // Si el nombre contiene el texto del filtro
                tarjeta.style.display = 'block';  // Mostrar la tarjeta
            } else {
                tarjeta.style.display = 'none';  // Ocultar la tarjeta
            }
        });
    });

    function mostrarResenas(idSalon){
    document.getElementById('modalResenas').style.display = 'block';
    document.getElementById('contenidoResenas').innerHTML = 'Cargando reseñas...';

    fetch('ajax/cargar_resenas.php?id=' + idSalon)
    .then(resp => resp.text())
    .then(html => {
        document.getElementById('contenidoResenas').innerHTML = html;
    })
    .catch(() => {
        document.getElementById('contenidoResenas').innerHTML = '<p class="mensaje mensaje-error">Error al cargar reseñas</p>';
    });
}
function mostrarLogin(){
    document.getElementById('modalLogin').style.display = 'block';
}
/**
 * Función genérica para mostrar un modal.
 * @param {string} idModal El ID del elemento modal (e.g., 'modalLogin', 'modalResenas').
 */
function mostrarModal(idModal) {
    const modal = document.getElementById(idModal);
    if (modal) {
        modal.style.display = 'block';
    }
}

/**
 * Función Corregida para cerrar cualquier modal.
 * Ahora recibe el ID del modal a cerrar.
 * @param {string} idModal El ID del elemento modal (e.g., 'modalLogin', 'modalResenas').
 */
function cerrarModal(idModal){
    const modal = document.getElementById(idModal);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Función para enviar reseña con AJAX
function enviarResena(e, idSalon){
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch('procesos/guardar_resena.php', {
        method: 'POST',
        body: formData
    })
    .then(resp => resp.json())
    .then(data => {
        alert(data.message);
        if(data.success){
            mostrarResenas(idSalon); // recargar reseñas
        }
    })
    .catch(() => alert('Error al enviar la reseña'));
}

/**
 * Función de ejemplo para manejar el inicio de sesión.
 */
// ... (código JavaScript previo)

/**
 * Función que maneja el inicio de sesión vía AJAX.
 */
function iniciarSesion(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const messageElement = document.getElementById('loginMessage');
    messageElement.textContent = 'Verificando...';

    fetch('procesos/login.php', { 
        method: 'POST',
        body: formData
    })
    .then(resp => resp.json())
    .then(data => {
        if (data.success) {
            messageElement.style.color = 'green';
            messageElement.textContent = data.message;
            
            // Redirección del administrador si la URL existe
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect; // Redirigir a admin.php
                }, 500);
            } else {
                // Si no hay redirección específica (p.ej., usuario normal)
                setTimeout(() => {
                    cerrarModal('modalLogin');
                    window.location.reload(); 
                }, 1000);
            }

        } else {
            messageElement.style.color = 'red';
            messageElement.textContent = data.message || 'Error de autenticación.';
        }
    })
    .catch(() => {
        messageElement.style.color = 'red';
        messageElement.textContent = 'Error de conexión al servidor.';
    });
}
function mostrarDecoracion(){
    document.getElementById('panelMenu').style.display = 'none';
    document.getElementById('panelSnacks').style.display = 'none';
    
    const cont = document.getElementById('panelDecoracion');
    cont.style.display = 'block';
    cont.innerHTML = '';

    // Formulario para comentario de Decoración
    cont.innerHTML = `
        <h3>como te gustaria la decoracion? cuentanos</h3>
        <textarea id="comentarioDecoracion" rows="4" style="width:100%; padding:10px;"></textarea>
        <button onclick="guardarComentario('decoracion')">Enviar Comentario</button>
    `;
}

function mostrarMenu(){
    document.getElementById('panelDecoracion').style.display = 'none';
    document.getElementById('panelSnacks').style.display = 'none';

    const cont = document.getElementById('panelMenu');
    cont.style.display = 'block';
    cont.innerHTML = '';

    // Formulario para comentario del Menú
    cont.innerHTML = `
        <h3>como te gustaria el menu? cuentanos</h3>
        <textarea id="comentarioMenu" rows="4" style="width:100%; padding:10px;"></textarea>
        <button onclick="guardarComentario('menu')">Enviar Comentario</button>
    `;
}

function mostrarSnacks(){
    document.getElementById('panelDecoracion').style.display = 'none';
    document.getElementById('panelMenu').style.display = 'none';
    
    const cont = document.getElementById('panelSnacks');
    cont.style.display = 'block';
    cont.innerHTML = '';

    // Formulario para comentario de Snacks
    cont.innerHTML = `
        <h3>como te gustaria los snacks? cuentanos</h3>
        <textarea id="comentarioSnacks" rows="4" style="width:100%; padding:10px;"></textarea>
        <button onclick="guardarComentario('snacks')">Enviar Comentario</button>
    `;
}
// Función para guardar el comentario
function guardarComentario(tipo) {
    let comentario = '';
    if (tipo === 'decoracion') {
        comentario = document.getElementById('comentarioDecoracion').value;
    } else if (tipo === 'menu') {
        comentario = document.getElementById('comentarioMenu').value;
    } else if (tipo === 'snacks') {
        comentario = document.getElementById('comentarioSnacks').value;
    }

    if (comentario.trim() === '') {
        alert('Por favor, escribe un comentario antes de enviarlo.');
        return;
    }

    // Enviar el comentario al servidor
    fetch('procesos/guardar_comentario.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            salon_id: infoSalonGlobal.id,
            tipo_comentario: tipo,
            comentario: comentario
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Comentario enviado exitosamente.');
        } else {
            alert('Error al enviar el comentario. Intenta nuevamente.');
        }
    })
    .catch(() => {
        alert('Error de conexión al enviar el comentario.');
    });
}

    // Las funciones anteriores (mostrarResenas, mostrarRanking, mostrarInfoSalon) están al final del archivo, no necesitan cambios
function mostrarInfoSalon(idSalon) {
    const modal = document.getElementById('modalInfoSalon');
    const titulo = document.getElementById('tituloInfoSalon');
    const contenido = document.getElementById('contenidoInfoSalon');
    const capacidadP = document.getElementById('capacidadSalon');

    modal.style.display = 'block';
    titulo.textContent = 'Cargando información...';
    capacidadP.textContent = '';
    contenido.innerHTML = '<p>Cargando detalles...</p>';

    // La solicitud fetch que llama al archivo PHP para obtener los datos del salón
    fetch('ajax/cargar_info_salon.php?id=' + idSalon)  // URL del archivo PHP
        .then(resp => resp.json())  // Convierte la respuesta a JSON
        .then(data => {
            if (data.error) {
                titulo.textContent = 'Error';
                contenido.innerHTML = '<p>' + data.error + '</p>';  // Muestra el error si existe
                return;
            }

            infoSalonGlobal = data;  // Almacena la información del salón
            titulo.textContent = data.nombre;
            capacidadP.textContent = `Capacidad máxima: ${data.capacidad}`;  // Muestra la capacidad
            contenido.innerHTML = '<p>Selecciona una opción para ver detalles.</p>';
        })
        .catch(() => {
            titulo.textContent = 'salon infantil';
            contenido.innerHTML = '<p>comenta';  // Error de red
        });
}

</script>

<?php include 'includes/footer.php'; ?>
