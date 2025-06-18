<?php
$title = "Gestión de Usuarios";
$description = "Administración de usuarios y sus roles en el sistema.";
?>

<div class="container-fluid dashboard-compact">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaUsuarios" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
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

<!-- Modal Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" role="dialog" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUsuarioLabel">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUsuario">
                <div class="modal-body">
                    <input type="hidden" id="idUsuario" name="idUsuario">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required autocomplete="username">
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label for="rol">Rol</label>
                        <select class="form-control" id="rol" name="rol" required>
                            <option value="">Seleccione un rol</option>
                            <option value="admin">Administrador</option>
                            <option value="user">Usuario</option>
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

<!-- FAB: Botón flotante de acción principal para la página de usuarios -->
<button class="fab-btn" id="btnNuevoUsuarioFlotante" data-bs-toggle="modal" data-bs-target="#modalUsuario" aria-label="Nuevo Usuario" title="Nuevo usuario">
    <span class="fab-icon"><i class="fas fa-plus"></i></span>
    <span class="fab-text">Nuevo Usuario</span>
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTable
    const tablaUsuarios = $('#tablaUsuarios').DataTable({
        processing: true,
        serverSide: true,
        lengthChange: false,
        dom: 'fltip',
        responsive: true,
        ajax: {
            url: '<?php echo APP_URL; ?>/usuarios/obtenerUsuarios',
            type: 'POST',
            dataSrc: function(json) {
                console.log("Datos recibidos por DataTables:", json);
                return json.data; // Asegúrate de que tu API devuelve los datos bajo la clave 'data'
            },
            error: function(xhr, error, thrown) {
                console.error("Error en la petición AJAX de DataTables:", xhr, error, thrown);
                // Puedes mostrar un mensaje de error al usuario si lo deseas
            }
        },
        columns: [
            { data: 'id' },
            { data: 'nombre' },
            { data: 'email' },
            { data: 'rol' },
            { 
                data: 'estado',
                render: function(data, type, row) {
                    const isChecked = data === 'activo' ? 'checked' : '';
                    const userId = row.id; // Asumiendo que 'id' está disponible en los datos de la fila
                    return `
                        <div class="form-check form-switch">
                            <input class="form-check-input estado-switch" type="checkbox" role="switch" id="estadoSwitch_${userId}" data-id="${userId}" ${isChecked}>
                            <label class="form-check-label" for="estadoSwitch_${userId}"></label>
                        </div>
                    `;
                }
            },
            {
                data: null,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-info editar-usuario" data-id="${data.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger eliminar-usuario" data-id="${data.id}">
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
    $('#tablaUsuarios tbody').on('change', '.estado-switch', function() {
        const userId = $(this).data('id');
        const nuevoEstado = this.checked ? 'activo' : 'inactivo';

        $.ajax({
            url: `<?php echo APP_URL; ?>/usuarios/toggleEstado`, // Asumiendo que este endpoint existe en tu backend
            type: 'POST',
            data: { id: userId, estado: nuevoEstado },
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
                    $(this).prop('checked', !this.checked); // Revertir el switch si falla
                }
            }.bind(this), // 'bind(this)' para mantener el contexto del switch dentro de success/error
            error: function(xhr, status, error) {
                console.error("Error al cambiar el estado:", xhr, status, error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de comunicación con el servidor.'
                });
                $(this).prop('checked', !this.checked); // Revertir el switch en caso de error de red
            }.bind(this)
        });
    });

    // Manejar envío del formulario
    document.getElementById('formUsuario').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('<?php echo APP_URL; ?>/usuarios/guardarUsuario', {
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
                $('#modalUsuario').modal('hide');
                tablaUsuarios.ajax.reload();
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

    // Editar usuario
    document.getElementById('tablaUsuarios').addEventListener('click', function(e) {
        if (e.target.closest('.editar-usuario')) {
            const id = e.target.closest('.editar-usuario').dataset.id;
            fetch(`<?php echo APP_URL; ?>/usuarios/obtenerUsuario/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('idUsuario').value = data.usuario.id;
                        document.getElementById('nombre').value = data.usuario.nombre;
                        document.getElementById('email').value = data.usuario.email;
                        document.getElementById('rol').value = data.usuario.rol;
                        document.getElementById('password').value = '';
                        $('#modalUsuario').modal('show');
                    }
                });
        }
    });

    // Eliminar usuario
    document.getElementById('tablaUsuarios').addEventListener('click', function(e) {
        if (e.target.closest('.eliminar-usuario')) {
            const id = e.target.closest('.eliminar-usuario').dataset.id;
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
                    fetch(`<?php echo APP_URL; ?>/usuarios/eliminarUsuario/${id}`, {
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
                            tablaUsuarios.ajax.reload();
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
                            text: 'Ha ocurrido un error al eliminar el usuario'
                        });
                    });
                }
            });
        }
    });

    // Resetear formulario y título del modal al abrir
    $('#modalUsuario').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget); // Botón que activó el modal
        const isEdit = button.hasClass('editar-usuario');
        const modalTitle = $(this).find('.modal-title');
        const form = document.getElementById('formUsuario');

        if (!isEdit) {
            modalTitle.text('Nuevo Usuario');
            form.reset();
            document.getElementById('idUsuario').value = ''; // Asegurar que el ID esté vacío para nuevos usuarios
            document.getElementById('password').setAttribute('required', 'required'); // Contraseña requerida para nuevo
        } else {
            modalTitle.text('Editar Usuario');
            document.getElementById('password').removeAttribute('required'); // Contraseña opcional al editar
        }
    });

    // Forzar búsqueda automática al escribir en el input de búsqueda de DataTables
    $('#tablaUsuarios_filter input').off().on('input', function() {
        tablaUsuarios.search(this.value).draw();
    });
});
</script> 