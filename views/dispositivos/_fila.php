<?php
// Plantilla parcial para una fila de la tabla de dispositivos
?>
<tr
  <?php if (($dispositivo['estado'] ?? '') === 'activo'): ?>
    style="background-color: #e6f9ed;"
  <?php elseif (($dispositivo['estado'] ?? '') === 'inactivo'): ?>
    style="background-color: #f5f5f5;"
  <?php endif; ?>
  class="fila-dispositivo"
  data-id="<?= $dispositivo['id'] ?>"
  style="padding: 12px 0;"
>
    <td class="id-azul text-center"><?= $dispositivo['id'] ?></td>
    <td class="nombre-dispositivo" style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" data-bs-toggle="tooltip" title="<?= htmlspecialchars($dispositivo['nombre']) ?>">
        <?= htmlspecialchars($dispositivo['nombre']) ?>
    </td>
    <td><?= htmlspecialchars($dispositivo['mac']) ?></td>
    <td><?= htmlspecialchars($dispositivo['usuario_nombre'] ?? $dispositivo['propietario_nombre'] ?? '-') ?></td>
    <td
        <?php if (empty($dispositivo['mascota_nombre'])): ?>
            style="background-color: #198754; color: #fff; font-weight: bold;"
        <?php else: ?>
            style="background-color: #f8f9fa; color: #212529;"
        <?php endif; ?>
    >
        <?= empty($dispositivo['mascota_nombre']) ? 'Sí' : 'No' ?>
    </td>
    <td class="text-center"><?= htmlspecialchars($dispositivo['estado'] ?? '-') ?></td>
    <td class="text-center"><?= htmlspecialchars($dispositivo['bateria'] ?? '-') ?></td>
    <td><?= htmlspecialchars($dispositivo['mascota_nombre'] ?? '-') ?></td>
    <td><?= htmlspecialchars($dispositivo['ultima_lectura'] ?? '-') ?></td>
    <td class="text-center">
        <div class="btn-group" role="group">
            <a class="btn-accion btn-primary" href="<?= BASE_URL ?>monitor/device/<?= $dispositivo['id'] ?>" data-bs-toggle="tooltip" title="Monitor en vivo">
                <i class="fas fa-chart-line"></i>
            </a>
            <?php if (verificarPermiso('editar_dispositivos')): ?>
            <button class="btn-accion btn-info editar-dispositivo" data-id="<?= $dispositivo['id'] ?>" data-bs-toggle="tooltip" title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <?php endif; ?>
            <?php if (verificarPermiso('eliminar_dispositivos')): ?>
            <button class="btn-accion btn-danger eliminar-dispositivo" data-id="<?= $dispositivo['id'] ?>" data-bs-toggle="tooltip" title="Eliminar">
                <i class="fas fa-trash-alt"></i>
            </button>
            <?php endif; ?>
            <?php if (verificarPermiso('editar_dispositivos')): ?>
            <button class="btn-accion btn-dark asignar-dispositivo" data-id="<?= $dispositivo['id'] ?>" data-bs-toggle="tooltip" title="Asignar/Reasignar">
                <i class="fas fa-user-plus"></i>
            </button>
            <?php endif; ?>
        </div>
    </td>
</tr>
<?php if (isset($soloMobile) && $soloMobile): ?>
<!-- Detalle acordeón solo visible en mobile -->
<tr class="detalle-mobile" style="display:none; background:#f8f9fa;">
  <td colspan="11" style="padding:0.5rem 1rem;">
    <div style="font-size:13px;">
      <div><b>MAC:</b> <?= htmlspecialchars($dispositivo['mac']) ?></div>
      <div><b>Dueño:</b> <?= htmlspecialchars($dispositivo['usuario_nombre'] ?? $dispositivo['propietario_nombre'] ?? '-') ?></div>
      <div><b>Mascota:</b> <?= htmlspecialchars($dispositivo['mascota_nombre'] ?? '-') ?></div>
      <div><b>Última Lectura:</b> <?= htmlspecialchars($dispositivo['ultima_lectura'] ?? '-') ?></div>
      <div class="mt-2">
        <div class="dropdown">
          <button class="btn btn-link text-dark p-0 m-0" type="button" id="dropdownMenuButtonMobile<?= $dispositivo['id'] ?>" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-ellipsis-v fa-lg"></i>
          </button>
          <ul class="dropdown-menu" aria-labelledby="dropdownMenuButtonMobile<?= $dispositivo['id'] ?>">
            <li>
              <a class="dropdown-item ver-detalles" href="#" data-id="<?= $dispositivo['id'] ?>" data-bs-toggle="tooltip" title="Ver historial">
                <i class="fas fa-history me-2"></i>Ver historial
              </a>
            </li>
            <?php if (verificarPermiso('editar_dispositivos')): ?>
            <li>
              <a class="dropdown-item editar-dispositivo" href="#" data-id="<?= $dispositivo['id'] ?>" data-bs-toggle="tooltip" title="Editar">
                <i class="fas fa-edit me-2"></i>Editar
              </a>
            </li>
            <?php endif; ?>
            <?php if (verificarPermiso('eliminar_dispositivos')): ?>
            <li>
              <a class="dropdown-item eliminar-dispositivo text-danger" href="#" data-id="<?= $dispositivo['id'] ?>" data-bs-toggle="tooltip" title="Eliminar">
                <i class="fas fa-trash me-2"></i>Eliminar
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
  </td>
</tr>
<?php endif; ?> 