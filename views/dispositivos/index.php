<?php
// Eliminar encabezados y subtítulos, incluyendo el include del header_titulo.php
// $subtitulo = isset($subtitulo) ? $subtitulo : 'Gestiona, busca y administra los dispositivos IoT del sistema.';
$titulo = "Gestión de Dispositivos";
$subtitulo = "Administración de dispositivos IoT para monitoreo de mascotas.";
?>

<!-- FAB: Botón flotante de acción principal para la página de dispositivos -->
<?php if (verificarPermiso('crear_dispositivos')): ?>
<button class="fab-btn" id="btnNuevoDispositivoFlotante" data-bs-toggle="modal" data-bs-target="#modalDispositivo" title="Gestionar Dispositivo">
    <span class="fab-icon"><i class="fas fa-plus"></i></span>
    <span class="fab-text">Gestionar Dispositivo</span>
</button>
<?php endif; ?>

<!-- Page Content -->
<div class="container-fluid px-0">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 w-100 mx-auto">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaDispositivos" class="table table-bordered table-striped" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Mascota</th>
                                    <th>Estado</th>
                                    <th>Batería</th>
                                    <th>Última Actividad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contenedor para la configuración que necesita el JS -->
<div id="dispositivos-config" 
     data-app-url="<?= APP_URL ?>"
     data-permiso-editar="<?= verificarPermiso('editar_dispositivos') ? 'true' : 'false' ?>"
     data-permiso-eliminar="<?= verificarPermiso('eliminar_dispositivos') ? 'true' : 'false' ?>">
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
    function getDynamicPageLength() {
        const tableWrapper = document.querySelector('.table-container');
        if (!tableWrapper) return 10;

        const topOffset = tableWrapper.getBoundingClientRect().top;
        const headerHeight = 56;
        const footerHeight = 50;
        const safetyMargin = 20;

        const availableHeight = window.innerHeight - topOffset - headerHeight - footerHeight - safetyMargin;
        const avgRowHeight = 48;
        const numRows = Math.floor(availableHeight / avgRowHeight);
        
        return Math.max(5, numRows);
    }
    
    let tablaDispositivos;

    function inicializarDataTable() {
        tablaDispositivos = $('#tablaDispositivos').DataTable({
            "processing": true,
            "serverSide": false, // Cambiado a false ya que los datos se cargan en el HTML
            "responsive": true,
            "lengthChange": false,
            "dom": 'fltip',
            "language": { "url": "<?= APP_URL ?>/assets/js/i18n/Spanish.json" },
            initComplete: function() {
                const newPageLength = getDynamicPageLength();
                this.api().page.len(newPageLength).draw();
            }
        });
    }

    inicializarDataTable();

    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (tablaDispositivos) {
                const newPageLength = getDynamicPageLength();
                tablaDispositivos.page.len(newPageLength).draw();
            }
        }, 250);
    });

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