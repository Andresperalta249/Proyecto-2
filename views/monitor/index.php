<div class="container">
    <h1 class="mb-4">Monitor en Vivo</h1>

    <div class="row">
        <?php foreach ($dispositivos as $dispositivo): ?>
        <div class="col-md-6 mb-4">
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
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Tipo:</strong> <?= $dispositivo['tipo'] ?><br>
                                <strong>ID:</strong> <?= $dispositivo['identificador'] ?><br>
                                <strong>Mascota:</strong> <?= $dispositivo['mascota_nombre'] ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid">
                                <a href="<?= BASE_URL ?>monitor/device/<?= $dispositivo['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-chart-line"></i> Ver Monitor
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div> 