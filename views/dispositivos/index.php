<?php
$header_buttons = '<div class="d-flex gap-2"><a href="' . BASE_URL . 'dispositivos/create" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Dispositivo</a></div>';
?>
<?php if (verificarPermiso('crear_dispositivos')): ?>
<button class="fab-crear" id="btnNuevoDispositivoFlotante">
    <i class="fas fa-plus"></i>
    <span class="fab-text">Agregar Dispositivo</span>
</button>
<?php endif; ?>
<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="tabla-app" id="tablaDispositivos">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>NOMBRE</th>
                        <th>TIPO</th>
                        <th>IDENTIFICADOR</th>
                        <th>MASCOTA</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dispositivos as $dispositivo): ?>
                    <tr>
                        <td class="id-azul"><?= $dispositivo['id'] ?></td>
                        <td><?= htmlspecialchars($dispositivo['nombre']) ?></td>
                        <td><?= htmlspecialchars($dispositivo['tipo']) ?></td>
                        <td><?= htmlspecialchars($dispositivo['identificador']) ?></td>
                        <td><?= htmlspecialchars($dispositivo['mascota_nombre']) ?></td>
                        <td>
                            <?php if (verificarPermiso('cambiar_estado_dispositivos')): ?>
                                <div class="form-check form-switch d-flex align-items-center mb-0">
                                    <input class="form-check-input cambiar-estado-dispositivo" type="checkbox" data-id="<?= $dispositivo['id'] ?>" <?= ($dispositivo['estado'] === 'activo') ? 'checked' : '' ?> >
                                    <label class="form-check-label ms-2 <?= $dispositivo['estado'] === 'activo' ? 'estado-activo' : 'estado-inactivo' ?>">
                                        <?= ucfirst($dispositivo['estado']) ?>
                                    </label>
                                </div>
                            <?php else: ?>
                                <span class="badge bg-<?= $dispositivo['estado'] === 'activo' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($dispositivo['estado']) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>monitor/device/<?= $dispositivo['id'] ?>" class="btn-accion btn-primary" title="Monitor"><i class="fas fa-chart-line"></i></a>
                            <?php if (verificarPermiso('editar_dispositivos')): ?>
                            <a href="<?= BASE_URL ?>dispositivos/edit/<?= $dispositivo['id'] ?>" class="btn-accion btn-info" title="Editar"><i class="fas fa-edit"></i></a>
                            <?php endif; ?>
                            <?php if (verificarPermiso('eliminar_dispositivos')): ?>
                            <button class="btn-accion btn-danger eliminar-dispositivo" data-id="<?= $dispositivo['id'] ?>" title="Eliminar"><i class="fas fa-trash"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function cargarDataTablesYPlugins(callback) {
    var scripts = [
        'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js',
        'https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js',
        'https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js',
        'https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js',
        'https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js',
        'https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js'
    ];
    var i = 0;
    function next() {
        if (i < scripts.length) {
            var s = document.createElement('script');
            s.src = scripts[i++];
            s.onload = next;
            document.head.appendChild(s);
        } else {
            callback();
        }
    }
    next();
}
function ejecutarDispositivosJS() {
    $(document).on('click', '#btnNuevoDispositivoFlotante', function() {
        window.location.href = '<?= BASE_URL ?>dispositivos/create';
    });
    // Cambiar estado
    $(document).on('change', '.cambiar-estado-dispositivo', function() {
        const id = $(this).data('id');
        const estado = $(this).is(':checked') ? 'activo' : 'inactivo';
        $.post('<?= BASE_URL ?>dispositivos/cambiarEstado', { id, estado }, function(response) {
            if (response.success) {
                Swal.fire('¡Éxito!', 'Estado actualizado correctamente', 'success');
            } else {
                Swal.fire('Error', response.error || 'No se pudo cambiar el estado', 'error');
            }
        });
    });
    // Eliminar dispositivo
    $(document).on('click', '.eliminar-dispositivo', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= BASE_URL ?>dispositivos/delete/' + id,
                    type: 'POST',
                    success: function(response) {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        if (response.success) {
                            Swal.fire('¡Éxito!', response.message, 'success').then(() => { location.reload(); });
                        } else {
                            Swal.fire('Error', response.error || 'No se pudo eliminar', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo eliminar el dispositivo', 'error');
                    }
                });
            }
        });
    });
    $(document).ready(function() {
        $('#tablaDispositivos').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            responsive: true
        });
    });
}
if (typeof $ === 'undefined') {
    var script = document.createElement('script');
    script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
    script.onload = function() {
        cargarDataTablesYPlugins(ejecutarDispositivosJS);
    };
    document.head.appendChild(script);
} else if (typeof $.fn.DataTable === 'undefined') {
    cargarDataTablesYPlugins(ejecutarDispositivosJS);
} else {
    ejecutarDispositivosJS();
}
</script> 