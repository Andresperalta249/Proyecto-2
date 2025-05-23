<?php
// Plantilla parcial para una fila de la tabla de dispositivos
?>
<tr class="fila-dispositivo" data-id="<?= $dispositivo['id'] ?>">
    <td class="id-azul text-center"><?= $dispositivo['id'] ?></td>
    <td class="nombre-dispositivo" style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" data-bs-toggle="tooltip" title="<?= htmlspecialchars($dispositivo['nombre']) ?>">
        <?= htmlspecialchars($dispositivo['nombre']) ?>
    </td>
    <td><?= htmlspecialchars($dispositivo['mac']) ?></td>
    <td><?= htmlspecialchars($dispositivo['usuario_nombre'] ?? $dispositivo['propietario_nombre'] ?? '-') ?></td>
    <td class="text-center">
        <?php if (empty($dispositivo['mascota_nombre'])): ?>
            <span style="color: #198754; font-weight: 600;">Disponible</span>
        <?php else: ?>
            <span style="color: #222; font-weight: 600;">Asignado</span>
        <?php endif; ?>
    </td>
    <td class="text-center"><?= htmlspecialchars($dispositivo['estado'] ?? '-') ?></td>
    <td class="text-center">
        <?php
        $bateria = isset($dispositivo['bateria']) ? (int)$dispositivo['bateria'] : null;
        if ($bateria === null || $bateria === '') {
            echo '-';
        } else {
            echo '<span style="color:#222;font-weight:500;">' . $bateria . '%</span>';
        }
        ?>
    </td>
    <td><?= htmlspecialchars($dispositivo['mascota_nombre'] ?? '-') ?></td>
    <td><?= htmlspecialchars($dispositivo['ultima_lectura'] ?? '-') ?></td>
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