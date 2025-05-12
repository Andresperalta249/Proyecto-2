<?php
$header_buttons = '';

$extra_js = <<<'JS'
<script>
console.log('Script de usuarios cargado');
$(document).ready(function() {
    console.log('DOM listo');
    // Validación del formulario
    function validarFormulario() {
        const form = $('#formUsuario');
        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();
        const isEdit = $('#usuario_id').val() !== '';

        // Validar campos requeridos
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return false;
        }

        // Validar contraseñas solo si es nuevo usuario o se está cambiando la contraseña
        if (!isEdit || password) {
            if (password.length < 6) {
                Swal.fire('Error', 'La contraseña debe tener al menos 6 caracteres', 'error');
                return false;
            }
            if (password !== confirmPassword) {
                Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
                return false;
            }
        }

        return true;
    }

    // Filtro de búsqueda
    $('#formBuscarUsuarios').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'usuarios/buscar',
            type: 'POST',
            data: $(this).serialize(),
            beforeSend: function() {
                showLoader();
            },
            success: function(html) {
                $('#tablaUsuarios tbody').html(html);
                console.log('HTML actualizado en tbody:', html);
                // Probar evento directo tras actualizar el tbody
                $('.editar-usuario').on('click', function() {
                    console.log('Click directo editar');
                });
                if (window.bootstrap && bootstrap.Tooltip) {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                } else if ($.fn.tooltip) {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            },
            error: function(xhr) {
                let errorMsg = 'No se pudo realizar la búsqueda';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                Swal.fire('Error', errorMsg, 'error');
            },
            complete: function() {
                hideLoader();
            }
        });
    });

    // Botón flotante para nuevo usuario
    $('#btnNuevoUsuario').on('click', function() {
        limpiarModalUsuario();
        $('#modalUsuario .modal-title').text('Nuevo Usuario');
        $('#modalUsuario').modal('show');
    });

    // Editar usuario
    $('#tablaUsuarios').on('click', '.editar-usuario', function() {
        const id = $(this).data('id');
        $.ajax({
            url: 'usuarios/get',
            type: 'GET',
            data: { id },
            beforeSend: function() {
                showLoader();
            },
            success: function(response) {
                console.log('AJAX editar usuario respuesta:', response);
                if (response.success) {
                    const usuario = response.data;
                    $('#modalUsuario .modal-title').text('Editar Usuario');
                    $('#usuario_id').val(usuario.id);
                    $('#nombre').val(usuario.nombre);
                    $('#email').val(usuario.email);
                    $('#rol_id').val(usuario.rol_id);
                    $('#estado').val(usuario.estado);
                    $('#passwordFields').hide();
                    $('#modalUsuario').modal('show');
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function(xhr) {
                console.log('AJAX editar usuario error:', xhr.responseText);
                let errorMsg = 'No se pudo cargar la información del usuario';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                Swal.fire('Error', errorMsg, 'error');
            },
            complete: function() {
                hideLoader();
            }
        });
    });

    // Eliminar usuario
    $('#tablaUsuarios').on('click', '.eliminar-usuario', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Está seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'usuarios/delete',
                    type: 'POST',
                    data: { id },
                    beforeSend: function() {
                        showLoader();
                    },
                    success: function(response) {
                        console.log('AJAX eliminar usuario respuesta:', response);
                        if (response.success) {
                            Swal.fire('¡Éxito!', response.message, 'success');
                            $('#formBuscarUsuarios').submit();
                        } else {
                            Swal.fire('Error', response.error, 'error');
                        }
                    },
                    error: function(xhr) {
                        console.log('AJAX eliminar usuario error:', xhr.responseText);
                        let errorMsg = 'No se pudo eliminar el usuario';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        Swal.fire('Error', errorMsg, 'error');
                    },
                    complete: function() {
                        hideLoader();
                    }
                });
            }
        });
    });

    // Cambiar estado
    $('#tablaUsuarios').on('change', '.cambiar-estado', function() {
        const id = $(this).data('id');
        const nuevoEstado = $(this).is(':checked') ? 'activo' : 'inactivo';
        $.ajax({
            url: 'usuarios/estado',
            type: 'POST',
            data: { id, estado: nuevoEstado },
            beforeSend: function() {
                showLoader();
            },
            success: function(response) {
                console.log('AJAX cambiar estado respuesta:', response);
                if (response.success) {
                    Swal.fire('¡Éxito!', response.message, 'success');
                    $('#formBuscarUsuarios').submit();
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function(xhr) {
                console.log('AJAX cambiar estado error:', xhr.responseText);
                let errorMsg = 'No se pudo cambiar el estado';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                Swal.fire('Error', errorMsg, 'error');
            },
            complete: function() {
                hideLoader();
            }
        });
    });

    // Guardar usuario (crear/editar)
    $('#formUsuario').on('submit', function(e) {
        e.preventDefault();
        if (!validarFormulario()) {
            return;
        }
        const id = $('#usuario_id').val();
        const url = id ? 'usuarios/update' : 'usuarios/create';
        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            beforeSend: function() {
                showLoader();
            },
            success: function(response) {
                console.log('AJAX guardar usuario respuesta:', response);
                if (response.success) {
                    Swal.fire('¡Éxito!', response.message, 'success');
                    $('#modalUsuario').modal('hide');
                    $('#formBuscarUsuarios').submit();
                } else {
                    Swal.fire('Error', response.error, 'error');
                }
            },
            error: function(xhr) {
                console.log('AJAX guardar usuario error:', xhr.responseText);
                let errorMsg = 'No se pudo procesar la solicitud';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                Swal.fire('Error', errorMsg, 'error');
            },
            complete: function() {
                hideLoader();
            }
        });
    });

    // Limpiar modal al abrir
    function limpiarModalUsuario() {
        $('#formUsuario')[0].reset();
        $('#usuario_id').val('');
        $('#passwordFields').show();
        $('#password').prop('required', true);
        $('#confirm_password').prop('required', true);
    }

    // Log en el contenedor de la tabla
    $('#tablaUsuarios').on('click', function() {
        console.log('Click en la tabla');
    });
});
</script>
JS;
?>

<!-- jQuery y Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Botón flotante para agregar usuario -->
<button id="btnNuevoUsuario" class="btn btn-primary btn-lg rounded-circle shadow position-fixed" style="bottom: 2rem; right: 2rem; z-index: 1050; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;" data-bs-toggle="tooltip" data-bs-placement="left" title="Crear usuario">
    <i class="fas fa-plus fa-lg"></i>
</button>

<!-- Barra de búsqueda y filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form id="formBuscarUsuarios" class="row g-3 mb-2">
            <div class="col-md-4">
                <input type="text" class="form-control" id="buscarNombreUsuario" name="nombre" placeholder="Buscar por nombre o email...">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filtroRol" name="rol">
                    <option value="">Todos los roles</option>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= $rol['nombre'] ?>"><?= $rol['nombre'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filtroEstado" name="estado">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Buscar</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped align-middle" id="tablaUsuarios">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th style="width: 100px;">Estado</th>
                        <th style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php include 'tabla.php'; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUsuario">
                <div class="modal-body">
                    <input type="hidden" id="usuario_id" name="id">
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div id="passwordFields">
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="6">
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rol_id" class="form-label">Rol</label>
                        <select class="form-select" id="rol_id" name="rol_id" required>
                            <option value="">Seleccione un rol</option>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?= $rol['id'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
#btnNuevoUsuario {
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    transition: background 0.2s, box-shadow 0.2s;
}
#btnNuevoUsuario:hover {
    background: #2563eb;
    box-shadow: 0 8px 24px rgba(0,0,0,0.18);
}
</style> 