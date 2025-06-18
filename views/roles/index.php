<?php
$title = "Gestión de Roles";
$description = "Administración de roles y permisos en el sistema.";
?>

<div class="container-fluid dashboard-compact">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaRoles" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rol -->
<div class="modal fade" id="rolModal" tabindex="-1" role="dialog" aria-labelledby="rolModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rolModalLabel">Nuevo Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formRol">
                <div class="modal-body">
                    <input type="hidden" id="idRol" name="idRol">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" required>
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select class="form-control" id="estado" name="estado" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- FAB: Botón flotante de acción principal para la página de roles -->
<?php if (verificarPermiso('roles_crear')): ?>
<button class="fab-btn" id="btnAgregarRol" data-bs-toggle="modal" data-bs-target="#rolModal" aria-label="Nuevo Rol" title="Nuevo rol">
    <span class="fab-icon"><i class="fas fa-plus"></i></span>
    <span class="fab-text">Nuevo Rol</span>
</button>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTable para roles
    const tablaRoles = $('#tablaRoles').DataTable({
        processing: true,
        serverSide: true,
        lengthChange: false,
        dom: 'fltip',
        responsive: true,
        ajax: {
            url: '<?php echo APP_URL; ?>/roles/obtenerRoles',
            type: 'POST',
            dataSrc: function(json) {
                return json.data;
            },
            error: function(xhr, error, thrown) {
                console.error("Error en la petición AJAX de DataTables:", xhr, error, thrown);
            }
        },
        columns: [
            { data: 'id' },
            { data: 'nombre' },
            { data: 'descripcion' },
            { 
                data: 'estado',
                render: function(data, type, row) {
                    const isChecked = data === 'activo' ? 'checked' : '';
                    const rolId = row.id;
                    return `
                        <div class="form-check form-switch">
                            <input class="form-check-input estado-switch" type="checkbox" role="switch" id="estadoSwitch_${rolId}" data-id="${rolId}" ${isChecked}>
                            <label class="form-check-label" for="estadoSwitch_${rolId}"></label>
                        </div>
                    `;
                }
            },
            {
                data: null,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-info editar-rol" data-id="${data.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger eliminar-rol" data-id="${data.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        language: {
            url: '<?= APP_URL ?>/assets/js/i18n/Spanish.json'
        }
    });

    // Manejar el cambio de estado del switch
    $('#tablaRoles tbody').on('change', '.estado-switch', function() {
        const rolId = $(this).data('id');
        const nuevoEstado = this.checked ? 'activo' : 'inactivo';

        $.ajax({
            url: `<?php echo APP_URL; ?>/roles/toggleEstado`,
            type: 'POST',
            data: { id: rolId, estado: nuevoEstado },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                    $(this).prop('checked', !this.checked);
                }
            }.bind(this),
            error: function(xhr, status, error) {
                console.error("Error al cambiar el estado:", xhr, status, error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de comunicación con el servidor.'
                });
                $(this).prop('checked', !this.checked);
            }.bind(this)
        });
    });

    // Abrir modal para crear rol
    document.getElementById('btnAgregarRol')?.addEventListener('click', function() {
        document.getElementById('formRol').reset();
        document.getElementById('idRol').value = '';
        document.getElementById('rolModalLabel').textContent = 'Nuevo Rol';
    });

    // Editar rol
    $('#tablaRoles tbody').on('click', '.editar-rol', function() {
        const rolId = $(this).data('id');
        fetch(`<?php echo APP_URL; ?>/roles/obtenerRol/${rolId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('idRol').value = data.rol.id;
                    document.getElementById('nombre').value = data.rol.nombre;
                    document.getElementById('descripcion').value = data.rol.descripcion;
                    document.getElementById('estado').value = data.rol.estado;
                    document.getElementById('rolModalLabel').textContent = 'Editar Rol';
                    $('#rolModal').modal('show');
                }
            });
    });

    // Eliminar rol
    $('#tablaRoles tbody').on('click', '.eliminar-rol', function() {
        const rolId = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`<?php echo APP_URL; ?>/roles/eliminarRol/${rolId}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        tablaRoles.ajax.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                });
            }
        });
    });

    // Manejar envío del formulario
    document.getElementById('formRol').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('<?php echo APP_URL; ?>/roles/guardarRol', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                });
                $('#rolModal').modal('hide');
                tablaRoles.ajax.reload();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ha ocurrido un error al procesar la solicitud'
            });
        });
    });
});
</script> 