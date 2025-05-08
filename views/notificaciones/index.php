<?php require_once 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Mis Notificaciones</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" onclick="marcarTodasComoLeidas()">
                            <i class="fas fa-check-double"></i> Marcar todas como leídas
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarTodas()">
                            <i class="fas fa-trash"></i> Eliminar todas
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($notificaciones)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No tienes notificaciones</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($notificaciones as $notificacion): ?>
                                <div class="list-group-item list-group-item-action <?= $notificacion['leida'] ? '' : 'list-group-item-primary' ?>">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <?php if ($notificacion['tipo'] === 'alerta'): ?>
                                                    <i class="fas fa-exclamation-triangle text-danger"></i>
                                                <?php elseif ($notificacion['tipo'] === 'info'): ?>
                                                    <i class="fas fa-info-circle text-info"></i>
                                                <?php elseif ($notificacion['tipo'] === 'exito'): ?>
                                                    <i class="fas fa-check-circle text-success"></i>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($notificacion['titulo']) ?>
                                            </h6>
                                            <p class="mb-1"><?= htmlspecialchars($notificacion['mensaje']) ?></p>
                                            <small class="text-muted">
                                                <?= date('d/m/Y H:i', strtotime($notificacion['fecha_creacion'])) ?>
                                            </small>
                                        </div>
                                        <div class="btn-group">
                                            <?php if (!$notificacion['leida']): ?>
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="marcarComoLeida(<?= $notificacion['id'] ?>)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($notificacion['enlace']): ?>
                                                <a href="<?= htmlspecialchars($notificacion['enlace']) ?>" 
                                                   class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="eliminarNotificacion(<?= $notificacion['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function marcarComoLeida(id) {
    fetch('<?= BASE_URL ?>notificacion/marcarLeida/' + id, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            mostrarToast('error', 'Error al marcar como leída');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('error', 'Error al marcar como leída');
    });
}

function marcarTodasComoLeidas() {
    if (!confirm('¿Estás seguro de marcar todas las notificaciones como leídas?')) {
        return;
    }

    fetch('<?= BASE_URL ?>notificacion/marcarTodasLeidas', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            mostrarToast('error', 'Error al marcar como leídas');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('error', 'Error al marcar como leídas');
    });
}

function eliminarNotificacion(id) {
    if (!confirm('¿Estás seguro de eliminar esta notificación?')) {
        return;
    }

    fetch('<?= BASE_URL ?>notificacion/eliminar/' + id, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            mostrarToast('error', 'Error al eliminar la notificación');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('error', 'Error al eliminar la notificación');
    });
}

function eliminarTodas() {
    if (!confirm('¿Estás seguro de eliminar todas las notificaciones?')) {
        return;
    }

    fetch('<?= BASE_URL ?>notificacion/eliminarTodas', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            mostrarToast('error', 'Error al eliminar las notificaciones');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('error', 'Error al eliminar las notificaciones');
    });
}
</script>

<?php require_once 'views/layouts/footer.php'; ?> 