<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Mis Dispositivos</h1>
        <a href="<?= BASE_URL ?>dispositivos/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Dispositivo
        </a>
    </div>

    <div class="row">
        <?php foreach ($dispositivos as $dispositivo): ?>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <?= $dispositivo['nombre'] ?>
                        <span class="badge bg-<?= $dispositivo['estado'] === 'activo' ? 'success' : 'danger' ?> float-end">
                            <?= ucfirst($dispositivo['estado']) ?>
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        <strong>Tipo:</strong> <?= $dispositivo['tipo'] ?><br>
                        <strong>ID:</strong> <?= $dispositivo['identificador'] ?><br>
                        <strong>Mascota:</strong> <?= $dispositivo['mascota_nombre'] ?><br>
                        <strong>Descripción:</strong> <?= $dispositivo['descripcion'] ?>
                    </p>
                    <div class="btn-group w-100">
                        <a href="<?= BASE_URL ?>monitor/device/<?= $dispositivo['id'] ?>" class="btn btn-info">
                            <i class="fas fa-chart-line"></i> Monitor
                        </a>
                        <a href="<?= BASE_URL ?>dispositivos/edit/<?= $dispositivo['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <button class="btn btn-danger" onclick="deleteDispositivo(<?= $dispositivo['id'] ?>)">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
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
                showToast(data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showToast(data.error, 'danger');
            }
        })
        .catch(error => {
            showToast('Error al eliminar el dispositivo', 'danger');
        });
    }
}
</script> 