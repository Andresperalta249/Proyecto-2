<link rel="stylesheet" href="assets/css/boton.css">
<?php
$subtitulo = isset($subtitulo) ? $subtitulo : 'Gestiona, crea y administra los roles del sistema.';
?>
<p class="subtitle text-md" style="margin-top: 0; margin-bottom: 0;">
  <?= htmlspecialchars($subtitulo) ?>
</p>
<div class="table-container">
    <div class="search-filters">
        <input type="text" id="searchInput" class="search-input" placeholder="Buscar roles...">
        <select class="filter-select" id="estadoFilter">
            <option value="">Todos los estados</option>
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
        </select>
        <button type="button" id="searchButton" title="Buscar" style="padding: 0.5rem 1rem; border-radius: 4px; border: 1px solid #2196f3; background: #2196f3; color: #fff; cursor: pointer;">
            <i class="fas fa-search"></i>
        </button>
    </div>
    <div id="rolesTable">
        <!-- La tabla se cargará aquí mediante AJAX -->
    </div>
    <?php if (verificarPermiso('roles_crear')): ?>
    <button type="button" id="btnAgregarRol" class="fab-btn" title="Agregar rol">
        <span class="fab-icon"><i class="fas fa-plus"></i></span>
        <span class="fab-text">Agregar rol</span>
    </button>
    <?php endif; ?>
</div>
<!-- Modal para crear/editar rol -->
<div class="modal fade" id="rolModal" tabindex="-1" aria-labelledby="rolModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div id="modalContent">
                <!-- El contenido del modal se cargará aquí mediante AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cargar tabla inicial
    cargarTablaRoles();

    // Búsqueda
    document.getElementById('searchButton').addEventListener('click', function() {
        cargarTablaRoles();
    });

    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            cargarTablaRoles();
        }
    });

    // Filtro de estado
    document.getElementById('estadoFilter').addEventListener('change', function() {
        cargarTablaRoles();
    });

    // Abrir modal para crear rol
    document.getElementById('btnAgregarRol')?.addEventListener('click', function() {
        cargarFormularioRol();
    });

    // Delegación de eventos para editar y eliminar
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-editar')) {
            const id = e.target.closest('.btn-editar').dataset.id;
            cargarFormularioRol(id);
        }
        if (e.target.closest('.btn-eliminar')) {
            const id = e.target.closest('.btn-eliminar').dataset.id;
            eliminarRol(id);
        }
    });

    // Manejar cierre del modal
    const rolModal = document.getElementById('rolModal');
    if (rolModal) {
        rolModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('modalContent').innerHTML = '';
        });
    }
});

function cargarTablaRoles() {
    const search = document.getElementById('searchInput').value;
    const estado = document.getElementById('estadoFilter').value;

    fetch(`<?= APP_URL ?>/roles/tabla?search=${encodeURIComponent(search)}&estado=${encodeURIComponent(estado)}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('rolesTable').innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar la tabla de roles'
            });
        });
}

function cargarFormularioRol(id = null) {
    const url = id ? 
        `<?= APP_URL ?>/roles/form?id=${encodeURIComponent(id)}` : 
        '<?= APP_URL ?>/roles/form';

    // Cierra el modal si ya está abierto y limpia el contenido
    const rolModal = document.getElementById('rolModal');
    const modalInstance = bootstrap.Modal.getInstance(rolModal);
    if (modalInstance) {
        modalInstance.hide();
    }
    document.getElementById('modalContent').innerHTML = '';

    fetch(url)
        .then(response => response.text())
        .then(html => {
            document.getElementById('modalContent').innerHTML = html;
            const modal = new bootstrap.Modal(rolModal, {
                backdrop: false,
                keyboard: true,
                focus: true
            });
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar el formulario'
            });
        });
}

function eliminarRol(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);

            fetch('<?= APP_URL ?>/roles/delete', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Rol eliminado correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        cargarTablaRoles();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al eliminar el rol'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al eliminar el rol'
                });
            });
        }
    });
}
</script> 