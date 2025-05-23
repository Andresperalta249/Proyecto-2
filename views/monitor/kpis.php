<div class="row g-2">
    <?php
    // Valores de ejemplo, reemplaza por tus variables reales
    $temp = isset($ultimosDatos[0]['temperatura']) ? $ultimosDatos[0]['temperatura'] : null;
    $bat = isset($ultimosDatos[0]['bateria']) ? $ultimosDatos[0]['bateria'] : null;
    $bpm = isset($ultimosDatos[0]['bpm']) ? $ultimosDatos[0]['bpm'] : null;

    // Lógica de color y estado para cada KPI
    if (!function_exists('kpiColor')) {
        function kpiColor($valor, $tipo) {
            if ($tipo === 'temp') {
                if ($valor < 36 || $valor > 39) return 'bg-danger text-white';
                if ($valor < 37 || $valor > 38) return 'bg-warning';
                return 'bg-success text-white';
            }
            if ($tipo === 'bat') {
                if ($valor < 20) return 'bg-danger text-white';
                if ($valor < 40) return 'bg-warning';
                return 'bg-success text-white';
            }
            if ($tipo === 'bpm') {
                if ($valor < 60 || $valor > 180) return 'bg-danger text-white';
                if ($valor < 80 || $valor > 150) return 'bg-warning';
                return 'bg-success text-white';
            }
            return '';
        }
    }
    ?>
    <div class="col-12 col-md-12 mb-2">
        <div class="card text-center <?= kpiColor($temp, 'temp') ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Rango saludable: 37-38°C">
            <div class="card-body">
                <span class="display-6">🌡️</span>
                <h6 class="card-title">Temperatura</h6>
                <p class="fs-2 mb-0"><?= $temp !== null ? number_format($temp, 1) . '°C' : '-' ?></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-12 mb-2">
        <div class="card text-center <?= kpiColor($bat, 'bat') ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Batería óptima: &gt;40%">
            <div class="card-body">
                <span class="display-6">🔋</span>
                <h6 class="card-title">Batería</h6>
                <p class="fs-2 mb-0"><?= $bat !== null ? number_format($bat, 1) . '%' : '-' ?></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-12 mb-2">
        <div class="card text-center <?= kpiColor($bpm, 'bpm') ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Ritmo normal: 80-150 bpm">
            <div class="card-body">
                <span class="display-6">❤️</span>
                <h6 class="card-title">Ritmo Cardíaco</h6>
                <p class="fs-2 mb-0"><?= $bpm !== null ? intval($bpm) . ' bpm' : '-' ?></p>
            </div>
        </div>
    </div>
</div>
<script>var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl);
});</script> 