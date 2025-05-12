<?php if (empty($usuarios)): ?>
    <tr>
        <td colspan="6" class="text-center text-muted py-4">No hay usuarios registrados.</td>
    </tr>
<?php else: ?>
    <?php foreach ($usuarios as $usuario): ?>
        <tr>
            <td class="text-center"><?= $usuario['id'] ?></td>
            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
            <td><?= htmlspecialchars($usuario['email']) ?></td>
            <td><?= htmlspecialchars($usuario['rol_nombre']) ?></td>
            <td class="text-center">
                <div class="form-check form-switch">
                    <input class="form-check-input cambiar-estado" type="checkbox"
                        data-id="<?= $usuario['id'] ?>"
                        data-estado="<?= $usuario['estado'] ?>"
                        <?= $usuario['estado'] === 'activo' ? 'checked' : '' ?>
                        <?= !verificarPermiso('cambiar_estado_usuarios') ? 'disabled' : '' ?>>
                    <label class="form-check-label">
                        <?= ucfirst($usuario['estado']) ?>
                    </label>
                </div>
            </td>
            <td class="text-center">
                <?php if (verificarPermiso('editar_usuarios')): ?>
                    <button type="button" class="btn btn-sm btn-info me-1 editar-usuario" data-id="<?= $usuario['id'] ?>" data-bs-toggle="tooltip" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                <?php endif; ?>
                
                <?php if (verificarPermiso('eliminar_usuarios')): ?>
                    <button type="button" class="btn btn-sm btn-danger eliminar-usuario" data-id="<?= $usuario['id'] ?>" data-bs-toggle="tooltip" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?> 