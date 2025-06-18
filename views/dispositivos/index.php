<?php
// Eliminar encabezados y subtítulos, incluyendo el include del header_titulo.php
$subtitulo = isset($subtitulo) ? $subtitulo : 'Gestiona, busca y administra los dispositivos IoT del sistema.';
?>
<p class="subtitle text-md" style="margin-top: 0; margin-bottom: 0;">
  <?= htmlspecialchars($subtitulo) ?>
</p>
<!-- FAB: Botón flotante de acción principal para la página de dispositivos -->
<?php if (verificarPermiso('crear_dispositivos')): ?>
<button class="fab-btn" id="btnNuevoDispositivoFlotante" data-bs-toggle="modal" data-bs-target="#modalDispositivo" title="Gestionar Dispositivo">
    <span class="fab-icon"><i class="fas fa-plus"></i></span>
    <span class="fab-text">Gestionar Dispositivo</span>
</button>
<?php endif; ?>

<!-- Page Content -->
<div class="container-fluid px-0">
    <!-- Barra de búsqueda y filtros -->
    <div class="card mb-4 w-100 mx-auto">
        <div class="card-body d-flex flex-column align-items-center">
            <div class="table-container">
                <table class="table" id="tablaDispositivos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>MAC</th>
                            <th>Dueño</th>
                            <th>Disponible</th>
                            <th>Estado</th>
                            <th>Batería</th>
                            <th>Mascota</th>
                            <th>Última Lectura</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyDispositivos">
                        <?php foreach ($dispositivos as $dispositivo): ?>
                        <tr class="fila-dispositivo" data-id="<?= $dispositivo['id'] ?>">
                            <td class="id-azul text-center" data-label="ID"><?= $dispositivo['id'] ?></td>
                            <td class="nombre-dispositivo" data-label="Nombre" title="<?= htmlspecialchars($dispositivo['nombre']) ?>">
                                <?= htmlspecialchars($dispositivo['nombre']) ?>
                            </td>
                            <td data-label="MAC"><?= htmlspecialchars($dispositivo['mac']) ?></td>
                            <td data-label="Dueño"><?= htmlspecialchars($dispositivo['usuario_nombre'] ?? $dispositivo['propietario_nombre'] ?? '-') ?></td>
                            <td class="text-center" data-label="Disponible">
                                <?php if (empty($dispositivo['mascota_nombre'])): ?>
                                    <span class="status-badge badge-success">Disponible</span>
                                <?php else: ?>
                                    <span class="status-badge badge-warning">Asignado</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center" data-label="Estado"><?= htmlspecialchars($dispositivo['estado'] ?? '-') ?></td>
                            <td class="text-center" data-label="Batería">
                                <?php
                                $bateria = isset($dispositivo['bateria']) ? (int)$dispositivo['bateria'] : null;
                                if ($bateria === null || $bateria === '') {
                                    echo '-';
                                } else {
                                    echo '<span class="fw-medium">' . $bateria . '%</span>';
                                }
                                ?>
                            </td>
                            <td data-label="Mascota"><?= htmlspecialchars($dispositivo['mascota_nombre'] ?? '-') ?></td>
                            <td data-label="Última Lectura"><?= htmlspecialchars($dispositivo['ultima_lectura'] ?? '-') ?></td>
                            <td data-label="Acciones">
                                <div class="action-buttons">
                                    <button class="btn-accion btn-info me-1 btnEditarDispositivo" data-id="<?= $dispositivo['id'] ?>" title="Editar" data-bs-toggle="tooltip">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-accion btn-danger me-1 btnEliminarDispositivo" data-id="<?= $dispositivo['id'] ?>" title="Eliminar" data-bs-toggle="tooltip">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <?php if ($dispositivo['estado'] === 'activo' && !empty($dispositivo['mascota_nombre'])): ?>
                                        <button class="btn-accion btn-success btnMonitorDispositivo" data-id="<?= $dispositivo['id'] ?>" title="Monitor" data-bs-toggle="tooltip">
                                            <i class="fas fa-desktop"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-accion btn-secondary" disabled title="Sin dispositivo asociado o inactivo" data-bs-toggle="tooltip">
                                            <i class="fas fa-desktop"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Aquí irá el modal unificado Gestionar Dispositivo -->
<div class="modal fade" id="modalDispositivo" tabindex="-1" data-bs-backdrop="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-microchip me-2"></i>Nuevo Dispositivo IoT</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formNuevoDispositivo">
          <!-- Nombre/tipo de dispositivo oculto o automático -->
          <input type="hidden" name="nombre" value="Dispositivo_<?= uniqid() ?>">

          <!-- Dirección MAC -->
          <div class="input-group mb-3" style="height: 3.5rem;">
            <span class="input-group-text"><i class="fas fa-microchip"></i></span>
            <input type="text" class="form-control" id="mac_nuevo" name="mac" pattern="^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$" required maxlength="17" autocomplete="off" placeholder="00:00:00:00:00:00" />
          </div>
          <small id="macHelp" class="form-text text-muted mb-2">Ejemplo: 00:00:00:00:00:00</small>
          <div id="macError" class="text-danger mb-2" style="display:none;"></div>

          <!-- Usuario asignado -->
          <?php if (verificarPermiso('ver_todos_dispositivo')): ?>
          <div class="input-group mb-3">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <select class="form-select" id="usuario_id_nuevo" name="usuario_id">
              <option value="">Seleccione un usuario...</option>
              <?php foreach ($usuarios as $usuario): ?>
                <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['nombre']) ?> (<?= htmlspecialchars($usuario['email']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>

          <!-- Mascota asociada (opcional) -->
          <div class="input-group mb-3">
            <span class="input-group-text"><i class="fas fa-paw"></i></span>
            <select class="form-select" id="mascota_id_nuevo" name="mascota_id">
              <option value="">Seleccione una mascota (opcional)...</option>
              <!-- Opciones dinámicas vía JS según usuario -->
            </select>
          </div>

          <!-- Estado -->
          <div class="input-group mb-3">
            <span class="input-group-text"><i class="fas fa-toggle-on"></i></span>
            <select class="form-select" id="estado_nuevo" name="estado" required>
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
          </div>

          <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Crear Dispositivo</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- Incluir modales -->
<?php include 'modals.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si jQuery está disponible
    if (typeof jQuery === 'undefined') {
        console.error('jQuery no está disponible');
        return;
    }

    // Función para cargar DataTables y plugins
    function cargarDataTablesYPlugins(callback) {
        var scripts = [
            'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
            'https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js',
            'https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js',
            'https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js'
        ];

        var loadedCount = 0;
        scripts.forEach(function(scriptUrl) {
            if (!document.querySelector('script[src="' + scriptUrl + '"]')) {
                var script = document.createElement('script');
                script.src = scriptUrl;
                script.onload = function() {
                    loadedCount++;
                    if (loadedCount === scripts.length && typeof callback === 'function') {
                        callback();
                    }
                };
                document.head.appendChild(script);
            } else {
                loadedCount++;
                if (loadedCount === scripts.length && typeof callback === 'function') {
                    callback();
                }
            }
        });

        if (scripts.length === 0 && typeof callback === 'function') {
            callback();
        }
    }

    cargarDataTablesYPlugins(function() {
        // La inicialización de DataTables se manejará en assets/js/tables.js
        // Aquí solo se manejarán eventos específicos de la tabla de dispositivos si los hay
        
        // Asignación de DataTables search() al campo de búsqueda personalizado (si existe)
        // Esta parte se eliminará si el buscador se gestiona globalmente
        if ($('#buscarDispositivo').length && $.fn.DataTable.isDataTable('#tablaDispositivos')) {
            var tablaDispositivos = $('#tablaDispositivos').DataTable();
            $('#buscarDispositivo').on('keyup', function() {
                tablaDispositivos.search(this.value).draw();
            });
        }

        // Forzar búsqueda automática al escribir en el input de búsqueda de DataTables (dispositivos)
        $('#tablaDispositivos_filter input').off().on('input', function() {
            var tablaDispositivos = $('#tablaDispositivos').DataTable();
            tablaDispositivos.search(this.value).draw();
        });
    });

    // Resto del código JavaScript específico de esta vista (ej. manejo de modales)
    // ... (Mantener el código existente para botones, modales, etc.)
});
</script>

<!-- Botón de filtros mobile -->
<!-- Offcanvas de filtros -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasFiltros" aria-labelledby="offcanvasFiltrosLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasFiltrosLabel">Filtros</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form id="formBuscarDispositivosMobile">
      <div class="mb-3">
        <label for="buscarDispositivoMobile" class="form-label">Nombre o MAC</label>
        <input type="text" class="form-control" id="buscarDispositivoMobile" name="busqueda" placeholder="Buscar...">
      </div>
      <div class="mb-3">
        <label for="filtroEstadoMobile" class="form-label">Estado</label>
   <select class="form-select" id="filtroEstadoMobile" name="estado">
          <option value="">Todos</option>
          <option value="activo">Activo</option>
          <option value="inactivo">Inactivo</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="filtroBateriaMobile" class="form-label">Batería</label>
        <select class="form-select" id="filtroBateriaMobile" name="bateria">
          <option value="">Todas</option>
          <option value="baja">Baja (&lt;30%)</option>
          <option value="media">Media (30-70%)</option>
          <option value="alta">Alta (&gt;70%)</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Buscar</button>
    </form>
  </div>
</div>

<!-- Modal de Filtros Avanzados (solo para dispositivos: disponible, estado, batería) -->
<div class="modal fade" id="modalFiltrosDispositivosPHP" tabindex="-1" aria-labelledby="modalFiltrosDispositivosPHPLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFiltrosDispositivosPHPLabel">Filtros Avanzados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formFiltrosDispositivosPHP">
                    <div class="mb-3">
                        <label for="filtroDisponibleDispositivo" class="form-label">Disponible</label>
                        <select class="form-select" id="filtroDisponibleDispositivo" name="disponible">
                            <option value="">Todos</option>
                            <option value="si">Sí</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filtroEstadoDispositivo" class="form-label">Estado</label>
                        <select class="form-select" id="filtroEstadoDispositivo" name="estado">
                            <option value="">Todos</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filtroBateriaDispositivo" class="form-label">Rango de Batería</label>
                        <select class="form-select" id="filtroBateriaDispositivo" name="bateria">
                            <option value="">Todas</option>
                            <option value="baja">Baja (&lt;30%)</option>
                            <option value="media">Media (30-70%)</option>
                            <option value="alta">Alta (&gt;70%)</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="aplicarFiltrosDispositivosPHP">Aplicar Filtros</button>
            </div>
        </div>
    </div>
</div> 