<?php if (empty($usuarios)): ?>
    <tr>
        <td colspan="9" class="text-center sin-resultados">No hay usuarios registrados.</td>
    </tr>
<?php else: ?>
    <?php foreach ($usuarios as $usuario): ?>
        <tr>
            <td class="text-center id-azul"><?= $usuario['id'] ?></td>
            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
            <td><?= htmlspecialchars($usuario['email']) ?></td>
            <td><?= htmlspecialchars($usuario['rol_nombre']) ?></td>
            <td><?= htmlspecialchars($usuario['telefono'] ?? '-') ?></td>
            <td><?= htmlspecialchars($usuario['direccion'] ?? '-') ?></td>
            <td class="text-center">
                <div class="form-check form-switch d-flex align-items-center mb-0">
                    <input class="form-check-input cambiar-estado-usuario" type="checkbox"
                        data-id="<?= $usuario['id'] ?>"
                        data-estado="<?= $usuario['estado'] ?>"
                        <?= $usuario['estado'] === 'activo' ? 'checked' : '' ?>
                        <?= !verificarPermiso('cambiar_estado_usuarios') ? 'disabled' : '' ?> >
                    <label class="form-check-label ms-2">
                        <?= ucfirst($usuario['estado']) ?>
                    </label>
                </div>
            </td>
            <td class="text-center">
                <?= $usuario['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : '<span class="text-muted">-</span>' ?>
            </td>
            <td class="text-center">
                <?php if (verificarPermiso('editar_usuarios')): ?>
                    <button class="btn-accion btn-info editar-usuario" data-id="<?= $usuario['id'] ?>" title="Editar usuario">
                        <i class="fas fa-edit"></i>
                    </button>
                <?php endif; ?>
                <?php if (verificarPermiso('eliminar_usuarios')): ?>
                    <?php
                    $rol = strtolower(trim($usuario['rol_nombre'] ?? ''));
                    if (!in_array($rol, ['administrador', 'superadministrador'])):
                    ?>
                        <button class="btn-accion btn-danger eliminar-usuario" data-id="<?= $usuario['id'] ?>" title="Eliminar usuario">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>

<style>
.btn-accion {
    font-size: 1.1rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(37,99,235,0.08);
    margin: 0 2px;
}
.table.bg-white {
    background: #fff;
}
</style> 