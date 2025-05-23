<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
:root {
  --azul: #2563eb;
  --azul-claro: #e0e7ff;
  --verde: #22c55e;
  --naranja: #f59e42;
  --rojo: #ef4444;
}
.bg-azul { background: var(--azul) !important; color: #fff !important; }
.text-azul { color: var(--azul) !important; }
.kpi-card {
  border-radius: 1rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  min-height: 120px;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  margin-bottom: 1rem;
  border: 2px solid #e0e7ff;
  transition: border-color 0.2s;
}
.kpi-icon { font-size: 2.2rem; margin-bottom: 0.2rem; }
.kpi-value { font-size: 2rem; font-weight: 700; }
.kpi-label { font-size: 1rem; }
.kpi-normal { border-color: var(--verde) !important; }
.kpi-warning { border-color: var(--naranja) !important; }
.kpi-critical { border-color: var(--rojo) !important; }
.card-info { background: var(--azul-claro); border: none; }
.card-info .card-title { color: var(--azul); }
.table-sticky thead th { position: sticky; top: 0; background: #f8fafc; z-index: 2; }
.last-update { font-size: 0.95rem; color: #888; margin-bottom: 0.5rem; }
.monitor-map {
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  min-height: 450px;
  background: #fff;
}
.info-inline {
  display: flex;
  flex-wrap: wrap;
  gap: 1.2rem;
  margin-bottom: 1.2rem;
}
.info-badge {
  background: #f3f6fa;
  border-radius: 0.7rem;
  padding: 0.7rem 1.2rem;
  font-size: 1.05rem;
  color: #222;
  display: flex;
  align-items: center;
  box-shadow: 0 1px 4px rgba(0,0,0,0.03);
  min-width: 120px;
}
.info-badge i { color: #2563eb; margin-right: 0.5rem; font-size: 1.1rem; }
.info-badge .badge { margin-left: 0.5rem; }
@media (max-width: 767px) {
  .kpi-card { min-height: 90px; }
  .kpi-value { font-size: 1.3rem; }
  .monitor-map { min-height: 300px; margin-bottom: 1rem; }
  .info-inline { flex-direction: column; gap: 0.7rem; }
  .info-badge { width: 100%; justify-content: flex-start; }
}
.map-controls {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.7rem;
  flex-wrap: wrap;
}
.map-controls .btn {
  font-size: 0.95rem;
  padding: 0.3rem 0.9rem;
}
.map-controls-fab {
  position: absolute;
  top: 12px;
  right: 12px;
  z-index: 1000;
  background: rgba(255,255,255,0.95);
  border-radius: 1.2rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.12);
  padding: 10px 12px;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 8px;
}
.btn-fab {
  border: none;
  background: #2563eb;
  color: #fff;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  margin-bottom: 4px;
  transition: background 0.2s;
  box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}
.btn-fab:hover, .btn-fab.active {
  background: #1d4ed8;
}
.btn-group-fab {
  display: flex;
  gap: 6px;
}
.btn-group-fab .btn-fab {
  background: #f3f6fa;
  color: #2563eb;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  font-size: 0.95rem;
  border: 1px solid #e0e7ff;
  margin-bottom: 0;
}
.btn-group-fab .btn-fab.active, .btn-group-fab .btn-fab:hover {
  background: #2563eb;
  color: #fff;
}
.leaflet-control.custom-map-controls {
  background: rgba(255,255,255,0.97);
  border-radius: 1.2rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.12);
  padding: 10px 12px;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 8px;
  margin-bottom: 8px;
}
.leaflet-control .btn-fab {
  border: none;
  background: #2563eb;
  color: #fff;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  margin-bottom: 4px;
  transition: background 0.2s;
  box-shadow: 0 1px 4px rgba(0,0,0,0.08);
  position: relative;
}
.leaflet-control .btn-fab:hover, .leaflet-control .btn-fab.active {
  background: #1d4ed8;
}
.leaflet-control .popover-rangos {
  position: absolute;
  top: 0;
  right: 44px;
  left: auto;
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.18);
  padding: 8px 12px;
  display: flex;
  flex-direction: row-reverse;
  gap: 8px;
  z-index: 9999;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.25s, right 0.25s;
}
.leaflet-control .popover-rangos.show {
  opacity: 1;
  pointer-events: auto;
  right: 44px;
}
.leaflet-control .btn-rango-pop {
  border-radius: 50%;
  width: 32px; height: 32px;
  font-size: 0.95rem;
  background: #f3f6fa;
  color: #2563eb;
  border: 1px solid #e0e7ff;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s, color 0.2s;
}
.leaflet-control .btn-rango-pop:hover {
  background: #2563eb;
  color: #fff;
}
.map-header {
  margin-bottom: 0.5rem;
}
.btn-fab-ubicacion {
  position: fixed;
  bottom: 32px;
  right: 32px;
  z-index: 3000;
  border-radius: 50%;
  width: 60px; height: 60px;
  font-size: 2.2rem;
  background: #2563eb;
  color: #fff;
  box-shadow: 0 4px 16px rgba(37,99,235,0.18);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 3px solid #fff;
  transition: box-shadow 0.2s, background 0.2s;
}
.btn-fab-ubicacion:hover {
  background: #1d4ed8;
  box-shadow: 0 6px 20px rgba(37,99,235,0.28);
}
</style>

<!-- Toast de alerta -->
<div class="toast-container position-fixed top-0 end-0 p-3">
  <div id="alertaToast" class="toast align-items-center text-bg-danger border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<div class="container py-3">
  <!-- Encabezado -->
  <div class="mb-2">
    <div class="text-secondary small mb-1">Monitor de Dispositivo</div>
    <h2 class="fw-bold mb-0">Monitor - Dispositivo de <?= htmlspecialchars($mascota['nombre'] ?? '-') ?></h2>
  </div>
  <!-- Info general en línea -->
  <div class="info-inline">
    <div class="info-badge"><i class="fa-solid fa-tag"></i>Tipo: <?= htmlspecialchars($dispositivo['tipo'] ?? '-') ?></div>
    <div class="info-badge"><i class="fa-solid fa-barcode"></i>Identificación: <?= htmlspecialchars($dispositivo['identificador'] ?? '-') ?></div>
    <div class="info-badge"><i class="fa-solid fa-circle"></i>Estado:
      <?php $estado = $dispositivo['estado'] ?? 'inactivo'; ?>
      <span class="badge <?= $estado === 'activo' ? 'bg-success' : 'bg-danger' ?> ms-1"> <?= ucfirst($estado) ?> </span>
    </div>
    <?php
    // Iconos por especie
    $iconoEspecie = 'fa-paw';
    $iconoMapa = 'https://cdn-icons-png.flaticon.com/512/616/616408.png'; // Perro por defecto
    if (isset($mascota['especie'])) {
      if (stripos($mascota['especie'], 'gato') !== false) {
        $iconoEspecie = 'fa-cat';
        $iconoMapa = 'https://cdn-icons-png.flaticon.com/512/2171/2171991.png'; // Icono de gato
      } else if (stripos($mascota['especie'], 'perro') !== false) {
        $iconoEspecie = 'fa-dog';
        $iconoMapa = 'https://cdn-icons-png.flaticon.com/512/616/616408.png'; // Icono de perro
      }
    }
    ?>
    <div class="info-badge"><i class="fa-solid <?= $iconoEspecie ?>"></i>Mascota: <?= htmlspecialchars($mascota['nombre'] ?? '-') ?> <span class="text-muted ms-1">(<?= htmlspecialchars($mascota['especie'] ?? '-') ?>)</span></div>
  </div>
  <!-- Selector de rango de horas (sin 'Todo el historial') -->
  <div class="mb-2 d-flex flex-wrap gap-2 align-items-center justify-content-start">
    <?php
    $rangos = [
      ['valor' => '2h', 'texto' => '2h'],
      ['valor' => '4h', 'texto' => '4h'],
      ['valor' => '6h', 'texto' => '6h'],
      ['valor' => '12h', 'texto' => '12h'],
      ['valor' => '24h', 'texto' => '24h'],
    ];
    ?>
    <?php foreach ($rangos as $rango): ?>
      <button type="button" class="btn btn-outline-primary btn-sm rango-btn" data-rango="<?= $rango['valor'] ?>"><?= $rango['texto'] ?></button>
    <?php endforeach; ?>
  </div>
  <!-- Encabezado moderno sobre el mapa -->
  <div class="card mb-3 position-relative">
    <div class="card-body p-2">
      <div class="monitor-map" id="mapaDispositivo" style="height: 450px; width: 100%; position:relative;"></div>
    </div>
  </div>

  <!-- Tercera fila: Gráficas -->
  <div class="row g-3 mb-2">
    <div class="col-12 col-md-4">
      <div class="card p-2">
        <div class="fw-semibold mb-1" id="tituloTemp"><i class="fa-solid fa-temperature-half me-1"></i>Temperatura (24h)</div>
        <canvas id="chartTemp"></canvas>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card p-2">
        <div class="fw-semibold mb-1" id="tituloBat"><i class="fa-solid fa-battery-half me-1"></i>Batería (24h)</div>
        <canvas id="chartBat"></canvas>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card p-2">
        <div class="fw-semibold mb-1" id="tituloBpm"><i class="fa-solid fa-heart-pulse me-1"></i>Ritmo cardíaco (24h)</div>
        <canvas id="chartBpm"></canvas>
      </div>
    </div>
  </div>

  <!-- Cuarta fila: Tabla de historial de datos -->
  <div class="row g-3 mb-2">
    <div class="col-12">
      <div class="card p-2">
        <div class="fw-semibold mb-2" id="tituloTabla"><i class="fa-solid fa-table me-1"></i>Historial de datos</div>
        <div class="table-responsive" style="max-height: 350px;">
          <table class="tabla-app table-historial-datos" id="tablaHistorialDatos">
            <thead>
              <tr>
                <th>Fecha/Hora</th>
                <th>Temperatura</th>
                <th>Ritmo cardíaco</th>
                <th>Batería</th>
                <th>Ubicación</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Pie -->
  <div class="row">
    <div class="col-12 text-end">
      <button class="btn btn-outline-success" id="btnExportarCSV"><i class="fa-solid fa-file-csv me-1"></i>Exportar datos</button>
    </div>
  </div>
</div>

<!-- Modales para gráficas y tabla ampliadas -->
<div class="modal fade" id="modalGraficaAmpliada" tabindex="-1" aria-labelledby="modalGraficaAmpliadaLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalGraficaAmpliadaLabel">Gráfica ampliada</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <!-- Controles de rango rápido -->
        <div class="mb-2 d-flex flex-wrap gap-2 align-items-center">
          <?php foreach ($rangos as $rango): ?>
            <button type="button" class="btn btn-outline-primary btn-sm rango-btn" data-rango="<?= $rango['valor'] ?>"><?= $rango['texto'] ?></button>
          <?php endforeach; ?>
        </div>
        <!-- Datepickers (solo para personalizado) -->
        <div class="row mb-2" id="filtrosPersonalizados" style="display:none;">
          <div class="col-12 col-md-6">
            <label for="fechaInicioGrafica" class="form-label">Desde</label>
            <input type="date" class="form-control" id="fechaInicioGrafica">
          </div>
          <div class="col-12 col-md-6">
            <label for="fechaFinGrafica" class="form-label">Hasta</label>
            <input type="date" class="form-control" id="fechaFinGrafica">
          </div>
        </div>
        <!-- KPIs de min, max, promedio -->
        <div class="mb-2" id="statsGraficaTemp" style="display:none;">
          <span id="tempMax" class="me-3">🔥 Máxima: --</span>
          <span id="tempMin" class="me-3">❄️ Mínima: --</span>
          <span id="tempProm">📊 Promedio: --</span>
        </div>
        <div class="position-relative">
          <canvas id="canvasGraficaAmpliada" style="min-height:350px;"></canvas>
        </div>
        <!-- Leyenda de líneas de referencia -->
        <div class="mt-2 text-center small">
          <span class="me-3"><span style="display:inline-block;width:16px;height:4px;background:#22c55e;vertical-align:middle;margin-right:4px;"></span>38°C Ideal</span>
          <span class="me-3"><span style="display:inline-block;width:16px;height:4px;background:#2563eb;vertical-align:middle;margin-right:4px;"></span>39°C Máx. seguro</span>
          <span><span style="display:inline-block;width:16px;height:4px;background:#ef4444;vertical-align:middle;margin-right:4px;"></span>40°C Crítico</span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalHistorialCompleto" tabindex="-1" aria-labelledby="modalHistorialCompletoLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalHistorialCompletoLabel">Historial completo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body p-3">
        <div class="row g-3 mb-4 align-items-end">
          <div class="col-12 col-md-5">
            <label for="fechaInicioTabla" class="form-label small text-muted">Desde</label>
            <input type="date" class="form-control form-control-sm" id="fechaInicioTabla">
          </div>
          <div class="col-12 col-md-5">
            <label for="fechaFinTabla" class="form-label small text-muted">Hasta</label>
            <input type="date" class="form-control form-control-sm" id="fechaFinTabla">
          </div>
          <div class="col-12 col-md-2">
            <button class="btn btn-primary btn-sm w-100" onclick="aplicarFiltroTabla()">
              <i class="fa-solid fa-filter me-2"></i>Filtrar
            </button>
          </div>
        </div>
        <div class="table-responsive" style="max-height: 400px;">
          <table class="tabla-app" id="tablaHistorialCompleto">
            <thead>
              <tr>
                <th>Fecha/Hora</th>
                <th>Temperatura</th>
                <th>Ritmo cardíaco</th>
                <th>Batería</th>
                <th>Ubicación</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Gráficas Chart.js -->
<script>
// Datos de ejemplo, reemplaza por tus datos PHP reales
const ultimosDatos = <?php echo json_encode($ultimosDatos); ?>;
const labels = ultimosDatos.map(d => d.fecha ? new Date(d.fecha).toLocaleTimeString() : '');
const tempData = ultimosDatos.map(d => d.temperatura);
const batData = ultimosDatos.map(d => d.bateria);
const bpmData = ultimosDatos.map(d => d.bpm);

function renderChart(canvasId, label, data, color, labels) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;
  if (ctx._chartInstance) ctx._chartInstance.destroy();
  ctx._chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels || [],
      datasets: [{
        label: label,
        data: data,
        borderColor: color,
        backgroundColor: color + '33',
        tension: 0.2,
        pointRadius: 3,
        pointHoverRadius: 6,
        fill: true
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: { enabled: true }
      },
      scales: {
        y: { beginAtZero: false },
        x: {
          ticks: {
            autoSkip: true,
            maxTicksLimit: 3,
            font: { size: 10 },
            maxRotation: 0,
            callback: function(value, index, values) {
              const label = this.getLabelForValue(value);
              const date = new Date(label);
              if (isNaN(date.getTime())) return '';
              return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
          }
        }
      }
    }
  });
}
document.addEventListener('DOMContentLoaded', function() {
  renderChart('chartTemp', 'Temperatura (°C)', tempData, '#2563eb', labels);
  renderChart('chartBat', 'Batería (%)', batData, '#f59e42', labels);
  renderChart('chartBpm', 'Ritmo cardíaco (bpm)', bpmData, '#ef4444', labels);
});
</script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Tooltips KPIs
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Mapa con marcador y zona segura
  var mapaDiv = document.getElementById('mapaDispositivo');
  if (!mapaDiv) return;
  var ultimosDatos = <?php echo json_encode($ultimosDatos); ?>;
  var lat = <?php echo isset($ultimosDatos[0]['latitude']) ? floatval($ultimosDatos[0]['latitude']) : 'null'; ?>;
  var lng = <?php echo isset($ultimosDatos[0]['longitude']) ? floatval($ultimosDatos[0]['longitude']) : 'null'; ?>;
  var mapa, circle, polyline;
  if (lat !== null && lng !== null && !isNaN(lat) && !isNaN(lng)) {
    // --- Marcador personalizado con ícono/foto de mascota ---
    var iconMascota = L.icon({
      iconUrl: '<?= $iconoMapa ?>',
      iconSize: [48, 48],
      iconAnchor: [24, 24]
    });
    mapa = L.map('mapaDispositivo').setView([lat, lng], 18);
    window.mapa = mapa; // Asegurar que el mapa esté en window
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap'
    }).addTo(mapa);
    // Marcador mascota
    L.marker([lat, lng], {icon: iconMascota}).addTo(mapa);
    // Círculo de margen de error en la última ubicación
    circle = L.circle([lat, lng], {
      color: '#2563eb',
      fillColor: '#2563eb',
      fillOpacity: 0.1,
      radius: 50
    }).addTo(mapa);

    // --- Controles nativos personalizados ---
    var CustomControls = L.Control.extend({
      options: { position: 'topright' },
      onAdd: function(map) {
        var container = L.DomUtil.create('div', 'leaflet-control custom-map-controls');
        // Botón ubicación actual
        var btnUbic = L.DomUtil.create('button', 'btn-fab', container);
        btnUbic.title = 'Centrar en ubicación actual';
        btnUbic.innerHTML = '<i class="fa-solid fa-location-dot"></i>';
        btnUbic.onclick = function(e) { e.preventDefault(); centrarEnUbicacionActual(); };
        // Botón historial
        var btnHist = L.DomUtil.create('button', 'btn-fab', container);
        btnHist.title = 'Ver historial de recorridos';
        btnHist.innerHTML = '<i class="fa-solid fa-clock-rotate-left"></i>';
        // Popover de rangos
        var popover = L.DomUtil.create('div', 'popover-rangos', btnHist);
        [2,4,6,12,24].forEach(function(h) {
          var btn = L.DomUtil.create('button', 'btn-rango-pop', popover);
          btn.innerText = h + 'h';
          btn.title = 'Recorrido ' + h + 'h';
          btn.onclick = function(e) {
            e.stopPropagation();
            mostrarRecorrido(h);
            popover.classList.remove('show');
          };
        });
        btnHist.onclick = function(e) {
          e.stopPropagation();
          // Cerrar otros popovers si hay
          document.querySelectorAll('.popover-rangos.show').forEach(function(pop) {
            if (pop !== popover) pop.classList.remove('show');
          });
          popover.classList.toggle('show');
        };
        // Cerrar popover si se hace clic fuera
        setTimeout(function() {
          document.addEventListener('click', function handler(e) {
            if (!btnHist.contains(e.target) && !popover.contains(e.target)) {
              popover.classList.remove('show');
              document.removeEventListener('click', handler);
            }
          });
        }, 0);
        L.DomEvent.disableClickPropagation(container);
        return container;
      }
    });
    mapa.addControl(new CustomControls());
  } else {
    mapaDiv.innerHTML = '<div class="text-muted p-3">No hay datos de ubicación disponibles para este dispositivo.</div>';
    mapaDiv.style.background = '#f8fafc';
    return;
  }

  // Función para centrar en la ubicación actual
  window.centrarEnUbicacionActual = function() {
    if (lat !== null && lng !== null && !isNaN(lat) && !isNaN(lng)) {
      mapa.setView([lat, lng], 18);
      if (circle) {
        circle.setLatLng([lat, lng]);
      }
    }
  };

  // Toast de alerta si algún KPI es crítico
  (function(){
    let alerta = '';
    if (tempData !== null && (tempData < 36 || tempData > 39)) alerta += '¡Temperatura fuera de rango!\n';
    if (batData !== null && batData < 20) alerta += '¡Batería baja!\n';
    if (bpmData !== null && (bpmData < 60 || bpmData > 180)) alerta += '¡Ritmo cardíaco anormal!';
    if (alerta) {
      const toastEl = document.getElementById('alertaToast');
      toastEl.querySelector('.toast-body').innerText = alerta.trim();
      const toast = new bootstrap.Toast(toastEl);
      toast.show();
    }
  })();

  // Script para el botón de ubicación actual
  const btnUbicacionActual = document.getElementById('btnUbicacionActual');
  if (btnUbicacionActual) {
    btnUbicacionActual.addEventListener('click', function(e) {
      e.preventDefault();
      if (typeof centrarEnUbicacionActual === 'function') centrarEnUbicacionActual();
    });
  }
});
</script>

<?php if (empty($ultimosDatos)): ?>
  <div class="alert alert-warning text-center my-4" role="alert">
    <i class="fa-solid fa-circle-exclamation me-2"></i>
    No hay datos registrados para este dispositivo aún.<br>
    Cuando el dispositivo envíe datos, aquí aparecerán los valores, gráficas y registros.
  </div>
<?php endif; ?>

<script>
// --- Lógica para abrir modales y mostrar gráficas ampliadas ---

// Abrir modal de gráfica ampliada
function abrirGraficaAmpliada(tipo) {
  const modal = new bootstrap.Modal(document.getElementById('modalGraficaAmpliada'));
  document.getElementById('modalGraficaAmpliadaLabel').innerText =
    tipo === 'temp' ? 'Temperatura ampliada' : tipo === 'bat' ? 'Batería ampliada' : 'Ritmo cardíaco ampliado';
  // Sincronizar el rango seleccionado de la pequeña con la grande
  const rangoBtns = document.querySelectorAll('#modalGraficaAmpliada .rango-btn');
  rangoBtns.forEach(btn => btn.classList.remove('active'));
  // Buscar el botón que coincide con el rangoSeleccionado
  const btnMatch = Array.from(rangoBtns).find(btn => btn.getAttribute('data-rango') === rangoSeleccionado);
  if (btnMatch) {
    btnMatch.classList.add('active');
    btnMatch.click(); // Disparar el evento para filtrar y renderizar
  } else {
    renderGraficaAmpliada(tipo); // Por si no hay coincidencia
  }
  modal.show();
}

// Abrir modal de historial completo
function abrirHistorialCompleto() {
  renderTablaHistorial();
  const modal = new bootstrap.Modal(document.getElementById('modalHistorialCompleto'));
  modal.show();
}

// Eventos para tarjetas y botón
// Esperar a que el DOM esté listo
window.addEventListener('DOMContentLoaded', function() {
  // Eliminar evento de click en las tarjetas KPI
  document.querySelectorAll('.kpi-card').forEach(card => {
    card.style.cursor = 'default';
    card.replaceWith(card.cloneNode(true)); // Elimina todos los listeners
  });

  // Agregar evento de click y cursor pointer a los canvas de las gráficas pequeñas
  const canvasConfig = [
    { id: 'chartTemp', tipo: 'temp' },
    { id: 'chartBat', tipo: 'bat' },
    { id: 'chartBpm', tipo: 'bpm' }
  ];
  canvasConfig.forEach(cfg => {
    const canvas = document.getElementById(cfg.id);
    if (canvas) {
      canvas.style.cursor = 'pointer';
      canvas.addEventListener('click', function() {
        abrirGraficaAmpliada(cfg.tipo);
      });
    }
  });

  const btnHistorial = document.getElementById('btnVerHistorialCompleto');
  if (btnHistorial) btnHistorial.addEventListener('click', abrirHistorialCompleto);
});

// --- Lógica de filtrado y renderizado ---
function filtrarDatosPorFecha(datos, inicio, fin) {
  if (!inicio && !fin) return datos;
  return datos.filter(d => {
    const fecha = new Date(d.fecha);
    if (inicio && fecha < new Date(inicio)) return false;
    if (fin && fecha > new Date(fin + 'T23:59:59')) return false;
    return true;
  });
}

// --- Filtro de rango para gráficas pequeñas y tabla de últimos datos ---
const RANGOS_HORAS = {
  '2h': 2,
  '4h': 4,
  '6h': 6,
  '12h': 12,
  '24h': 24
};
let rangoSeleccionado = '2h';

function filtrarPorRango(datos, rango) {
  if (rango === 'historial') {
    // Máximo 1000 registros más recientes
    return datos.slice(0, 1000);
  }
  if (!RANGOS_HORAS[rango]) return datos;
  // Buscar la fecha máxima en los datos
  const maxFecha = datos.reduce((max, d) => {
    const f = new Date(d.fecha);
    return f > max ? f : max;
  }, new Date(0));
  // Filtrar por el rango respecto a la fecha máxima encontrada
  return datos.filter(d => {
    const fecha = new Date(d.fecha);
    return (maxFecha - fecha) <= (RANGOS_HORAS[rango] * 60 * 60 * 1000);
  });
}

function actualizarTitulosPorRango() {
  const rango = rangoSeleccionado;
  document.getElementById('tituloTemp').innerHTML = '<i class="fa-solid fa-temperature-half me-1"></i>Temperatura (' + rango + ')';
  document.getElementById('tituloBat').innerHTML = '<i class="fa-solid fa-battery-half me-1"></i>Batería (' + rango + ')';
  document.getElementById('tituloBpm').innerHTML = '<i class="fa-solid fa-heart-pulse me-1"></i>Ritmo cardíaco (' + rango + ')';
  document.getElementById('tituloTabla').innerHTML = '<i class="fa-solid fa-table me-1"></i>Historial de datos (' + rango + ')';
}

function renderGraficasYTablaPorRango() {
  actualizarTitulosPorRango();
  const datosFiltrados = filtrarPorRango(ultimosDatos, rangoSeleccionado);
  // Gráficas pequeñas
  renderChart('chartTemp', 'Temperatura (°C)', datosFiltrados.map(d => d.temperatura), '#2563eb', datosFiltrados.map(d => d.fecha));
  renderChart('chartBat', 'Batería (%)', datosFiltrados.map(d => d.bateria), '#f59e42', datosFiltrados.map(d => d.fecha));
  renderChart('chartBpm', 'Ritmo cardíaco (bpm)', datosFiltrados.map(d => d.bpm), '#ef4444', datosFiltrados.map(d => d.fecha));
  // Tabla de historial de datos
  renderTablaHistorialDatos(datosFiltrados);
}

function renderTablaHistorialDatos(datos) {
  const tbody = document.querySelector('.table-historial-datos tbody');
  if (!tbody) return;
  tbody.innerHTML = '';
  if (!datos.length) {
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay datos registrados.</td></tr>';
    return;
  }
  datos.forEach(d => {
    const lat = d.latitude ?? d.lat;
    const lng = d.longitude ?? d.lng;
    tbody.innerHTML += `<tr class="fila-historial-datos" style="cursor:pointer;">
      <td>${d.fecha ? new Date(d.fecha).toLocaleString() : '-'}</td>
      <td>${d.temperatura !== undefined ? Number(d.temperatura).toFixed(1) + '°C' : '-'}</td>
      <td>${d.bpm !== undefined ? parseInt(d.bpm) + ' bpm' : '-'}</td>
      <td>${d.bateria !== undefined ? Number(d.bateria).toFixed(1) + '%' : '-'}</td>
      <td>${lat !== undefined && lng !== undefined ? lat + ', ' + lng : '<span class=\"text-muted\">-</span>'}</td>
    </tr>`;
  });
}

// Evento para los botones de rango
window.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.rango-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.rango-btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      rangoSeleccionado = this.getAttribute('data-rango');
      renderGraficasYTablaPorRango();
    });
  });
  // Activar por defecto el de 2h
  document.querySelector('.rango-btn[data-rango="2h"]').classList.add('active');
  renderGraficasYTablaPorRango();
});

// Mostrar KPIs de min, max, promedio solo para temperatura
function mostrarStatsTemp(datos) {
  if (!Array.isArray(datos) || !datos.length) {
    document.getElementById('statsGraficaTemp').style.display = 'none';
    return;
  }
  const max = Math.max(...datos.map(d => d.temperatura));
  const min = Math.min(...datos.map(d => d.temperatura));
  const prom = datos.reduce((a,b) => a + (b.temperatura||0), 0) / datos.length;
  document.getElementById('tempMax').textContent = `🔥 Máxima: ${max.toFixed(1)}°C`;
  document.getElementById('tempMin').textContent = `❄️ Mínima: ${min.toFixed(1)}°C`;
  document.getElementById('tempProm').textContent = `📊 Promedio: ${prom.toFixed(1)}°C`;
  document.getElementById('statsGraficaTemp').style.display = '';
}

// Gráfica ampliada individual
let chartGraficaAmpliada;
function renderGraficaAmpliada(tipo) {
  const canvas = document.getElementById('canvasGraficaAmpliada');
  if (!canvas) return;
  // Destruir la instancia previa y limpiar el canvas
  if (chartGraficaAmpliada) {
    chartGraficaAmpliada.destroy();
    chartGraficaAmpliada = null;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  }
  // Usar el mismo rango que el botón activo en el modal
  let rangoModal = '2h';
  const btnActivo = document.querySelector('#modalGraficaAmpliada .rango-btn.active');
  if (btnActivo) rangoModal = btnActivo.getAttribute('data-rango');
  let datosFiltrados = filtrarPorRango(ultimosDatos, rangoModal);
  let labels = datosFiltrados.map(d => d.fecha ? d.fecha : '');
  let data, color, label;
  if (tipo === 'temp') {
    data = datosFiltrados.map(d => d.temperatura);
    color = '#2563eb';
    label = 'Temperatura (°C)';
  } else if (tipo === 'bat') {
    data = datosFiltrados.map(d => d.bateria);
    color = '#f59e42';
    label = 'Batería (%)';
  } else {
    data = datosFiltrados.map(d => d.bpm);
    color = '#ef4444';
    label = 'Ritmo cardíaco (bpm)';
  }
  chartGraficaAmpliada = new Chart(canvas, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: label,
        data: data,
        borderColor: color,
        backgroundColor: color + '33',
        tension: 0.2,
        pointRadius: 3,
        pointHoverRadius: 6,
        fill: true
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: { enabled: true }
      },
      scales: {
        y: { beginAtZero: false },
        x: {
          ticks: {
            autoSkip: true,
            maxTicksLimit: 8,
            font: { size: 10 },
            maxRotation: 0,
            callback: function(value, index, values) {
              const label = this.getLabelForValue(value);
              const date = new Date(label);
              if (isNaN(date.getTime())) return '';
              return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
          }
        }
      }
    }
  });
}
// Asegurar que al cambiar de rango en el modal, se renderice la gráfica ampliada correctamente
window.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('#modalGraficaAmpliada .rango-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('#modalGraficaAmpliada .rango-btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      const tipo = document.getElementById('modalGraficaAmpliadaLabel').innerText.includes('Temperatura') ? 'temp' : document.getElementById('modalGraficaAmpliadaLabel').innerText.includes('Batería') ? 'bat' : 'bpm';
      renderGraficaAmpliada(tipo);
    });
  });
});

// Gráficas grandes en historial
let chartTempBig, chartBatBig, chartBpmBig;
function renderGraficasBig() {
  const fechaInicio = document.getElementById('fechaInicioGraficas').value;
  const fechaFin = document.getElementById('fechaFinGraficas').value;
  const datosFiltrados = filtrarDatosPorFecha(ultimosDatos, fechaInicio, fechaFin);
  const labels = datosFiltrados.map(d => d.fecha ? new Date(d.fecha).toLocaleString() : '');

  const opcionesComunes = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        mode: 'index',
        intersect: false,
        backgroundColor: 'rgba(255, 255, 255, 0.9)',
        titleColor: '#000',
        bodyColor: '#000',
        borderColor: '#ddd',
        borderWidth: 1
      }
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 }
      },
      y: {
        beginAtZero: false,
        grid: { borderDash: [2, 2] }
      }
    }
  };

  // Temperatura
  if (chartTempBig) chartTempBig.destroy();
  chartTempBig = new Chart(document.getElementById('chartTempBig'), {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Temperatura (°C)',
        data: datosFiltrados.map(d => d.temperatura),
        borderColor: '#2563eb',
        backgroundColor: '#2563eb33',
        tension: 0.4,
        fill: true
      }]
    },
    options: opcionesComunes
  });

  // Batería
  if (chartBatBig) chartBatBig.destroy();
  chartBatBig = new Chart(document.getElementById('chartBatBig'), {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Batería (%)',
        data: datosFiltrados.map(d => d.bateria),
        borderColor: '#f59e42',
        backgroundColor: '#f59e4233',
        tension: 0.4,
        fill: true
      }]
    },
    options: opcionesComunes
  });

  // BPM
  if (chartBpmBig) chartBpmBig.destroy();
  chartBpmBig = new Chart(document.getElementById('chartBpmBig'), {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Ritmo cardíaco (bpm)',
        data: datosFiltrados.map(d => d.bpm),
        borderColor: '#ef4444',
        backgroundColor: '#ef444433',
        tension: 0.4,
        fill: true
      }]
    },
    options: opcionesComunes
  });
}
['fechaInicioGraficas', 'fechaFinGraficas'].forEach(id => {
  document.getElementById(id)?.addEventListener('change', renderGraficasBig);
});

// Tabla historial completa
function renderTablaHistorial() {
  const fechaInicio = document.getElementById('fechaInicioTabla').value;
  const fechaFin = document.getElementById('fechaFinTabla').value;
  // Filtra primero por fecha
  let datosFiltrados = filtrarDatosPorFecha(ultimosDatos, fechaInicio, fechaFin);
  // Luego filtra por rango de horas seleccionado
  datosFiltrados = filtrarPorRango(datosFiltrados, rangoSeleccionado);
  const tbody = document.querySelector('#tablaHistorialCompleto tbody');
  tbody.innerHTML = '';
  if (!datosFiltrados.length) {
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay datos registrados.</td></tr>';
    return;
  }
  datosFiltrados.forEach(d => {
    const lat = d.latitude ?? d.lat;
    const lng = d.longitude ?? d.lng;
    tbody.innerHTML += `<tr class="fila-historial" style="cursor:pointer;">
      <td>${d.fecha ? new Date(d.fecha).toLocaleString() : '-'}</td>
      <td>${d.temperatura !== undefined ? Number(d.temperatura).toFixed(1) + '°C' : '-'}</td>
      <td>${d.bpm !== undefined ? parseInt(d.bpm) + ' bpm' : '-'}</td>
      <td>${d.bateria !== undefined ? Number(d.bateria).toFixed(1) + '%' : '-'}</td>
      <td>${lat !== undefined && lng !== undefined ? lat + ', ' + lng : '<span class=\"text-muted\">-</span>'}</td>
    </tr>`;
  });
}
['fechaInicioTabla', 'fechaFinTabla'].forEach(id => {
  document.getElementById(id)?.addEventListener('change', renderTablaHistorial);
});

// Función para aplicar filtros de historial
function aplicarFiltroHistorial() {
  renderGraficasBig();
}

// Función para aplicar filtros de tabla
function aplicarFiltroTabla() {
  renderTablaHistorial();
}

// Implementación de mostrarRecorrido para Leaflet usando el mismo filtro de rango que la gráfica
function mostrarRecorrido(h) {
  if (!window.mapa) { console.log('Mapa no inicializado'); return; }
  // Eliminar polilínea anterior si existe
  if (window.polyline) {
    window.mapa.removeLayer(window.polyline);
    window.polyline = null;
  }
  // Usar el mismo filtro de rango que la gráfica
  let datos = window.datosSensores || [];
  if (!Array.isArray(datos) || !datos.length) {
    alert('No hay datos para mostrar el recorrido.');
    return;
  }
  // Filtrar por rango de horas (idéntico a filtrarPorRango)
  const maxFecha = datos.reduce((max, d) => {
    const f = new Date(d.fecha);
    return f > max ? f : max;
  }, new Date(0));
  const datosRecorrido = datos.filter(d => {
    const fecha = new Date(d.fecha);
    return (maxFecha - fecha) <= (h * 60 * 60 * 1000);
  });
  console.log('Datos para recorrido:', datosRecorrido);
  // Obtener coordenadas
  const coords = datosRecorrido.map(d => [d.latitude ?? d.lat, d.longitude ?? d.lng]).filter(c => c[0] && c[1]);
  console.log('Coordenadas para polilínea:', coords);
  if (coords.length < 2) {
    alert('No hay suficientes datos de ubicación para mostrar el recorrido.');
    return;
  }
  // Dibujar polilínea
  window.polyline = L.polyline(coords, { color: '#2563eb', weight: 4, opacity: 0.7 }).addTo(window.mapa);
  // Ajustar vista
  window.mapa.fitBounds(window.polyline.getBounds(), { padding: [30, 30] });
}

// Al cargar la página, guardar los datos en window.datosSensores para acceso global
window.addEventListener('DOMContentLoaded', function() {
  window.datosSensores = ultimosDatos;
});

// Evento de clic para la tabla de historial de datos
window.addEventListener('DOMContentLoaded', function() {
  const tbody = document.querySelector('.table-historial-datos tbody');
  if (tbody) {
    tbody.addEventListener('click', function(e) {
      const fila = e.target.closest('.fila-historial-datos');
      if (!fila) return;
      const celdas = fila.querySelectorAll('td');
      if (celdas.length < 5) return;
      const fecha = celdas[0].innerText;
      // Buscar el dato correspondiente en ultimosDatos
      const dato = ultimosDatos.find(d => {
        const f = d.fecha ? new Date(d.fecha).toLocaleString() : '-';
        return f === fecha;
      });
      if (!dato) return;
      // Centrar el mapa si hay lat/lng
      const lat = dato.latitude ?? dato.lat;
      const lng = dato.longitude ?? dato.lng;
      if (lat && lng && window.mapa) {
        window.mapa.setView([lat, lng], 18);
        if (window.marcadorSeleccionado) window.mapa.removeLayer(window.marcadorSeleccionado);
        window.marcadorSeleccionado = L.marker([lat, lng], {
          icon: L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png',
            iconSize: [40, 40],
            iconAnchor: [20, 40]
          })
        }).addTo(window.mapa);
      }
      // Resaltar el punto en las tres gráficas
      ['chartTemp', 'chartBat', 'chartBpm'].forEach(chartId => {
        const chartElem = document.getElementById(chartId);
        if (window.Chart && chartElem && chartElem._chartInstance) {
          const chart = chartElem._chartInstance;
          const idx = chart.data.labels.findIndex(l => {
            const lDate = l ? new Date(l).toLocaleString() : '-';
            return lDate === fecha;
          });
          if (idx !== -1) {
            chart.setActiveElements([{datasetIndex: 0, index: idx}]);
            chart.tooltip.setActiveElements([{datasetIndex: 0, index: idx}], {x: 0, y: 0});
            chart.update();
          }
        }
      });
    });
  }
});
</script>

<!-- Chart.js annotation plugin -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@4.0.0"></script>

<script>
// Registrar el plugin de anotaciones globalmente para Chart.js
if (window.Chart && window['ChartAnnotationPlugin']) {
  Chart.register(window['ChartAnnotationPlugin']);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Funcionalidad para exportar la tabla de últimos datos registrados a CSV -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('btnExportarCSV')?.addEventListener('click', function() {
    // Exportar SIEMPRE todo el historial, no solo el filtro actual
    const datos = ultimosDatos;
    if (!datos.length) {
      alert('No hay datos para exportar.');
      return;
    }
    // Descripción y encabezados profesionales
    const descripcion = 'Exportación de datos de monitoreo de mascota. Fecha de exportación: ' + new Date().toLocaleString();
    const headers = [
      'Fecha/Hora de Registro',
      'Temperatura (°C)',
      'Ritmo Cardíaco (bpm)',
      'Nivel de Batería (%)',
      'Latitud',
      'Longitud'
    ];
    // Filas organizadas
    const rows = datos.map(d => [
      d.fecha ? new Date(d.fecha).toLocaleString() : '-',
      d.temperatura !== undefined ? Number(d.temperatura).toFixed(1) : '-',
      d.bpm !== undefined ? parseInt(d.bpm) : '-',
      d.bateria !== undefined ? Number(d.bateria).toFixed(1) : '-',
      (d.latitude ?? d.lat) !== undefined ? (d.latitude ?? d.lat) : '-',
      (d.longitude ?? d.lng) !== undefined ? (d.longitude ?? d.lng) : '-'
    ]);
    // Construir CSV profesional
    let csv = '"' + descripcion + '"\n';
    csv += headers.join(',') + '\n';
    rows.forEach(r => {
      csv += r.map(val => '"' + String(val).replace(/"/g, '""') + '"').join(',') + '\n';
    });
    // Descargar
    const blob = new Blob([csv], {type: 'text/csv'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'monitoreo_salud_mascota.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  });
});
</script>
</body>
</html> 