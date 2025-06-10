<?php
$roles = $this->model->getAll();
?>

<div class="table-responsive">
    <table class="table" id="tablaRoles">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tbodyRoles">
            <?php if (empty($roles)): ?>
            <tr>
                <td colspan="5">No hay roles disponibles</td>
            </tr>
            <?php else: ?>
                <?php foreach ($roles as $rol): ?>
                <tr>
                    <td><?= $rol['id_rol'] ?></td>
                    <td><?= htmlspecialchars($rol['nombre']) ?></td>
                    <td>
                        <?php if ($rol['id_rol'] > 3 && verificarPermiso('roles_editar')): ?>
                        <label class="status-switch">
                            <input type="checkbox" <?= $rol['estado'] == 'activo' ? 'checked' : '' ?> onchange="cambiarEstado(<?= $rol['id_rol'] ?>, this.checked)">
                            <span class="switch-slider"></span>
                        </label>
                        <?php else: ?>
                        <span><?= ucfirst($rol['estado']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($rol['descripcion'] ?? 'Sin descripción') ?></td>
                    <td data-label="Acciones">
                        <div class="action-buttons">
                            <button class="btn-action btn-edit" title="Ver permisos" onclick="verPermisos(<?= $rol['id_rol'] ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if (verificarPermiso('roles_editar')): ?>
                            <button class="btn-action btn-edit" data-id="<?= $rol['id_rol'] ?>" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php endif; ?>
                            <?php if (verificarPermiso('roles_eliminar') && $rol['id_rol'] > 3): ?>
                            <button class="btn-action btn-delete" data-id="<?= $rol['id_rol'] ?>" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
// Inicializar tooltips de Bootstrap
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

function cambiarEstado(id, estado) {
    $.ajax({
        url: '<?= APP_URL ?>/roles/cambiarEstado',
        type: 'POST',
        data: {
            id: id,
            estado: estado ? 'activo' : 'inactivo'
        },
        success: function(response) {
            if (response.success) {
                mostrarExito('Estado actualizado correctamente');
            } else {
                mostrarError(response.message || 'Error al cambiar el estado');
                cargarTablaRoles(); // Recargar para restaurar el estado anterior
            }
        },
        error: function() {
            mostrarError('Error al cambiar el estado');
            cargarTablaRoles(); // Recargar para restaurar el estado anterior
        }
    });
}

function verPermisos(id) {
    $.ajax({
        url: '<?= APP_URL ?>/roles/getPermisos',
        type: 'GET',
        data: { id },
        success: function(response) {
            if (response.success) {
                let permisosHtml = '<ul class="list-group">';
                response.permisos.forEach(permiso => {
                    permisosHtml += `<li class="list-group-item">
                        <strong>${permiso.nombre}</strong>
                        <br>
                        <small class="text-muted">${permiso.descripcion}</small>
                    </li>`;
                });
                permisosHtml += '</ul>';

                Swal.fire({
                    title: 'Permisos del Rol',
                    html: permisosHtml,
                    width: '600px',
                    confirmButtonText: 'Cerrar'
                });
            } else {
                mostrarError(response.message || 'Error al obtener los permisos');
            }
        },
        error: function() {
            mostrarError('Error al obtener los permisos');
        }
    });
}
</script> 