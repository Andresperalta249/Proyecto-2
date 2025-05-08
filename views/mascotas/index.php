<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Mis Mascotas</h1>
        <a href="<?= BASE_URL ?>mascotas/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Mascota
        </a>
    </div>

    <div class="row">
        <?php foreach ($mascotas as $mascota): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <?php if ($mascota['imagen']): ?>
                <img src="<?= BASE_URL ?>uploads/mascotas/<?= $mascota['imagen'] ?>" 
                     class="card-img-top" 
                     alt="<?= $mascota['nombre'] ?>"
                     style="height: 200px; object-fit: cover;">
                <?php else: ?>
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                     style="height: 200px;">
                    <i class="fas fa-paw fa-4x text-muted"></i>
                </div>
                <?php endif; ?>
                
                <div class="card-body">
                    <h5 class="card-title"><?= $mascota['nombre'] ?></h5>
                    <p class="card-text">
                        <strong>Especie:</strong> <?= $mascota['especie'] ?><br>
                        <strong>Raza:</strong> <?= $mascota['raza'] ?><br>
                        <strong>Edad:</strong> <?= calcularEdad($mascota['fecha_nacimiento']) ?> años
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="btn-group w-100">
                        <a href="<?= BASE_URL ?>mascotas/view/<?= $mascota['id'] ?>" class="btn btn-info">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="<?= BASE_URL ?>mascotas/edit/<?= $mascota['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <button class="btn btn-danger" onclick="deleteMascota(<?= $mascota['id'] ?>)">
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
function deleteMascota(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta mascota? Esta acción no se puede deshacer.')) {
        fetch('<?= BASE_URL ?>mascotas/delete/' + id, {
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
            showToast('Error al eliminar la mascota', 'danger');
        });
    }
}
</script>

<?php
function calcularEdad($fechaNacimiento) {
    $nacimiento = new DateTime($fechaNacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($nacimiento);
    return $edad->y;
}
?> 