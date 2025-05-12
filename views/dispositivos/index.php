<?php
$header_buttons = '<div class="d-flex gap-2"><a href="' . BASE_URL . 'dispositivos/create" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Dispositivo</a></div>';
?>
<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped" id="tablaDispositivos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Identificador</th>
                        <th>Mascota</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dispositivos as $dispositivo): ?>
                    <tr>
                        <td><?= $dispositivo['id'] ?></td>
                        <td><?= htmlspecialchars($dispositivo['nombre']) ?></td>
                        <td><?= htmlspecialchars($dispositivo['tipo']) ?></td>
                        <td><?= htmlspecialchars($dispositivo['identificador']) ?></td>
                        <td><?= htmlspecialchars($dispositivo['mascota_nombre']) ?></td>
                        <td>
                            <span class="badge bg-<?= $dispositivo['estado'] === 'activo' ? 'success' : 'danger' ?>">
                                <?= ucfirst($dispositivo['estado']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>monitor/device/<?= $dispositivo['id'] ?>" class="btn btn-info btn-sm" title="Monitor"><i class="fas fa-chart-line"></i></a>
                            <a href="<?= BASE_URL ?>dispositivos/edit/<?= $dispositivo['id'] ?>" class="btn btn-primary btn-sm" title="Editar"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-danger btn-sm" onclick="deleteDispositivo(<?= $dispositivo['id'] ?>)" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
function deleteDispositivo(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este dispositivo?')) {
        fetch('<?= BASE_URL ?>dispositivos/delete/' + id, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                alert(data.error);
            }
        })
        .catch(error => {
            alert('Error al eliminar el dispositivo');
        });
    }
}
$(document).ready(function() {
    $('#tablaDispositivos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        responsive: true
    });
});
</script> 