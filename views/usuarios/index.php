<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Sistema de Monitoreo de Mascotas</title>
    <link rel="icon" type="image/svg+xml" href="http://localhost/proyecto-2/assets/img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="http://localhost/proyecto-2/assets/css/style.css" rel="stylesheet">
    <link href="http://localhost/proyecto-2/assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
<!-- Botón flotante para agregar usuario -->
<?php if ($puedeCrear ?? true): ?>
<button class="fab-crear" id="btnNuevoUsuarioFlotante">
    <i class="fas fa-plus"></i>
    <span class="fab-text">Agregar Usuario</span>
</button>
<?php endif; ?>

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
                        <option value="Administrador">Administrador</option>
                        <option value="Superadministrador">Superadministrador</option>
                        <option value="Usuario">Usuario</option>
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
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="tabla-app" id="tablaUsuarios">
                        <thead>
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th>NOMBRE</th>
                                <th>EMAIL</th>
                                <th>ROL</th>
                                <th>TELÉFONO</th>
                                <th>DIRECCIÓN</th>
                                <th>ESTADO</th>
                                <th>ÚLTIMO ACCESO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td class="id-azul"><?= $usuario['id'] ?></td>
                                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td><?= htmlspecialchars($usuario['rol_nombre'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($usuario['telefono'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($usuario['direccion'] ?? '-') ?></td>
                                <td>
                                    <div class="form-check form-switch d-flex align-items-center mb-0">
                                        <input class="form-check-input cambiar-estado-usuario"
                                            type="checkbox"
                                            data-id="<?= $usuario['id'] ?>"
                                            <?= $usuario['estado'] === 'activo' ? 'checked' : '' ?> >
                                        <label class="form-check-label ms-2">
                                            <?= ucfirst($usuario['estado']) ?>
                                        </label>
                                    </div>
                                </td>
                                <td><?= $usuario['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : 'Nunca' ?></td>
                                <td>
                                    <button class="btn-accion btn-info editar-usuario" data-id="<?= $usuario['id'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-accion btn-danger eliminar-usuario" data-id="<?= $usuario['id'] ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Usuario -->
    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                    <h5 class="modal-title" id="modalUsuarioLabel">Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUsuario" method="POST" action="/proyecto-2/usuarios/create">
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
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono" name="telefono">
                    </div>
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion">
                    </div>
                    <div class="mb-3">
                        <label for="rol_id" class="form-label">Rol</label>
                        <select class="form-select" id="rol_id" name="rol_id" required>
                            <option value="">Seleccione un rol</option>
                            <option value="2">Administrador</option>
                            <option value="1">Superadministrador</option>
                            <option value="3">Usuario</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
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

    <!-- SCRIPTS SOLO UNA VEZ -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Eventos de editar y eliminar usuario -->
    <script>
    $(document).on('click', '.editar-usuario', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        $.ajax({
            url: 'usuarios/get',
            type: 'GET',
            data: { id },
            success: function(response) {
                if (response.success) {
                    const usuario = response.data;
                    $('#modalUsuarioLabel').text('Editar Usuario');
                    $('#usuario_id').val(usuario.id);
                    $('#nombre').val(usuario.nombre);
                    $('#email').val(usuario.email);
                    $('#telefono').val(usuario.telefono || '');
                    $('#direccion').val(usuario.direccion || '');
                    $('#rol_id').val(usuario.rol_id);
                    $('#password').val('');
                    $('#confirm_password').val('');
                    const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
                    modal.show();
                } else {
                    alert('Error al cargar los datos del usuario');
                }
            }
        });
    });

    $(document).on('click', '#btnNuevoUsuarioFlotante', function() {
        $('#modalUsuarioLabel').text('Nuevo Usuario');
        $('#usuario_id').val('');
        $('#nombre').val('');
        $('#email').val('');
        $('#telefono').val('');
        $('#direccion').val('');
        $('#rol_id').val('');
        $('#password').val('');
        $('#confirm_password').val('');
        const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
        modal.show();
    });

    $(document).on('click', '.eliminar-usuario', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        // Consultar asociaciones antes de eliminar
        $.ajax({
            url: 'usuarios/verificarAsociaciones/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let mensaje = '';
                    if ((response.mascotas && response.mascotas.length > 0) || (response.dispositivos && response.dispositivos.length > 0)) {
                        mensaje = '<strong>Este usuario tiene asociaciones:</strong><br>';
                        if (response.mascotas && response.mascotas.length > 0) {
                            mensaje += '<b>Mascotas:</b><ul>';
                            response.mascotas.forEach(m => mensaje += `<li>${m.nombre}</li>`);
                            mensaje += '</ul>';
                        }
                        if (response.dispositivos && response.dispositivos.length > 0) {
                            mensaje += '<b>Dispositivos:</b><ul>';
                            response.dispositivos.forEach(d => mensaje += `<li>${d.nombre}</li>`);
                            mensaje += '</ul>';
                        }
                        mensaje += '<br>¿Deseas continuar? Se eliminarán todas las mascotas y dispositivos asociados.';
                    } else {
                        mensaje = '¿Está seguro de eliminar este usuario?';
                    }
                    Swal.fire({
                        title: 'Confirmar eliminación',
                        html: mensaje,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        customClass: {
                            confirmButton: 'btn btn-danger',
                            cancelButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Eliminar usuario
                            $.ajax({
                                url: 'usuarios/delete',
                                type: 'POST',
                                data: { id },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire('Eliminado', 'Usuario y asociaciones eliminados correctamente', 'success');
                                        $('#formBuscarUsuarios').submit();
                                    } else {
                                        Swal.fire('Error', response.error || 'Error al eliminar el usuario', 'error');
                                    }
                                }
                            });
                        }
                    });
                } else {
                    Swal.fire('Error', 'No se pudo verificar las asociaciones del usuario.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo verificar las asociaciones del usuario.', 'error');
            }
        });
    });

    // Validación de contraseña
    $('#formUsuario').on('submit', function(e) {
        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();
        if (password || confirmPassword) {
            if (password.length < 6) {
                alert('La contraseña debe tener al menos 6 caracteres');
                e.preventDefault();
                return false;
            }
            if (password !== confirmPassword) {
                alert('Las contraseñas no coinciden');
                e.preventDefault();
                return false;
            }
        }
    });

    function validarRequisitos(password) {
        const requisitos = [
            { regex: /.{8,}/, id: 'req-len' },
            { regex: /[A-Z]/, id: 'req-mayus' },
            { regex: /[a-z]/, id: 'req-minus' },
            { regex: /[0-9]/, id: 'req-num' },
            { regex: /[^A-Za-z0-9]/, id: 'req-esp' }
        ];
        let cumple = true;
        requisitos.forEach(r => {
            if (r.regex.test(password)) {
                $('#' + r.id).removeClass('text-danger').addClass('text-success');
            } else {
                $('#' + r.id).removeClass('text-success').addClass('text-danger');
                cumple = false;
            }
        });
        return cumple;
    }

    $('#password').on('input', function() {
        validarRequisitos($(this).val());
    });

    // Búsqueda AJAX para usuarios
    $(document).ready(function() {
        $('#formBuscarUsuarios').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.ajax({
                url: '/proyecto-2/usuarios/tabla?' + formData,
                type: 'GET',
                success: function(data) {
                    $('#tablaUsuarios tbody').html(data);
                },
                error: function() {
                    alert('No se pudo recargar la tabla de usuarios.');
                }
            });
        });
    });

    // Manejar cambio de estado por AJAX en usuarios
    $(document).on('change', '.cambiar-estado-usuario', function() {
        var id = $(this).data('id');
        var nuevoEstado = $(this).is(':checked') ? 'activo' : 'inactivo';
        $.ajax({
            url: '/proyecto-2/usuarios/cambiarEstado/' + id,
            type: 'POST',
            data: { estado: nuevoEstado },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#formBuscarUsuarios').submit(); // Recarga la tabla
                } else {
                    alert(response.error || 'No se pudo actualizar el estado');
                }
            },
            error: function() {
                alert('No se pudo actualizar el estado. Intenta de nuevo.');
            }
        });
    });

    // Envío AJAX para crear/editar usuario
    $('#formUsuario').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();
        var nombreUsuario = $('#nombre').val();
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'El usuario ' + nombreUsuario + ' fue creado exitosamente.'
                    });
                    $('#modalUsuario').modal('hide');
                    $('#formBuscarUsuarios').submit(); // Refresca la tabla
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.error || 'Ocurrió un error al guardar el usuario'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo guardar el usuario. Intenta de nuevo.'
                });
            }
        });
    });
    </script>
</div>
</body>
</html>