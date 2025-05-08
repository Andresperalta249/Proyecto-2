<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Mis Alertas</h1>
        <button class="btn btn-primary" onclick="marcarTodasLeidas()">
            <i class="fas fa-check-double"></i> Marcar Todas como Leídas
        </button>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Mensaje</th>
                                    <th>Dispositivo</th>
                                    <th>Mascota</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alertas as $alerta): ?>
                                <tr class="<?= $alerta['leida'] ? '' : 'table-warning' ?>">
                                    <td><?= date('d/m/Y H:i:s', strtotime($alerta['fecha'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $alerta['tipo'] === 'error' ? 'danger' : ($alerta['tipo'] === 'advertencia' ? 'warning' : 'info') ?>">
                                            <?= ucfirst($alerta['tipo']) ?>
                                        </span>
                                    </td>
                                    <td><?= $alerta['mensaje'] ?></td>
                                    <td><?= $alerta['dispositivo_nombre'] ?? 'N/A' ?></td>
                                    <td><?= $alerta['mascota_nombre'] ?? 'N/A' ?></td>
                                    <td>
                                        <span class="badge bg-<?= $alerta['leida'] ? 'success' : 'warning' ?>">
                                            <?= $alerta['leida'] ? 'Leída' : 'No Leída' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if (!$alerta['leida']): ?>
                                            <button class="btn btn-sm btn-success" onclick="marcarLeida(<?= $alerta['id'] ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-danger" onclick="eliminarAlerta(<?= $alerta['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
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
</div>

<script>
function marcarLeida(id) {
    fetch('<?= BASE_URL ?>alertas/marcarLeida/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.error, 'danger');
        }
    })
    .catch(error => {
        showToast('Error al marcar la alerta como leída', 'danger');
    });
}

function marcarTodasLeidas() {
    if (confirm('¿Estás seguro de que deseas marcar todas las alertas como leídas?')) {
        fetch('<?= BASE_URL ?>alertas/marcarTodasLeidas', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showToast(data.error, 'danger');
            }
        })
        .catch(error => {
            showToast('Error al marcar las alertas como leídas', 'danger');
        });
    }
}

function eliminarAlerta(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta alerta?')) {
        fetch('<?= BASE_URL ?>alertas/eliminar/' + id, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showToast(data.error, 'danger');
            }
        })
        .catch(error => {
            showToast('Error al eliminar la alerta', 'danger');
        });
    }
}
</script> 