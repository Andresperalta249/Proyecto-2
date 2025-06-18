<?php
// Vista: Reporte moderno de monitoreo IoT de mascotas
?>
<div class="container-fluid py-4" style="background:#f8f9fc;min-height:100vh;">
  <div class="card shadow-sm mb-4 rounded-4">
    <div class="card-body">
      <form id="buscador-avanzado" class="row g-3 align-items-end">
        <div class="col-md-4">
          <label for="propietario" class="form-label">Dueño</label>
          <select id="propietario" class="form-select" data-placeholder="Buscar dueño..."></select>
        </div>
        <div class="col-md-4">
          <label for="mascota" class="form-label">Mascota</label>
          <select id="mascota" class="form-select" data-placeholder="Selecciona mascota..." disabled></select>
        </div>
        <div class="col-md-3">
          <label for="mac" class="form-label">MAC del dispositivo</label>
          <input id="mac" class="form-control" placeholder="Buscar MAC...">
        </div>
        <div class="col-md-1 d-grid">
          <button type="button" id="mostrar-todo" class="btn btn-outline-secondary">Mostrar todas</button>
        </div>
      </form>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-6 col-12">
      <div class="card shadow-sm rounded-4 h-100">
        <div class="card-body p-2">
          <div id="map" class="w-100" style="height:350px; min-height:250px; border-radius:12px;"></div>
        </div>
      </div>
    </div>
    <div class="col-lg-6 col-12">
      <div class="card shadow-sm rounded-4 h-100 d-flex flex-column">
        <div class="card-body pb-0">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold fs-5">Histórico de sensores</span>
            <button id="exportar-excel" class="btn btn-success btn-sm"><i class="fa fa-file-excel"></i> Exportar a Excel</button>
          </div>
          <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
            <table id="tabla-registros" class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Fecha y hora</th>
                  <th>Temperatura</th>
                  <th>Ritmo cardíaco</th>
                  <th>Ubicación</th>
                  <th>Batería</th>
                </tr>
              </thead>
              <tbody>
                <!-- Registros AJAX -->
              </tbody>
            </table>
          </div>
          <div id="paginador" class="mt-2"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- JS para interacción (a implementar en assets/js/reportes.js) -->
<script src="<?= APP_URL ?>/assets/js/config.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="<?= APP_URL ?>/assets/js/reportes.js"></script>
<script>
// Aquí irá la lógica JS para cargar propietarios, mascotas, MACs, mapa y tabla
// Se recomienda usar AJAX y reutilizar DataTables/Leaflet si ya están en el sistema
</script> 