<?php
// Permisos del usuario
$puedeEditarCualquiera = in_array('editar_cualquier_mascota', $_SESSION['permissions'] ?? []);
$puedeEditarPropias = in_array('editar_mascotas', $_SESSION['permissions'] ?? []);
$puedeEliminar = in_array('eliminar_mascotas', $_SESSION['permissions'] ?? []);
?>

<?php if (!empty($mascotas)): ?>
    <?php foreach ($mascotas as $mascota): ?>
        <tr>
            <td style="width: 48px;"><?= $mascota['id'] ?></td>
            <td style="max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($mascota['nombre']) ?>"><?= htmlspecialchars($mascota['nombre']) ?></td>
            <td style="width: 80px;"><?= htmlspecialchars($mascota['especie']) ?></td>
            <td style="width: 80px;"><?= htmlspecialchars($mascota['tamano']) ?></td>
            <td style="width: 60px;"><?= htmlspecialchars($mascota['genero'] ?? '-') ?></td>
            <td style="max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= $propietario ?>"><?= $propietario ?: '-' ?></td>
            <td style="width: 60px; text-align:center;">
                <?php
                if (!empty($mascota['fecha_nacimiento'])) {
                    $nacimiento = new DateTime($mascota['fecha_nacimiento']);
                    $hoy = new DateTime();
                    $edad = $hoy->diff($nacimiento)->y;
                    echo $edad . ' año' . ($edad != 1 ? 's' : '');
                } else {
                    echo '-';
                }
                ?>
            </td>
            <td style="width: 80px; text-align:center;">
                <?php
                $bateria = isset($mascota['bateria']) ? (int)$mascota['bateria'] : null;
                if ($bateria === null || $bateria === '') {
                    echo '-';
                } else {
                    echo '<span style="color:#222;font-weight:500;">' . $bateria . '%</span>';
                }
                ?>
            </td>
            <td style="width: 110px;">
                <div class="form-check form-switch d-flex align-items-center mb-0">
                    <input class="form-check-input cambiar-estado-mascota <?= $mascota['estado'] === 'inactivo' ? 'switch-inactivo' : '' ?>"
                        type="checkbox"
                        data-id="<?= $mascota['id'] ?>"
                        <?= $mascota['estado'] === 'activo' ? 'checked' : '' ?> >
                    <label class="form-check-label ms-2">
                        <?= ucfirst($mascota['estado']) ?>
                    </label>
                </div>
            </td>
            <td style="width: 90px;">
                <?php if (
                    $puedeEditarCualquiera ||
                    ($puedeEditarPropias && $mascota['usuario_id'] == $_SESSION['propietario_id'])
                ): ?>
                <button class="btn-accion btn-info me-1 btnEditarMascota" data-id="<?= $mascota['id'] ?>" title="Editar" data-bs-toggle="tooltip">
                    <i class="fas fa-edit"></i>
                </button>
                <?php endif; ?>
                <?php if ($puedeEliminar): ?>
                <button class="btn-accion btn-danger btnEliminarMascota" data-id="<?= $mascota['id'] ?>" title="Eliminar" data-bs-toggle="tooltip">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="10" class="text-center text-muted py-4">
            No hay mascotas registradas.
        </td>
    </tr>
<?php endif; ?> 