<?php
// includes/footer.php
?>
<script>
    function mostrarResenas(idSalon, nombreSalon) {
        // Cambiar el título del modal y mostrarlo
        document.getElementById('tituloResenas').textContent = nombreSalon;
        document.getElementById('contenidoResenas').innerHTML = '<p>Cargando reseñas...</p>';
        document.getElementById('modalResenas').style.display = 'block';

        document.getElementById('buscadorSalones').addEventListener('input', function() {
    const filtro = this.value.toLowerCase();
    const tarjetas = document.querySelectorAll('.salon-card');

    tarjetas.forEach(tarjeta => {
        const nombre = tarjeta.querySelector('h3').textContent.toLowerCase();
        if (nombre.includes(filtro)) {
            tarjeta.style.display = 'block';
        } else {
            tarjeta.style.display = 'none';
        }
    });
});
        
        // Llamar al archivo PHP que carga las reseñas
        fetch('ajax/cargar_resenas.php?id=' + idSalon)
            .then(response => response.text())
            .then(data => {
                document.getElementById('contenidoResenas').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('contenidoResenas').innerHTML = '<p class="mensaje mensaje-error">Error al cargar las reseñas.</p>';
            });

     // Llamar al archivo PHP que carga las informacion del salon
        fetch('ajax/cargar_ingo_salon.php?id=' + idSalon)
            .then(response => response.text())
            .then(data => {
                document.getElementById('contenidoInformacion').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('contenidoInformacion').innerHTML = '<p class="mensaje mensaje-error">Error al cargar informacion del salon.</p>';
            });
    }

    function mostrarRanking() {
    const modal = document.getElementById('modalRanking');
    const contenido = document.getElementById('contenidoRanking');

    modal.style.display = 'block';
    contenido.innerHTML = 'Cargando ranking...';

    // Aquí iría tu fetch si quieres traer el ranking desde PHP
    fetch('ajax/cargar_ranking.php') // crea este archivo para traer datos desde la base
        .then(resp => resp.text())
        .then(html => {
            contenido.innerHTML = html;
        })
        .catch(() => {
            contenido.innerHTML = '<p class="mensaje mensaje-error">Error al cargar ranking</p>';
        });
}



   // Función para cerrar un modal específico
function cerrarModal(idModal){
    const modal = document.getElementById(idModal);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Cierra cualquier modal si haces clic fuera del contenido
window.onclick = function(event) {
    const modales = document.querySelectorAll('.modal');
    modales.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

    function enviarResena(event, idSalon) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        fetch('procesos/guardar_resena.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('¡Reseña enviada con éxito!');
                mostrarResenas(idSalon, form.dataset.nombre);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al enviar la reseña.');
        });
    }

    // Cierra el modal si haces clic fuera de él
    window.onclick = function(event) {
        const modal = document.getElementById('modalResenas');
        if (event.target === modal) {
            cerrarModal();
        }
    }

    // ---- Cerrar el modal de LOGIN ----
const modalLogin = document.getElementById('modalLogin');
if (modalLogin) {
    const botonCerrar = modalLogin.querySelector('.close');
    
    if (botonCerrar) {
        botonCerrar.addEventListener('click', () => {
            modalLogin.style.display = 'none';
        });
    }

    window.addEventListener('click', (event) => {
        if (event.target === modalLogin) {
            modalLogin.style.display = 'none';
        }
    });
}

let infoSalonGlobal = {};

function mostrarInfoSalon(idSalon) {
    const modal = document.getElementById('modalInfoSalon');
    const titulo = document.getElementById('tituloInfoSalon');
    const contenido = document.getElementById('contenidoInfoSalon');

    modal.style.display = 'block';
    titulo.textContent = 'Cargando información...';
    contenido.innerHTML = '<p>Cargando detalles...</p>';

    fetch('ajax/cargar_info_detallada.php?id=' + idSalon)
        .then(resp => resp.json())
        .then(data => {
            if (data.error) {
                titulo.textContent = 'Error';
                contenido.innerHTML = '<p>' + data.error + '</p>';
                return;
            }

            infoSalonGlobal = data; // guardamos info para usar en botones
            titulo.textContent = 'Salón ' + idSalon;
            contenido.innerHTML = '<p>Selecciona una opción para ver detalles.</p>';
        })
        .catch(() => {
            titulo.textContent = 'salon infantil';
            contenido.innerHTML = '<p>detalles.</p>';
        });
}

// Función para mostrar la sección correspondiente
function mostrarSeccion(seccion) {
    const contenido = document.getElementById('contenidoInfoSalon');
    if (!infoSalonGlobal) return;

    if (seccion === 'capacidad') {
        contenido.innerHTML = `<p><strong>Capacidad máxima:</strong> ${infoSalonGlobal.capacidad} personas</p>`;
    } else if (seccion === 'decoracion') {
        let html = '<p><strong>Decoración disponible:</strong></p>';
        html += infoSalonGlobal.decoracion.map(d => `<button>${d}</button>`).join(' ');
        contenido.innerHTML = html;
    } else if (seccion === 'menu') {
        const categorias = ['refresco','postre','comida'];
        let html = '';
        categorias.forEach(cat => {
            html += `<p><strong>${cat.charAt(0).toUpperCase() + cat.slice(1)}:</strong></p>`;
            html += infoSalonGlobal.comidas
                .filter(c => c.categoria === cat)
                .map(c => `<div style="display:inline-block; margin:5px; text-align:center;">
                            <img src="${c.imagen}" style="width:60px;height:60px;"><br>${c.nombre}
                           </div>`).join('');
        });
        contenido.innerHTML = html;
    } else if (seccion === 'snacks') {
        let html = '<p><strong>Snacks disponibles:</strong></p>';
        html += infoSalonGlobal.snacks.map(s => `<div style="display:inline-block; margin:5px; text-align:center;">
                            <img src="${s.imagen}" style="width:60px;height:60px;"><br>${s.nombre}
                           </div>`).join('');
        contenido.innerHTML = html;
    }
}


</script>
</body>
</html>