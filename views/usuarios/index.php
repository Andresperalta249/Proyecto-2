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
        <form id="formBuscarUsuarios" class="form-filtros d-flex align-items-end gap-2 mb-3">
            <div class="flex-grow-1">
                <input type="text" class="form-control" id="buscarNombreUsuario" name="nombre" placeholder="Buscar por nombre o email...">
            </div>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalFiltrosUsuariosPHP">
                <i class="fas fa-filter"></i> Filtros
            </button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
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
                                    <?php
                                    $rol = strtolower(trim($usuario['rol_nombre'] ?? ''));
                                    if (!in_array($rol, ['administrador', 'superadministrador'])):
                                    ?>
                                    <button class="btn-accion btn-danger eliminar-usuario" data-id="<?= $usuario['id'] ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <?php endif; ?>
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
                <h5 class="modal-title" id="modalUsuarioLabel">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUsuario" method="POST" action="/proyecto-2/usuarios/update">
                <div class="modal-body">
                    <input type="hidden" id="usuario_id" name="id">
                    <!-- Pestañas arriba -->
                    <ul class="nav nav-tabs nav-tabs-usuario mb-3" id="usuarioTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="datos-tab" data-bs-toggle="tab" data-bs-target="#datos" type="button" role="tab" aria-controls="datos" aria-selected="true">
                                <i class="fas fa-user"></i> Datos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab" aria-controls="password" aria-selected="false">
                                <i class="fas fa-lock"></i> Cambiar Contraseña
                            </button>
                        </li>
                    </ul>
                    <style>
                    .nav-tabs-usuario .nav-link {
                        font-size: 1.08rem;
                        padding: 0.7rem 1.5rem;
                        color: #495057;
                        border: 1px solid #dee2e6;
                        border-bottom: none;
                        background: #f8f9fa;
                        margin-right: 2px;
                        transition: background 0.2s, color 0.2s;
                    }
                    .nav-tabs-usuario .nav-link.active {
                        background: #fff;
                        color: #0d6efd;
                        border-bottom: 2px solid #0d6efd;
                        font-weight: 600;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
                    }
                    .nav-tabs-usuario {
                        border-bottom: 1px solid #dee2e6;
                    }
                    </style>
                    <div class="tab-content" id="usuarioTabsContent">
                        <div class="tab-pane fade show active" id="datos" role="tabpanel" aria-labelledby="datos-tab">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" required readonly>
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
                                    <?php if (!empty($roles)):
                                        foreach ($roles as $rol): ?>
                                            <option value="<?= $rol['id'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
                                        <?php endforeach;
                                    endif; ?>
                                </select>
                            </div>
                            <!-- Campos de contraseña solo para nuevo usuario -->
                            <div id="campos-password-nuevo" style="display:none;">
                                <div class="mb-3 position-relative">
                                    <label for="password_nuevo_usuario" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_nuevo_usuario" name="password" autocomplete="new-password">
                                    <span class="toggle-password" style="position:absolute;top:38px;right:15px;cursor:pointer;"><i class="fas fa-eye"></i></span>
                                    <ul class="list-unstyled mt-2 mb-0" id="password-requisitos-nuevo">
                                        <li id="req-len-nuevo" class="text-secondary">• Mínimo 8 caracteres</li>
                                        <li id="req-mayus-nuevo" class="text-secondary">• Al menos una mayúscula</li>
                                        <li id="req-minus-nuevo" class="text-secondary">• Al menos una minúscula</li>
                                        <li id="req-num-nuevo" class="text-secondary">• Al menos un número</li>
                                        <li id="req-esp-nuevo" class="text-secondary">• Al menos un símbolo</li>
                                    </ul>
                                </div>
                                <div class="mb-3 position-relative">
                                    <label for="confirm_password_nuevo_usuario" class="form-label">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="confirm_password_nuevo_usuario" name="confirm_password" autocomplete="new-password">
                                    <span class="toggle-password" style="position:absolute;top:38px;right:15px;cursor:pointer;"><i class="fas fa-eye"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                            <div class="mb-3 position-relative">
                                <label for="password_editar" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="password_editar" name="password" autocomplete="new-password" required>
                                <span class="toggle-password" style="position:absolute;top:38px;right:15px;cursor:pointer;"><i class="fas fa-eye"></i></span>
                                <ul class="list-unstyled mt-2 mb-0" id="password-requisitos-editar">
                                    <li id="req-len-editar" class="text-secondary">• Mínimo 8 caracteres</li>
                                    <li id="req-mayus-editar" class="text-secondary">• Al menos una mayúscula</li>
                                    <li id="req-minus-editar" class="text-secondary">• Al menos una minúscula</li>
                                    <li id="req-num-editar" class="text-secondary">• Al menos un número</li>
                                    <li id="req-esp-editar" class="text-secondary">• Al menos un símbolo</li>
                                </ul>
                            </div>
                            <div class="mb-3 position-relative">
                                <label for="confirm_password_editar" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirm_password_editar" name="confirm_password" autocomplete="new-password" required>
                                <span class="toggle-password" style="position:absolute;top:38px;right:15px;cursor:pointer;"><i class="fas fa-eye"></i></span>
                            </div>
                        </div>
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

<!-- Modal Crear Usuario -->
<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalCrearUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCrearUsuarioLabel">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCrearUsuario" method="POST" action="/proyecto-2/usuarios/create">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre_nuevo" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre_nuevo" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="email_nuevo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email_nuevo" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefono_nuevo" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono_nuevo" name="telefono">
                    </div>
                    <div class="mb-3">
                        <label for="direccion_nuevo" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="direccion_nuevo" name="direccion">
                    </div>
                    <div class="mb-3">
                        <label for="rol_id_nuevo" class="form-label">Rol</label>
                        <select class="form-select" id="rol_id_nuevo" name="rol_id" required>
                            <option value="">Seleccione un rol</option>
                            <?php if (!empty($roles)):
                                foreach ($roles as $rol): ?>
                                    <option value="<?= $rol['id'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
                                <?php endforeach;
                            endif; ?>
                        </select>
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="password_nuevo" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password_nuevo" name="password" autocomplete="new-password" required>
                        <span class="toggle-password" style="position:absolute;top:38px;right:15px;cursor:pointer;"><i class="fas fa-eye"></i></span>
                        <ul class="list-unstyled mt-2 mb-0" id="password-requisitos-nuevo">
                            <li id="req-len-nuevo" class="text-secondary">• Mínimo 8 caracteres</li>
                            <li id="req-mayus-nuevo" class="text-secondary">• Al menos una mayúscula</li>
                            <li id="req-minus-nuevo" class="text-secondary">• Al menos una minúscula</li>
                            <li id="req-num-nuevo" class="text-secondary">• Al menos un número</li>
                            <li id="req-esp-nuevo" class="text-secondary">• Al menos un símbolo</li>
                        </ul>
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="confirm_password_nuevo" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="confirm_password_nuevo" name="confirm_password" autocomplete="new-password" required>
                        <span class="toggle-password" style="position:absolute;top:38px;right:15px;cursor:pointer;"><i class="fas fa-eye"></i></span>
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

<!-- Modal de Filtros Avanzados para usuarios -->
<div class="modal fade" id="modalFiltrosUsuariosPHP" tabindex="-1" aria-labelledby="modalFiltrosUsuariosPHPLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFiltrosUsuariosPHPLabel">Filtros Avanzados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formFiltrosUsuariosPHP">
                    <div class="mb-3">
                        <label for="filtroRolUsuario" class="form-label">Rol</label>
                        <select class="form-select" id="filtroRolUsuario" name="rol">
                            <option value="">Todos los roles</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Superadministrador">Superadministrador</option>
                            <option value="Usuario">Usuario</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filtroEstadoUsuario" class="form-label">Estado</label>
                        <select class="form-select" id="filtroEstadoUsuario" name="estado">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="aplicarFiltrosUsuariosPHP">Aplicar Filtros</button>
            </div>
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
                    // Limpiar estado visual
                    $('#usuarioTabs').show();
                    $('#usuarioTabsContent').show();
                    $('#datos').show();
                    $('#msg-rol-restriccion').remove();
                    $('#msg-restriccion').remove();
                    $('#email').prop('readonly', true);
                    $('#rol_id').prop('disabled', false);
                    $('#password').prop('disabled', false);
                    $('#confirm_password').prop('disabled', false);

                    $('#modalUsuarioLabel').text('Editar Usuario');
                    $('#usuario_id').val(usuario.id);
                    $('#nombre').val(usuario.nombre);
                    $('#email').val(usuario.email);
                    $('#telefono').val(usuario.telefono || '');
                    $('#direccion').val(usuario.direccion || '');
                    $('#rol_id').val(usuario.rol_id);
                    $('#password').val('');
                    $('#confirm_password').val('');

                    // Lógica para mostrar solo la pestaña de contraseña si es superadmin o admin
                    const rolNombre = (usuario.rol_nombre || '').toLowerCase();
                    if (rolNombre === 'superadministrador' || rolNombre === 'administrador') {
                        $('#usuarioTabs .nav-link').hide();
                        $('#password-tab').show().addClass('active');
                        $('#usuarioTabsContent .tab-pane').removeClass('show active');
                        $('#password').closest('.tab-pane').addClass('show active');
                    } else {
                        $('#usuarioTabs .nav-link').show();
                        $('#datos-tab').addClass('active');
                        $('#password-tab').removeClass('active');
                        $('#usuarioTabsContent .tab-pane').removeClass('show active');
                        $('#datos').addClass('show active');
                    }

                    // Bloquear cambio de rol si es superadministrador (usando rol_id)
                    if (usuario.rol_id == 1) { // 1 es el ID de Superadministrador
                        $('#rol_id').prop('disabled', true).blur();
                        if ($('#msg-rol-restriccion').length === 0) {
                            $('#rol_id').parent().append('<div id="msg-rol-restriccion" class="text-danger mt-2">No puedes cambiar el rol de un Superadministrador.</div>');
                        }
                    } else {
                        $('#rol_id').prop('disabled', false);
                        $('#msg-rol-restriccion').remove();
                    }

                    // Si el usuario logueado es admin y el usuario a editar es superadmin, deshabilitar el cambio de contraseña
                    const usuarioLogueadoRol = '<?= strtolower($_SESSION['rol_nombre'] ?? '') ?>';
                    const usuarioLogueadoId = <?= $_SESSION['user_id'] ?? 0 ?>;
                    
                    // Lógica de restricción de cambio de contraseña
                    if (rolNombre === 'superadministrador') {
                        if (usuarioLogueadoRol === 'superadministrador' && usuarioLogueadoId === usuario.id) {
                            $('#password').prop('disabled', false);
                            $('#confirm_password').prop('disabled', false);
                            $('#msg-restriccion').remove();
                            $('#password-tab').css('display', ''); // Mostrar
                        } else {
                            $('#password').prop('disabled', true);
                            $('#confirm_password').prop('disabled', true);
                            if ($('#msg-restriccion').length === 0) {
                                $('#password').parent().append('<div id="msg-restriccion" class="text-danger mt-2">Solo los superadministradores pueden cambiar sus propias contraseñas. Los roles inferiores no pueden modificar contraseñas de superadministradores.</div>');
                            }
                            // Ocultar la pestaña y forzar la de datos como activa
                            $('#password-tab').css('display', 'none');
                            $('#password-tab').removeClass('active');
                            $('#datos-tab').addClass('active');
                            $('#usuarioTabsContent .tab-pane').removeClass('show active');
                            $('#datos').addClass('show active');
                        }
                    } else {
                        $('#password').prop('disabled', false);
                        $('#confirm_password').prop('disabled', false);
                        $('#msg-restriccion').remove();
                        $('#password-tab').css('display', ''); // Mostrar
                    }

                    // Restaurar la pestaña y el contenido de 'Cambiar Contraseña' si no existen
                    if (!$('#password-tab').length) {
                        $("<li class='nav-item' role='presentation'><button class='nav-link' id='password-tab' data-bs-toggle='tab' data-bs-target='#password' type='button' role='tab' aria-controls='password' aria-selected='false'><i class='fas fa-lock'></i> Cambiar Contraseña</button></li>").insertAfter('#datos-tab');
                    }
                    if (!$('#usuarioTabsContent #password').length) {
                        var passwordTabContent = `<div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                            <div class="mb-3 position-relative">
                                <label for="password" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                                <span class="toggle-password" style="position:absolute;top:38px;right:15px;cursor:pointer;"><i class="fas fa-eye"></i></span>
                                <ul class="list-unstyled mt-2 mb-0" id="password-requisitos">
                                    <li id="req-len" class="text-secondary">• Mínimo 8 caracteres</li>
                                    <li id="req-mayus" class="text-secondary">• Al menos una mayúscula</li>
                                    <li id="req-minus" class="text-secondary">• Al menos una minúscula</li>
                                    <li id="req-num" class="text-secondary">• Al menos un número</li>
                                    <li id="req-esp" class="text-secondary">• Al menos un símbolo</li>
                                </ul>
                            </div>
                            <div class="mb-3 position-relative">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" autocomplete="new-password">
                                <span class="toggle-password" style="position:absolute;top:38px;right:15px;cursor:pointer;"><i class="fas fa-eye"></i></span>
                            </div>
                        </div>`;
                        $('#usuarioTabsContent').append(passwordTabContent);
                    }

                    // Limpiar clases activas para evitar remontes
                    $('#usuarioTabs .nav-link').removeClass('active');
                    $('#usuarioTabsContent .tab-pane').removeClass('show active');
                    $('#datos-tab').addClass('active');
                    $('#datos').addClass('show active');

                    // Eliminar del DOM los campos de contraseña de nuevo usuario si existen
                    if ($('#campos-password-nuevo').length) {
                        $('#campos-password-nuevo').remove();
                    }

                    const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
                    modal.show();
                } else {
                    alert('Error al cargar los datos del usuario');
                }
            }
        });
        filtrarOpcionesRolPorPermiso();
    });

    $(document).on('click', '#btnNuevoUsuarioFlotante', function() {
        $('#formCrearUsuario')[0].reset();
        filtrarOpcionesRolCrearPorPermiso();
        $('#modalCrearUsuario').modal('show');
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
                                dataType: 'json',
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire('Eliminado', 'Usuario y asociaciones eliminados correctamente', 'success');
                                        $('#formBuscarUsuarios').submit();
                                    } else {
                                        Swal.fire('Error', response.error || 'Error al eliminar el usuario', 'error');
                                    }
                                },
                                error: function(xhr) {
                                    let errorMsg = 'No se pudo eliminar el usuario. Intenta de nuevo.';
                                    if (xhr.responseJSON && xhr.responseJSON.error) {
                                        errorMsg = xhr.responseJSON.error;
                                    }
                                    Swal.fire('Error', errorMsg, 'error');
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

    $(document).ready(function() {
        function validarPasswordRealtime() {
            $('#password').off('input').on('input', function() {
                const password = $(this).val();
                const requisitos = [
                    { regex: /.{8,}/, id: 'req-len' },
                    { regex: /[A-Z]/, id: 'req-mayus' },
                    { regex: /[a-z]/, id: 'req-minus' },
                    { regex: /[0-9]/, id: 'req-num' },
                    { regex: /[^A-Za-z0-9]/, id: 'req-esp' }
                ];
                requisitos.forEach(r => {
                    if (password.length === 0) {
                        $('#' + r.id).removeClass('text-danger text-success').addClass('text-secondary');
                    } else if (r.regex.test(password)) {
                        $('#' + r.id).removeClass('text-danger text-secondary').addClass('text-success');
                    } else {
                        $('#' + r.id).removeClass('text-success text-secondary').addClass('text-danger');
                    }
                });
            });
        }
        validarPasswordRealtime();
        $('#modalUsuario').on('shown.bs.modal', function () {
            validarPasswordRealtime();
        });
        // Mostrar/ocultar contraseña (referencia relativa al input hermano)
        $(document).on('click', '.toggle-password', function() {
            const input = $(this).siblings('input');
            const icon = $(this).find('i');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
        // Validación al enviar el formulario
        $('#formUsuario').off('submit').on('submit', function(e) {
            const password = $('#password').val();
            const requisitos = [
                { regex: /.{8,}/, id: 'req-len' },
                { regex: /[A-Z]/, id: 'req-mayus' },
                { regex: /[a-z]/, id: 'req-minus' },
                { regex: /[0-9]/, id: 'req-num' },
                { regex: /[^A-Za-z0-9]/, id: 'req-esp' }
            ];
            let cumple = true;
            if (password.length > 0) {
                requisitos.forEach(r => {
                    if (!r.regex.test(password)) {
                        $('#' + r.id).removeClass('text-success text-secondary').addClass('text-danger');
                        cumple = false;
                    }
                });
                if (!cumple) {
                    e.preventDefault();
                    return false;
                }
            }
        });
        // Validación en tiempo real para nuevo usuario
        function validarPasswordNuevoRealtime() {
            $('#password_nuevo_usuario').off('input').on('input', function() {
                const password = $(this).val();
                const requisitos = [
                    { regex: /.{8,}/, id: 'req-len-nuevo' },
                    { regex: /[A-Z]/, id: 'req-mayus-nuevo' },
                    { regex: /[a-z]/, id: 'req-minus-nuevo' },
                    { regex: /[0-9]/, id: 'req-num-nuevo' },
                    { regex: /[^A-Za-z0-9]/, id: 'req-esp-nuevo' }
                ];
                requisitos.forEach(r => {
                    if (password.length === 0) {
                        $('#' + r.id).removeClass('text-danger text-success').addClass('text-secondary');
                    } else if (r.regex.test(password)) {
                        $('#' + r.id).removeClass('text-danger text-secondary').addClass('text-success');
                    } else {
                        $('#' + r.id).removeClass('text-success text-secondary').addClass('text-danger');
                    }
                });
            });
        }

        function validarPasswordEditarRealtime() {
            $('#password_editar').off('input').on('input', function() {
                const password = $(this).val();
                const requisitos = [
                    { regex: /.{8,}/, id: 'req-len-editar' },
                    { regex: /[A-Z]/, id: 'req-mayus-editar' },
                    { regex: /[a-z]/, id: 'req-minus-editar' },
                    { regex: /[0-9]/, id: 'req-num-editar' },
                    { regex: /[^A-Za-z0-9]/, id: 'req-esp-editar' }
                ];
                requisitos.forEach(r => {
                    if (password.length === 0) {
                        $('#' + r.id).removeClass('text-danger text-success').addClass('text-secondary');
                    } else if (r.regex.test(password)) {
                        $('#' + r.id).removeClass('text-danger text-secondary').addClass('text-success');
                    } else {
                        $('#' + r.id).removeClass('text-success text-secondary').addClass('text-danger');
                    }
                });
            });
        }

        validarPasswordNuevoRealtime();
        validarPasswordEditarRealtime();
        
        $('#modalUsuario').on('shown.bs.modal', function () {
            validarPasswordNuevoRealtime();
            validarPasswordEditarRealtime();
        });

        // Validación al enviar el formulario para nuevo usuario
        $('#formUsuario').off('submit').on('submit', function(e) {
            // Validar campos de contraseña para nuevo usuario
            if ($('#campos-password-nuevo').is(':visible')) {
                const passwordNuevo = $('#password_nuevo_usuario').val();
                const confirmPasswordNuevo = $('#confirm_password_nuevo_usuario').val();
                const requisitosNuevo = [
                    { regex: /.{8,}/, id: 'req-len-nuevo' },
                    { regex: /[A-Z]/, id: 'req-mayus-nuevo' },
                    { regex: /[a-z]/, id: 'req-minus-nuevo' },
                    { regex: /[0-9]/, id: 'req-num-nuevo' },
                    { regex: /[^A-Za-z0-9]/, id: 'req-esp-nuevo' }
                ];
                let cumpleNuevo = true;
                requisitosNuevo.forEach(r => {
                    if (!r.regex.test(passwordNuevo)) {
                        $('#' + r.id).removeClass('text-success text-secondary').addClass('text-danger');
                        cumpleNuevo = false;
                    }
                });
                if (!cumpleNuevo) {
                    e.preventDefault();
                    return false;
                }
                if (passwordNuevo !== confirmPasswordNuevo) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Las contraseñas no coinciden.'
                    });
                    return false;
                }
            }

            // Validar campos de contraseña para edición
            if ($('#password_editar').is(':visible')) {
                const passwordEditar = $('#password_editar').val();
                const confirmPasswordEditar = $('#confirm_password_editar').val();
                const requisitosEditar = [
                    { regex: /.{8,}/, id: 'req-len-editar' },
                    { regex: /[A-Z]/, id: 'req-mayus-editar' },
                    { regex: /[a-z]/, id: 'req-minus-editar' },
                    { regex: /[0-9]/, id: 'req-num-editar' },
                    { regex: /[^A-Za-z0-9]/, id: 'req-esp-editar' }
                ];
                let cumpleEditar = true;
                requisitosEditar.forEach(r => {
                    if (!r.regex.test(passwordEditar)) {
                        $('#' + r.id).removeClass('text-success text-secondary').addClass('text-danger');
                        cumpleEditar = false;
                    }
                });
                if (!cumpleEditar) {
                    e.preventDefault();
                    return false;
                }
                if (passwordEditar !== confirmPasswordEditar) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Las contraseñas no coinciden.'
                    });
                    return false;
                }
            }
        });
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

    function filtrarOpcionesRolPorPermiso() {
        var usuarioLogueadoRol = '<?= strtolower($_SESSION['rol_nombre'] ?? '') ?>';
        if (usuarioLogueadoRol !== 'superadministrador') {
            $("#rol_id option[value='1']").hide(); // Oculta Superadministrador
        } else {
            $("#rol_id option[value='1']").show();
        }
    }

    // Filtro de roles para el modal de crear usuario
    function filtrarOpcionesRolCrearPorPermiso() {
        var usuarioLogueadoRol = '<?= strtolower($_SESSION['rol_nombre'] ?? '') ?>';
        if (usuarioLogueadoRol !== 'superadministrador') {
            $("#rol_id_nuevo option[value='1']").hide(); // Oculta Superadministrador
        } else {
            $("#rol_id_nuevo option[value='1']").show();
        }
    }

    // Envío AJAX para crear usuario
    $('#formCrearUsuario').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();
        var nombreUsuario = $('#nombre_nuevo').val();
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
                    $('#modalCrearUsuario').modal('hide');
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
    <script>
    // Sincronizar filtros avanzados con el formulario principal
    document.getElementById('aplicarFiltrosUsuariosPHP').addEventListener('click', function() {
        // Obtener valores del modal
        var rol = document.getElementById('filtroRolUsuario').value;
        var estado = document.getElementById('filtroEstadoUsuario').value;
        // Asignar a campos ocultos o crear si no existen
        let form = document.getElementById('formBuscarUsuarios');
        let inputRol = form.querySelector('input[name="rol"]');
        let inputEstado = form.querySelector('input[name="estado"]');
        if (!inputRol) {
            inputRol = document.createElement('input');
            inputRol.type = 'hidden';
            inputRol.name = 'rol';
            form.appendChild(inputRol);
        }
        if (!inputEstado) {
            inputEstado = document.createElement('input');
            inputEstado.type = 'hidden';
            inputEstado.name = 'estado';
            form.appendChild(inputEstado);
        }
        inputRol.value = rol;
        inputEstado.value = estado;
        // Cerrar modal y disparar búsqueda
        var modal = bootstrap.Modal.getInstance(document.getElementById('modalFiltrosUsuariosPHP'));
        modal.hide();
        form.dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}));
    });
    </script>
</div>
</body>
</html>