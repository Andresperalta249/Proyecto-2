<?php
// Botones del encabezado
$header_buttons = '
<div class="d-flex gap-2">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRol">
        <i class="fas fa-plus"></i> Nuevo Rol
    </button>
</div>';
?>

<!-- Barra de búsqueda -->
<div class="card mb-4">
    <div class="card-body">
        <form id="formBuscar" class="row g-3">
            <div class="col-md-4">
                <label for="txtBuscarNombreRol" class="form-label">Nombre del Rol</label>
                <input type="text" class="form-control" id="txtBuscarNombreRol" placeholder="Buscar por nombre...">
            </div>
            <div class="col-md-4">
                <label for="cmbEstadoRol" class="form-label">Estado</label>
                <select class="form-select" id="cmbEstadoRol">
                    <option value="">Todos</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Roles -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped" id="tablaRoles">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Permisos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $rol): ?>
                    <tr>
                        <td><?= $rol['id'] ?></td>
                        <td><?= htmlspecialchars($rol['nombre']) ?></td>
                        <td>
                            <span class="badge bg-<?= $rol['estado'] === 'activo' ? 'success' : 'danger' ?>">
                                <?= ucfirst($rol['estado']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($rol['permisos'] ?? 'Sin permisos') ?></td>
                        <td>
                            <?php if ($rol['id'] > 3): // Solo mostrar acciones para roles personalizados ?>
                            <button type="button" class="btn btn-sm btn-primary editar-rol" 
                                    data-id="<?= $rol['id'] ?>" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalRol">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger eliminar-rol" 
                                    data-id="<?= $rol['id'] ?>">
                                <i class="fas fa-trash"></i>
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
                        <label class="form-label">Permisos</label>
                        <div class="border rounded p-3">
                            <?php foreach ($permisos as $permiso): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="permisos[]" 
                                       value="<?= $permiso['id'] ?>" 
                                       id="permiso_<?= $permiso['id'] ?>">
                                <label class="form-check-label" for="permiso_<?= $permiso['id'] ?>">
                                    <?= htmlspecialchars($permiso['nombre']) ?>
                                </label>
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

<!-- JavaScript -->
<script>
$(document).ready(function() {
    // Inicializar DataTable
    const tabla = $('#tablaRoles').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        responsive: true
    });
    
    // Manejar búsqueda
    $('#formBuscar').on('submit', function(e) {
        e.preventDefault();
        const nombre = $('#txtBuscarNombreRol').val();
        const estado = $('#cmbEstadoRol').val();
        
        tabla.search(nombre).draw();
        // Filtrar por estado si es necesario
        if (estado) {
            tabla.column(2).search(estado).draw();
        }
    });
    
    // Manejar edición
    $('.editar-rol').on('click', function() {
        const id = $(this).data('id');
        $.get('<?= APP_URL ?>/roles/get', { id: id }, function(response) {
            if (response.success) {
                const rol = response.data;
                $('#modalRol .modal-title').text('Editar Rol');
                $('#rol_id').val(rol.id);
                $('#nombre').val(rol.nombre);
                
                // Marcar permisos
                $('input[name="permisos[]"]').prop('checked', false);
                if (rol.permiso_ids) {
                    const permisoIds = rol.permiso_ids.split(',');
                    permisoIds.forEach(id => {
                        $(`#permiso_${id}`).prop('checked', true);
                    });
                }
            }
        });
    });
    
    // Manejar eliminación
    $('.eliminar-rol').on('click', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Está seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= APP_URL ?>/roles/delete', { id: id }, function(response) {
                    if (response.success) {
                        Swal.fire('¡Éxito!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.error, 'error');
                    }
                });
            }
        });
    });
    
    // Manejar formulario
    $('#formRol').on('submit', function(e) {
        e.preventDefault();
        const id = $('#rol_id').val();
        const url = id ? '<?= APP_URL ?>/roles/update' : '<?= APP_URL ?>/roles/create';
        
        $.post(url, $(this).serialize(), function(response) {
            if (response.success) {
                Swal.fire('¡Éxito!', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.error, 'error');
            }
        });
    });
    
    // Limpiar formulario al abrir modal
    $('#modalRol').on('show.bs.modal', function(e) {
        if (!$(e.relatedTarget).hasClass('editar-rol')) {
            $('#formRol')[0].reset();
            $('#rol_id').val('');
            $('#modalRol .modal-title').text('Nuevo Rol');
        }
    });
});
</script> 