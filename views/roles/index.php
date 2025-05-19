<?php
// No header_buttons, solo botón flotante
?>
<?php if (verificarPermiso('crear_roles')): ?>
<button class="fab-crear" id="btnNuevoRolFlotante">
    <i class="fas fa-plus"></i>
    <span class="fab-text">Crear Rol</span>
</button>
<?php endif; ?>
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <form id="formBuscar" class="form-filtros d-flex align-items-end gap-2 mb-3">
                <div class="flex-grow-1">
                    <input type="text" class="form-control" id="txtBuscarNombreRol" placeholder="Buscar por nombre...">
                </div>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalFiltrosRolesPHP">
                    <i class="fas fa-filter"></i> Filtros
                </button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
            </form>
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="tabla-app" id="tablaRoles">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">ID</th>
                                    <th>NOMBRE</th>
                                    <th>ESTADO</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th>DETALLES</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyRoles">
                                <?php foreach ($roles as $rol): ?>
                                <tr>
                                    <td class="id-azul"><?= $rol['id'] ?></td>
                                    <td><?= htmlspecialchars($rol['nombre']) ?></td>
                                    <td>
                                        <?php $estado = isset($rol['estado']) && $rol['estado'] ? $rol['estado'] : 'inactivo'; ?>
                                        <?php if ($rol['id'] > 3 && verificarPermiso('editar_roles')): ?>
                                            <div class="form-check form-switch d-flex align-items-center mb-0">
                                                <input class="form-check-input cambiar-estado-rol" type="checkbox" data-id="<?= $rol['id'] ?>" <?= ($estado === 'activo') ? 'checked' : '' ?> >
                                                <label class="form-check-label ms-2">
                                                    <?= ucfirst($estado) ?>
                                                </label>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-<?= $estado === 'activo' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($estado) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($rol['descripcion'] ?? 'Sin descripción') ?></td>
                                    <td>
                                        <button class="btn-accion btn-primary ver-detalles" data-id="<?= $rol['id'] ?>" data-bs-toggle="modal" data-bs-target="#modalDetalles">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="22" height="22">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12z" />
                                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" fill="none" />
                                            </svg>
                                        </button>
                                    </td>
                                    <td>
                                        <?php if ($rol['id'] > 3): ?>
                                            <?php if (verificarPermiso('editar_roles')): ?>
                                            <button class="btn-accion btn-info editar-rol" data-id="<?= $rol['id'] ?>" data-bs-toggle="modal" data-bs-target="#modalRol">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if (verificarPermiso('eliminar_roles')): ?>
                                            <button class="btn-accion btn-danger eliminar-rol" data-id="<?= $rol['id'] ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted" title="Rol protegido"><i class="fas fa-lock"></i> No editable</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div id="rolesInfo"></div>
                <nav>
                    <ul class="pagination mb-0" id="rolesPagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rol -->
<div class="modal fade" id="modalRol" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRol">
                <div class="modal-body">
                    <input type="hidden" id="rol_id" name="id">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Rol</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2" placeholder="Describe brevemente el rol"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permisos</label>
                        <input type="text" class="form-control mb-2" id="buscarPermiso" placeholder="Buscar permiso...">
                        <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                            <?php
                            $categorias = [
                                'Usuarios' => ['crear_usuarios', 'editar_usuarios', 'eliminar_usuarios', 'ver_usuarios', 'cambiar_estado_usuarios'],
                                'Roles' => ['crear_roles', 'editar_roles', 'eliminar_roles', 'ver_roles'],
                                'Mascotas' => ['crear_mascotas', 'editar_mascotas', 'eliminar_mascotas', 'ver_mascotas', 'editar_cualquier_mascota', 'cambiar_estado_mascotas'],
                                'Dispositivos' => [
                                    'ver_dispositivos',
                                    'ver_todos_dispositivo',
                                    'crear_dispositivos',
                                    'editar_dispositivos',
                                    'eliminar_dispositivos',
                                    'cambiar_estado_dispositivos'
                                ]
                            ];
                            foreach ($categorias as $cat => $codigos): ?>
                                <div class="mb-2">
                                    <strong><?= $cat ?></strong>
                                    <div class="row">
                                    <?php foreach ($permisos as $permiso):
                                        if (in_array($permiso['codigo'], $codigos)): ?>
                                            <div class="col-12 col-md-6">
                                                <div class="form-check" title="<?= htmlspecialchars($permiso['nombre']) ?>">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" 
                                                           name="permisos[]" 
                                                           value="<?= $permiso['id'] ?>" 
                                                           id="permiso_<?= $permiso['id'] ?>">
                                                    <label class="form-check-label" for="permiso_<?= $permiso['id'] ?>">
                                                        <?= htmlspecialchars($permiso['nombre']) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endif;
                                    endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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

<!-- Modal Detalles -->
<div class="modal fade" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetallesLabel">Detalles del Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Información General</h6>
                                <p class="mb-1"><strong>ID:</strong> <span id="detalleId"></span></p>
                                <p class="mb-1"><strong>Nombre:</strong> <span id="detalleNombre"></span></p>
                                <p class="mb-1"><strong>Descripción:</strong> <span id="detalleDescripcion"></span></p>
                                <p class="mb-0"><strong>Estado:</strong> <span id="detalleEstado"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Permisos Asignados</h6>
                                <div id="detallePermisos" class="permisos-grid"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Filtros Avanzados para roles -->
<div class="modal fade" id="modalFiltrosRolesPHP" tabindex="-1" aria-labelledby="modalFiltrosRolesPHPLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFiltrosRolesPHPLabel">Filtros Avanzados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formFiltrosRolesPHP">
                    <div class="mb-3">
                        <label for="filtroEstadoRol" class="form-label">Estado</label>
                        <select class="form-select" id="filtroEstadoRol" name="estado">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="aplicarFiltrosRolesPHP">Aplicar Filtros</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Unificación de filtros */
.form-filtros {
    display: flex;
    gap: 12px;
    align-items: end;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.form-filtros .form-control, .form-filtros .form-select {
    min-width: 180px;
    max-width: 260px;
}
.form-filtros .btn {
    min-width: 120px;
}

/* Botones de acción */
.btn-accion {
    font-size: 1.1rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(37,99,235,0.08);
    margin: 0 4px;
    padding: 6px 10px;
    transition: box-shadow 0.2s, background 0.2s;
}
.btn-accion:focus, .btn-accion:hover {
    box-shadow: 0 4px 12px rgba(37,99,235,0.15);
    background: #f3f6fa;
}
.btn-accion[title] {
    position: relative;
}

/* Tabla visual */
.tabla-app {
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.tabla-app th, .tabla-app td {
    vertical-align: middle;
    padding: 12px 8px;
    border: none;
}
.tabla-app th {
    background: #f8f9fa;
    font-weight: 600;
    text-align: center;
    color: #222;
}
.tabla-app td {
    color: #222;
    text-align: center;
}
.tabla-app td:first-child, .tabla-app th:first-child {
    text-align: center;
}
.tabla-app tbody tr:hover {
    background: #e9f5ff;
    transition: background 0.2s;
}

/* Feedback visual */
.tabla-app .cargando, .tabla-app .sin-resultados {
    text-align: center;
    color: #888;
    font-style: italic;
    padding: 24px 0;
}

/* Responsive: ocultar columnas menos importantes */
@media (max-width: 700px) {
    .tabla-app th:nth-child(4), .tabla-app td:nth-child(4),
    .tabla-app th:nth-child(5), .tabla-app td:nth-child(5) {
        display: none;
    }
}

.permisos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.permiso-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.permiso-item i {
    color: #0d6efd;
}

.permiso-item.active {
    background: #e7f1ff;
    border-color: #0d6efd;
}

.fab-crear {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #0d6efd;
    color: white;
    border: none;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    z-index: 1000;
    overflow: hidden;
}

.fab-crear:hover {
    width: 150px;
    border-radius: 25px;
    background-color: #0b5ed7;
}

.fab-crear i {
    font-size: 20px;
    transition: all 0.3s ease;
}

.fab-crear .fab-text {
    font-size: 16px;
    font-weight: 500;
    white-space: nowrap;
    opacity: 0;
    transform: translateX(20px);
    transition: all 0.3s ease;
    margin-left: 10px;
}

.fab-crear:hover .fab-text {
    opacity: 1;
    transform: translateX(0);
}

.fab-crear:hover i {
    transform: translateX(-5px);
}
</style>

<link href="<?= APP_URL ?>/assets/css/app.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Tippy.js para tooltips modernos -->
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<script>
// --- Paginación y filtrado personalizado ---
let rolesData = <?php echo json_encode($roles); ?>;
let currentPage = 1;
const pageSize = 10;

function agruparPermisos(permisos) {
    let html = `<div style='font-weight:bold; font-size:15px; margin-bottom:6px;'>Permisos asignados</div>`;
    const modulos = {};
    permisos.forEach(permiso => {
        let modulo = 'Otros';
        if (/usuario/i.test(permiso)) modulo = 'Usuarios';
        else if (/mascota/i.test(permiso)) modulo = 'Mascotas';
        else if (/dispositivo/i.test(permiso)) modulo = 'Dispositivos';
        else if (/rol/i.test(permiso)) modulo = 'Roles';
        else if (/reporte/i.test(permiso)) modulo = 'Reportes';
        else if (/alerta/i.test(permiso)) modulo = 'Alertas';
        else if (/monitor/i.test(permiso)) modulo = 'Monitor';
        else if (/configuraci/i.test(permiso)) modulo = 'Configuración';
        if (!modulos[modulo]) modulos[modulo] = [];
        modulos[modulo].push(permiso);
    });
    Object.keys(modulos).forEach(modulo => {
        html += `<div style='margin-bottom:4px;'><strong>${modulo}</strong></div>`;
        modulos[modulo].forEach(permiso => {
            html += `<div style='font-size:13px; margin-left:10px;'><i class='fas fa-check-circle text-primary'></i> ${permiso}</div>`;
        });
    });
    return html;
}

function renderRolesTable(data) {
    const tbody = $('#tbodyRoles');
    tbody.empty();
    if (data.length === 0) {
        tbody.append('<tr><td colspan="5" class="text-center">No hay roles para mostrar</td></tr>');
        return;
    }
    data.forEach(rol => {
        let estadoHtml = '';
        if (rol.id > 3 && <?= json_encode(verificarPermiso('editar_roles')) ?>) {
            estadoHtml = `<div class='form-check form-switch d-flex align-items-center mb-0'>
                <input class='form-check-input cambiar-estado-rol' type='checkbox' data-id='${rol.id}' ${rol.estado === 'activo' ? 'checked' : ''} >
                <label class='form-check-label ms-2'>${rol.estado.charAt(0).toUpperCase() + rol.estado.slice(1)}</label>
            </div>`;
        } else {
            estadoHtml = `<span class='badge bg-${rol.estado === 'activo' ? 'success' : 'danger'}'>${rol.estado.charAt(0).toUpperCase() + rol.estado.slice(1)}</span>`;
        }
        let accionesHtml = '';
        if (rol.id > 3) {
            if (<?= json_encode(verificarPermiso('editar_roles')) ?>) {
                accionesHtml += `<button class='btn-accion btn-info editar-rol' data-id='${rol.id}' data-bs-toggle='modal' data-bs-target='#modalRol'><i class='fas fa-edit'></i></button>`;
            }
            if (<?= json_encode(verificarPermiso('eliminar_roles')) ?>) {
                accionesHtml += `<button class='btn-accion btn-danger eliminar-rol' data-id='${rol.id}'><i class='fas fa-trash-alt'></i></button>`;
            }
        } else {
            accionesHtml = `<span class='text-muted' title='Rol protegido'><i class='fas fa-lock'></i> No editable</span>`;
        }
        // Tooltip de permisos
        let permisosTooltip = '';
        if (rol.permisos && rol.permisos !== 'Sin permisos') {
            const permisos = rol.permisos.split(',').map(p => p.trim());
            permisosTooltip = agruparPermisos(permisos);
        } else {
            permisosTooltip = '<div style="font-weight:bold; font-size:15px; margin-bottom:6px;">Permisos asignados</div><div style="color:#dc3545; font-size:14px;">Sin permisos asignados</div>';
        }
        tbody.append(`
            <tr>
                <td class='id-azul'>${rol.id}</td>
                <td>${rol.nombre}</td>
                <td>${estadoHtml}</td>
                <td>${rol.descripcion || 'Sin descripción'}</td>
                <td>
                    <button class="btn-accion btn-primary ver-detalles" data-tippy-content="${permisosTooltip.replace(/\"/g, '&quot;')}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="22" height="22">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12z" />
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" fill="none" />
                        </svg>
                    </button>
                </td>
                <td>${accionesHtml}</td>
            </tr>
        `);
    });
    // Inicializar tooltips modernos
    tippy('.ver-detalles', {
        allowHTML: true,
        theme: 'light-border',
        interactive: true,
        placement: 'right',
        maxWidth: 350,
        animation: 'scale',
        delay: [100, 100],
    });
}

function renderPagination(total, page, pageSize) {
    const totalPages = Math.ceil(total / pageSize);
    const pagination = $('#rolesPagination');
    pagination.empty();
    if (totalPages <= 1) return;
    for (let i = 1; i <= totalPages; i++) {
        pagination.append(`<li class='page-item${i === page ? ' active' : ''}'><a class='page-link' href='#' data-page='${i}'>${i}</a></li>`);
    }
}

function updateTable() {
    let nombre = $('#txtBuscarNombreRol').val().toLowerCase();
    let estado = $('#cmbEstadoRol').val();
    let filtered = rolesData.filter(rol => {
        let matchNombre = rol.nombre.toLowerCase().includes(nombre);
        let matchEstado = !estado || rol.estado === estado;
        return matchNombre && matchEstado;
    });
    let total = filtered.length;
    let start = (currentPage - 1) * pageSize;
    let end = start + pageSize;
    let pageData = filtered.slice(start, end);
    renderRolesTable(pageData);
    renderPagination(total, currentPage, pageSize);
    $('#rolesInfo').text(`Mostrando ${start + 1} a ${Math.min(end, total)} de ${total} roles`);
}

$(document).ready(function() {
    updateTable();
    $('#formBuscar').on('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        updateTable();
    });
    $('#rolesPagination').on('click', 'a', function(e) {
        e.preventDefault();
        currentPage = parseInt($(this).data('page'));
        updateTable();
    });
    // Botón flotante para nuevo rol
    $('#btnNuevoRolFlotante').on('click', function() {
        $('#modalRol .modal-title').text('Nuevo Rol');
        $('#rol_id').val('');
        $('#formRol')[0].reset();
        $('.permiso-checkbox').prop('checked', false);
        $('#modalRol').modal('show');
    });
    // Cambiar estado de rol
    $(document).on('change', '.cambiar-estado-rol', function() {
        const id = $(this).data('id');
        const estado = $(this).is(':checked') ? 'activo' : 'inactivo';
        $.post('<?= APP_URL ?>/roles/cambiarEstado', { id, estado }, function(response) {
            if (response.success) {
                Swal.fire('¡Éxito!', response.message, 'success');
            } else {
                Swal.fire('Error', response.error, 'error');
            }
        });
    });
    // Evento para el botón de editar rol
    $(document).on('click', '.editar-rol', function() {
        const id = $(this).data('id');
        cargarDetallesRol(id);
    });

    // Función para cargar los detalles del rol
    function cargarDetallesRol(id) {
        $.get('<?= APP_URL ?>/roles/get', { id: id }, function(response) {
            try {
                if (typeof response === 'string') {
                    response = JSON.parse(response);
                }
                
                if (response.success) {
                    const rol = response.data;
                    $('#modalRol .modal-title').text('Editar Rol');
                    $('#rol_id').val(rol.id);
                    $('#nombre').val(rol.nombre);
                    $('#descripcion').val(rol.descripcion);
                    $('#estado').val(rol.estado);
                    
                    // Limpiar y marcar los permisos
                    $('.permiso-checkbox').prop('checked', false);
                    if (rol.permiso_ids && rol.permiso_ids.length > 0) {
                        rol.permiso_ids.forEach(id => {
                            $(`#permiso_${id}`).prop('checked', true);
                        });
                    }
                    
                    $('#modalRol').modal('show');
                } else {
                    Swal.fire('Error', response.error || 'Error al cargar los datos del rol', 'error');
                }
            } catch (e) {
                console.error('Error al procesar la respuesta:', e);
                Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
            }
        }).fail(function(xhr, status, error) {
            console.error('Error en la petición:', error);
            Swal.fire('Error', 'Error al cargar los datos del rol', 'error');
        });
    }

    // Manejar envío del formulario de rol
    $('#formRol').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const permisos = [];
        $('.permiso-checkbox:checked').each(function() {
            permisos.push($(this).val());
        });
        formData.append('permisos', JSON.stringify(permisos));

        const url = $('#rol_id').val() ? '<?= APP_URL ?>/roles/update' : '<?= APP_URL ?>/roles/create';
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    if (response.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.message,
                            icon: 'success'
                        }).then(() => {
                            $('#modalRol').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.error || 'Error al procesar la solicitud', 'error');
                    }
                } catch (e) {
                    console.error('Error al procesar la respuesta:', e);
                    Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición:', error);
                Swal.fire('Error', 'Error al procesar la solicitud', 'error');
            }
        });
    });

    // Manejar eliminación de rol
    $(document).on('click', '.eliminar-rol', function() {
        const id = $(this).data('id');
        // Primero, consultar la cantidad de usuarios asociados
        $.post('<?= APP_URL ?>/roles/usuariosAsociados', { id: id }, function(resp) {
            let cantidad = 0;
            if (typeof resp === 'string') {
                try { resp = JSON.parse(resp); } catch(e) { resp = {}; }
            }
            if (resp.success) cantidad = resp.cantidad;
            let mensaje = 'Esta acción no se puede deshacer.';
            if (cantidad > 0) {
                mensaje = `Este rol tiene <b>${cantidad}</b> usuario(s) asignado(s). Si continúas, los usuarios afectados perderán el acceso y al intentar iniciar sesión verán un mensaje indicando que no tienen rol asignado y deben contactar a superadmin@petcare.com.<br><br>¿Deseas continuar y eliminar el rol?`;
            }
            Swal.fire({
                title: '¿Estás seguro?',
                html: mensaje,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= APP_URL ?>/roles/delete',
                        type: 'POST',
                        data: { id: id, forzar: cantidad > 0 ? 1 : 0 },
                        success: function(response) {
                            try {
                                if (typeof response === 'string') {
                                    response = JSON.parse(response);
                                }
                                if (response.success) {
                                    Swal.fire({
                                        title: '¡Éxito!',
                                        text: response.message,
                                        icon: 'success'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', response.error || 'Error al eliminar el rol', 'error');
                                }
                            } catch (e) {
                                console.error('Error al procesar la respuesta:', e);
                                Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error en la petición:', error);
                            Swal.fire('Error', 'Error al eliminar el rol', 'error');
                        }
                    });
                }
            });
        });
    });

    // Evento para el botón de ver detalles
    $(document).on('click', '.ver-detalles', function() {
        const id = $(this).data('id');
        cargarDetallesRol(id);
    });
    // Buscador de permisos en el modal de rol
    $('#buscarPermiso').on('input', function() {
        var filtro = $(this).val().toLowerCase();
        $('.permiso-checkbox').each(function() {
            var label = $(this).closest('.form-check').find('label').text().toLowerCase();
            if (label.indexOf(filtro) > -1) {
                $(this).closest('.form-check').show();
            } else {
                $(this).closest('.form-check').hide();
            }
        });
    });

    // Sincronizar filtros avanzados con el formulario principal de roles
    document.getElementById('aplicarFiltrosRolesPHP').addEventListener('click', function() {
        var estado = document.getElementById('filtroEstadoRol').value;
        let form = document.getElementById('formBuscar');
        let inputEstado = form.querySelector('input[name="estado"]');
        if (!inputEstado) {
            inputEstado = document.createElement('input');
            inputEstado.type = 'hidden';
            inputEstado.name = 'estado';
            form.appendChild(inputEstado);
        }
        inputEstado.value = estado;
        var modal = bootstrap.Modal.getInstance(document.getElementById('modalFiltrosRolesPHP'));
        modal.hide();
        form.dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}));
    });
});
</script> 