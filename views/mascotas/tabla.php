<?php
// Permisos del usuario
$puedeEditarCualquiera = in_array('editar_cualquier_mascota', $_SESSION['permissions'] ?? []);
$puedeEditarPropias = in_array('editar_mascotas', $_SESSION['permissions'] ?? []);
$puedeEliminar = in_array('eliminar_mascotas', $_SESSION['permissions'] ?? []);
?>

<?php if (!empty($mascotas)): ?>
    <?php foreach ($mascotas as $mascota): ?>
        <tr>
            <td><?= $mascota['id'] ?></td>
            <td><?= htmlspecialchars($mascota['nombre']) ?></td>
            <td><?= htmlspecialchars($mascota['especie']) ?></td>
            <td><?= htmlspecialchars($mascota['tamano']) ?></td>
            <td><?= htmlspecialchars($mascota['genero'] ?? '-') ?></td>
            <td>
                <?php
                $propietario = '';
                if (!empty($mascota['propietario_id']) && isset($usuarios)) {
                    foreach ($usuarios as $usuario) {
                        if ($usuario['id'] == $mascota['propietario_id']) {
                            $propietario = htmlspecialchars($usuario['nombre']);
                            break;
                        }
                    }
                }
                echo $propietario ?: '-';
                ?>
            </td>
            <td>
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
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input cambiar-estado-mascota" type="checkbox"
                        data-id="<?= $mascota['id'] ?>"
                        <?= $mascota['estado'] === 'activo' ? 'checked' : '' ?>>
                    <label class="form-check-label">
                        <?= ucfirst($mascota['estado']) ?>
                    </label>
                </div>
            </td>
            <td>
                <?php if (
                    $puedeEditarCualquiera ||
                    ($puedeEditarPropias && $mascota['usuario_id'] == $_SESSION['propietario_id'])
                ): ?>
                <button class="btn btn-sm btn-info me-1 btnEditarMascota" data-id="<?= $mascota['id'] ?>">
                    <i class="fas fa-edit"></i>
                </button>
                <?php endif; ?>
                <?php if ($puedeEliminar): ?>
                <button class="btn btn-sm btn-danger btnEliminarMascota" data-id="<?= $mascota['id'] ?>">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="9" class="text-center text-muted py-4">
            No hay mascotas registradas.
        </td>
    </tr>
<?php endif; ?> 