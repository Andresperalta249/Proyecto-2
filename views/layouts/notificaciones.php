<!-- Botón de notificaciones -->
<div class="dropdown">
    <button class="btn btn-link nav-link dropdown-toggle" type="button" id="notificacionesDropdown" 
            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <span id="notificacionesBadge" class="badge badge-danger badge-pill"></span>
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificacionesDropdown" style="width: 300px;">
        <h6 class="dropdown-header">Notificaciones</h6>
        <div id="notificacionesLista" class="notificaciones-lista">
            <div class="text-center py-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
            </div>
        </div>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-center" href="<?= BASE_URL ?>notificacion">
            Ver todas las notificaciones
        </a>
    </div>
</div>

<style>
.notificaciones-lista {
    max-height: 300px;
    overflow-y: auto;
}

.notificacion-item {
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}

.notificacion-item:hover {
    background-color: #f8f9fa;
}

.notificacion-item.no-leida {
    background-color: #e3f2fd;
}

.notificacion-item .notificacion-titulo {
    font-weight: bold;
    margin-bottom: 5px;
}

.notificacion-item .notificacion-mensaje {
    font-size: 0.9em;
    color: #666;
    margin-bottom: 5px;
}

.notificacion-item .notificacion-fecha {
    font-size: 0.8em;
    color: #999;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificacionesLista = document.getElementById('notificacionesLista');
    const notificacionesBadge = document.getElementById('notificacionesBadge');
    let notificaciones = [];

    // Función para cargar notificaciones
    function cargarNotificaciones() {
        fetch('<?= BASE_URL ?>notificacion/getNotificaciones')
            .then(response => response.json())
            .then(data => {
                notificaciones = data.notificaciones;
                actualizarBadge(data.no_leidas);
                renderizarNotificaciones();
            })
            .catch(error => {
                console.error('Error al cargar notificaciones:', error);
            });
    }

    // Función para actualizar el badge
    function actualizarBadge(noLeidas) {
        if (noLeidas > 0) {
            notificacionesBadge.textContent = noLeidas;
            notificacionesBadge.style.display = 'inline';
        } else {
            notificacionesBadge.style.display = 'none';
        }
    }

    // Función para renderizar las notificaciones
    function renderizarNotificaciones() {
        if (notificaciones.length === 0) {
            notificacionesLista.innerHTML = `
                <div class="text-center py-3">
                    <p class="text-muted mb-0">No hay notificaciones</p>
                </div>
            `;
            return;
        }

        notificacionesLista.innerHTML = notificaciones
            .slice(0, 5)
            .map(notificacion => `
                <div class="notificacion-item ${notificacion.leida ? '' : 'no-leida'}" 
                     data-id="${notificacion.id}">
                    <div class="notificacion-titulo">
                        ${notificacion.titulo}
                    </div>
                    <div class="notificacion-mensaje">
                        ${notificacion.mensaje}
                    </div>
                    <div class="notificacion-fecha">
                        ${formatearFecha(notificacion.fecha_creacion)}
                    </div>
                </div>
            `)
            .join('');

        // Agregar eventos a las notificaciones
        document.querySelectorAll('.notificacion-item').forEach(item => {
            item.addEventListener('click', function() {
                const id = this.dataset.id;
                marcarComoLeida(id);
                if (notificaciones.find(n => n.id === id).enlace) {
                    window.location.href = notificaciones.find(n => n.id === id).enlace;
                }
            });
        });
    }

    // Función para marcar una notificación como leída
    function marcarComoLeida(id) {
        fetch('<?= BASE_URL ?>notificacion/marcarLeida', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notificacion = notificaciones.find(n => n.id === id);
                if (notificacion) {
                    notificacion.leida = true;
                    const item = document.querySelector(`.notificacion-item[data-id="${id}"]`);
                    if (item) {
                        item.classList.remove('no-leida');
                    }
                    actualizarBadge(parseInt(notificacionesBadge.textContent || '0') - 1);
                }
            }
        });
    }

    // Función para formatear la fecha
    function formatearFecha(fecha) {
        const ahora = new Date();
        const notificacion = new Date(fecha);
        const diff = Math.floor((ahora - notificacion) / 1000); // diferencia en segundos

        if (diff < 60) {
            return 'Hace un momento';
        } else if (diff < 3600) {
            const minutos = Math.floor(diff / 60);
            return `Hace ${minutos} ${minutos === 1 ? 'minuto' : 'minutos'}`;
        } else if (diff < 86400) {
            const horas = Math.floor(diff / 3600);
            return `Hace ${horas} ${horas === 1 ? 'hora' : 'horas'}`;
        } else {
            return notificacion.toLocaleDateString();
        }
    }

    // Cargar notificaciones inicialmente
    cargarNotificaciones();

    // Actualizar notificaciones cada minuto
    setInterval(cargarNotificaciones, 60000);

    // Actualizar notificaciones cuando el dropdown se abre
    document.getElementById('notificacionesDropdown').addEventListener('click', function() {
        cargarNotificaciones();
    });
});
</script> 