<?php if (!empty($mascotas)): ?>
    <?php foreach ($mascotas as $mascota): ?>
        <tr>
            <td><?= htmlspecialchars($mascota['id']) ?></td>
            <td><?= htmlspecialchars($mascota['nombre']) ?></td>
            <td><?= htmlspecialchars($mascota['especie']) ?></td>
            <td><?= htmlspecialchars($mascota['tamano'] ?? '-') ?></td>
            <?php if (in_array('gestionar_mascotas', $_SESSION['permisos'] ?? [])): ?>
                <td><?= htmlspecialchars($mascota['propietario_nombre'] ?? '-') ?></td>
            <?php endif; ?>
            <td>
                <?php
                    $estado = strtolower(trim($mascota['estado']));
                    $isActivo = $estado === 'activo';
                    $switchColor = $isActivo ? '#198754' : '#ffc107';
                    $labelColor = $switchColor;
                ?>
                <div class="form-check form-switch" style="display: flex; align-items: center; gap: 10px;">
                    <input class="form-check-input cambiar-estado-mascota"
                           type="checkbox"
                           role="switch"
                           data-id="<?= $mascota['id'] ?>"
                           id="switchEstado<?= $mascota['id'] ?>"
                           <?= $isActivo ? 'checked' : '' ?>
                           style="width: 2.5em; height: 1.3em; background-color: <?= $switchColor ?>; border-color: <?= $switchColor ?>;">
                    <label class="form-check-label fw-bold ms-2" for="switchEstado<?= $mascota['id'] ?>" style="color: <?= $labelColor ?>; min-width: 60px;">
                        <?= $isActivo ? 'Activo' : 'Inactivo' ?>
                    </label>
                </div>
            </td>
            <td>
                <button class="btn btn-sm btn-info me-1 btnEditarMascota" data-id="<?= $mascota['id'] ?>">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger btnEliminarMascota" data-id="<?= $mascota['id'] ?>">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="<?= in_array('gestionar_mascotas', $_SESSION['permisos'] ?? []) ? 7 : 6 ?>" class="text-center text-muted py-4">
            No tienes mascotas registradas.
        </td>
    </tr>
<?php endif; ?> 