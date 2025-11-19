<?php
require_once __DIR__ . '/conexion.php';
$db = new conexion();
$pdo = $db->getConnection();

// Obtener todos los salones
$stmt = $pdo->query("SELECT * FROM salon");
$salones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>

<!-- ===========================
        SECCIÓN INICIO
=========================== -->
<section class="inicio" id="inicio">
    <div class="image">
        <img src="img/logo.png" alt="KINDERfiesta"
             onerror="this.src='https://placehold.co/400x180/25d1b2/0c443a?text=LOGO'">
    </div>
</section>

<!-- ===========================
        SECCIÓN SALONES
=========================== -->
<section class="salones" id="salones">

    <h2 class="titulo-salones">Salones de Kinder Fiesta</h2>

    <!-- Buscador -->
    <div style="text-align:center; margin-bottom:20px;">
        <input type="text" id="buscadorSalones" placeholder="Buscar salón por nombre..."
               style="padding:8px 12px; width:300px; border-radius:5px; border:1px solid #0c443a;">
    </div>

    <div class="salones-container">
        <?php foreach ($salones as $salon):

            // Obtener calificación del salón
            $stmtCalif = $pdo->prepare("
                SELECT AVG(estrellas) AS promedio, COUNT(*) AS total 
                FROM calificacion 
                WHERE id_salon = ?
            ");
            $stmtCalif->execute([$salon['id_salon']]);
            $califInfo = $stmtCalif->fetch(PDO::FETCH_ASSOC);

            $promedio = $califInfo['promedio'] ? round($califInfo['promedio'],1) : 0;
            $totalReseñas = $califInfo['total'];

            // Imagen del salón
            $urlFoto = 'img/salon' . $salon['id_salon'] . '.jpg';

            // Generar estrellas HTML
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

        ?>
        <div class="salon-card">

            <!-- Imagen del salón -->
            <img src="<?= htmlspecialchars($urlFoto); ?>" 
                 alt="<?= htmlspecialchars($salon['nombre']); ?>"
                 onerror="this.src='https://placehold.co/280x180/25d1b2/0c443a?text=<?= urlencode($salon['nombre']); ?>'">

            <h3><?= htmlspecialchars($salon['nombre']); ?></h3>

            <p>📞 <?= htmlspecialchars($salon['telefono']); ?></p>
            <p><strong>DESCRIPCIÓN:</strong></p>
            <p><?= htmlspecialchars($salon['descripcion']); ?></p>

            <p>📍 <?= htmlspecialchars(substr($salon['direccion'],0,30)) . (strlen($salon['direccion'])>30?'...':''); ?></p>

            <div class="rating">
                <div class="promedio-estrellas"><?= $estrellasHTML; ?></div>
                <div class="promedio-calificacion"><?= $promedio; ?>/5 (<?= $totalReseñas; ?> reseñas)</div>
            </div>

            <a href="https://www.google.com/maps?q=<?= $salon['latitud']; ?>,<?= $salon['longitud']; ?>"
               target="_blank" class="btn-ubicacion">Ubicación</a>

            <button class="btn-resenas" onclick="mostrarResenas(<?= $salon['id_salon']; ?>)">Ver reseñas</button>

            <button class="btn-mas-info" onclick="mostrarInfoSalon(<?= $salon['id_salon']; ?>)">Más Información</button>

        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===========================
        BOTÓN RANKING
=========================== -->
<div style="text-align:center; margin-top:30px;">
    <button onclick="mostrarRanking()"
            style="background-color:#0c443a; color:white; border:none;
                   padding:10px 20px; border-radius:10px; font-size:16px; cursor:pointer;">
         Ver Ranking de salones infantiles
    </button>
</div>

<!-- ===========================
        MODALES (RESEÑAS / INFO / LOGIN / RANKING)
=========================== -->

<div id="modalResenas" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal('modalResenas')">×</span>
        <h2 id="tituloResenas"></h2>
        <div id="contenidoResenas"></div>
    </div>
</div>

<div id="modalInfoSalon" class="modal">
    <div class="modal-content" style="width:80%; max-width:900px; font-size:18px;">
        <span class="close" onclick="cerrarModal('modalInfoSalon')">×</span>

        <h2 id="tituloInfoSalon" style="font-size:24px; color:#0c443a;">Nombre del Salón</h2>
        <p id="capacidadSalon" style="font-weight:bold; margin-bottom:15px;"></p>

        <!-- Botones -->
        <div class="info-buttons" style="margin-top:20px;">
            <button onclick="mostrarDecoracion()">Decoración</button>
            <button onclick="mostrarMenu()">Menú de Comidas</button>
            <button onclick="mostrarSnacks()">Snacks</button>
        </div>

        <div id="contenidoInfoSalon" style="margin-top:15px;">
            <p>Selecciona una opción para ver detalles.</p>
        </div>

        <div id="panelDecoracion" style="display:none;"></div>
        <div id="panelMenu" style="display:none;"></div>
        <div id="panelSnacks" style="display:none;"></div>
    </div>
</div>

<div id="modalRanking" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal('modalRanking')">×</span>
        <h2 style="color:#0c443a;">Ranking de salones infantiles con más estrellas</h2>
        <div id="contenidoRanking"><p>Cargando ranking...</p></div>
    </div>
</div>

<div id="modalLogin" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal('modalLogin')">×</span>
        <h3 style="color:#0c443a;">Iniciar Sesión</h3>

        <form id="loginForm" onsubmit="iniciarSesion(event)">
            <div class="form-resena">
                <input type="text" name="usuario" placeholder="Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" style="width:100%;">Acceder</button>
            </div>
        </form>

        <p id="loginMessage" style="text-align:center; margin-top:15px; color:red;"></p>
    </div>
</div>

<!-- ===========================
        JAVASCRIPT
=========================== -->
<script>

// FILTRAR SALONES EN TIEMPO REAL
document.getElementById('buscadorSalones').addEventListener('input', function() {
    const filtro = this.value.toLowerCase();
    const tarjetas = document.querySelectorAll('.salon-card');

    tarjetas.forEach(tarjeta => {
        const nombre = tarjeta.querySelector('h3').textContent.toLowerCase();
        tarjeta.style.display = nombre.includes(filtro) ? 'block' : 'none';
    });
});

// MODALES (GENÉRICOS)
function mostrarModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = 'block';
}
function cerrarModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = 'none';
}

// MOSTRAR RESEÑAS
function mostrarResenas(idSalon) {
    mostrarModal('modalResenas');
    document.getElementById('contenidoResenas').innerHTML = 'Cargando reseñas...';

    fetch('ajax/cargar_resenas.php?id=' + idSalon)
        .then(r => r.text())
        .then(html => document.getElementById('contenidoResenas').innerHTML = html)
        .catch(() => document.getElementById('contenidoResenas').innerHTML = '<p>Error al cargar reseñas.</p>');
}

// LOGIN
function iniciarSesion(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const msg = document.getElementById('loginMessage');

    msg.textContent = 'Verificando...';

    fetch('procesos/login.php', { method:'POST', body:formData })
        .then(r => r.json())
        .then(data => {
            msg.style.color = data.success ? 'green' : 'red';
            msg.textContent = data.message;

            if (data.success) {
                setTimeout(() => {
                    if (data.redirect) window.location.href = data.redirect;
                    else {
                        cerrarModal('modalLogin');
                        location.reload();
                    }
                }, 700);
            }
        })
        .catch(() => msg.textContent = 'Error de conexión.');
}

// MOSTRAR INFO DEL SALÓN
let infoSalonGlobal = null;

function mostrarInfoSalon(idSalon) {
    mostrarModal('modalInfoSalon');

    const titulo = document.getElementById('tituloInfoSalon');
    const contenido = document.getElementById('contenidoInfoSalon');
    const capacidad = document.getElementById('capacidadSalon');

    titulo.textContent = 'Cargando...';
    contenido.innerHTML = '<p>Cargando detalles...</p>';
    capacidad.textContent = '';

    fetch('ajax/cargar_info_salon.php?id=' + idSalon)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                titulo.textContent = 'Error';
                contenido.innerHTML = `<p>${data.error}</p>`;
                return;
            }

            infoSalonGlobal = data;

            titulo.textContent = data.nombre;
            capacidad.textContent = 'Capacidad máxima: ' + data.capacidad;
            contenido.innerHTML = '<p>Selecciona una opción para ver detalles.</p>';
        })
        .catch(() => contenido.innerHTML = '<p>Error de red.</p>');
}

// PANEL DECORACIÓN / MENÚ / SNACKS
function mostrarPanel(idMostrar) {
    ['panelDecoracion','panelMenu','panelSnacks'].forEach(id => {
        document.getElementById(id).style.display = (id === idMostrar) ? 'block' : 'none';
    });
}

function mostrarDecoracion() {
    mostrarPanel('panelDecoracion');
    document.getElementById('panelDecoracion').innerHTML = `
        <h3>¿Cómo te gustaría la decoración? Cuéntanos</h3>
        <textarea id="comentarioDecoracion" rows="4" style="width:100%;"></textarea>
        <button onclick="guardarComentario('decoracion')">Enviar Comentario</button>
    `;
}

function mostrarMenu() {
    mostrarPanel('panelMenu');
    document.getElementById('panelMenu').innerHTML = `
        <h3>¿Cómo te gustaría el menú? Cuéntanos</h3>
        <textarea id="comentarioMenu" rows="4" style="width:100%;"></textarea>
        <button onclick="guardarComentario('menu')">Enviar Comentario</button>
    `;
}

function mostrarSnacks() {
    mostrarPanel('panelSnacks');
    document.getElementById('panelSnacks').innerHTML = `
        <h3>¿Cómo te gustaría los snacks? Cuéntanos</h3>
        <textarea id="comentarioSnacks" rows="4" style="width:100%;"></textarea>
        <button onclick="guardarComentario('snacks')">Enviar Comentario</button>
    `;
}

// GUARDAR COMENTARIO
function guardarComentario(tipo) {
    const idTextarea = {
        decoracion: 'comentarioDecoracion',
        menu: 'comentarioMenu',
        snacks: 'comentarioSnacks'
    }[tipo];

    const comentario = document.getElementById(idTextarea).value.trim();

    if (!comentario) return alert('Escribe un comentario.');

    fetch('procesos/guardar_comentario.php', {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify({
            salon_id: infoSalonGlobal.id,
            tipo_comentario: tipo,
            comentario
        })
    })
    .then(r => r.json())
    .then(data => alert(data.success ? 'Comentario enviado' : 'Error al enviar'))
    .catch(() => alert('Error de conexión'));
}

</script>

<?php include 'includes/footer.php'; ?>


