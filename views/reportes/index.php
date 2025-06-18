<?php
$subtitulo = isset($subtitulo) ? $subtitulo : 'Genera y descarga reportes detallados del sistema.';
$titulo = "Reportes";
?>
<p class="subtitle text-md">
  <?= htmlspecialchars($subtitulo) ?>
</p>
<div class="container">
    <div class="card shadow-lg border-0 rounded-lg mb-4">
        <div class="card-body">
            <?php include __DIR__ . '/../partials/header_titulo.php'; ?>
        </div>
    </div>
    <!-- Reporte de Mascotas -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="font-weight-light my-4">Reporte de Mascotas</h3>
                    <form id="reporteMascotasForm" class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_inicio_mascotas">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="fecha_inicio_mascotas" name="fecha_inicio">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_fin_mascotas">Fecha Fin</label>
                                    <input type="date" class="form-control" id="fecha_fin_mascotas" name="fecha_fin">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="formato_mascotas">Formato</label>
                            <select class="form-control" id="formato_mascotas" name="formato">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Generar Reporte</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="font-weight-light my-4">Reporte de Dispositivos</h3>
                    <form id="reporteDispositivosForm" class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_inicio_dispositivos">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="fecha_inicio_dispositivos" name="fecha_inicio">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_fin_dispositivos">Fecha Fin</label>
                                    <input type="date" class="form-control" id="fecha_fin_dispositivos" name="fecha_fin">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="formato_dispositivos">Formato</label>
                            <select class="form-control" id="formato_dispositivos" name="formato">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Generar Reporte</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reporte de Mascotas
    document.getElementById('reporteMascotasForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        window.location.href = `<?= BASE_URL ?>reportes/mascotas?${params.toString()}`;
    });

    // Reporte de Dispositivos
    document.getElementById('reporteDispositivosForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        window.location.href = `<?= BASE_URL ?>reportes/dispositivos?${params.toString()}`;
    });

    // Validar fechas
    function validateDates(startDate, endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        return start <= end;
    }

    // Agregar validación a todos los formularios
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const startDate = form.querySelector('input[name="fecha_inicio"]');
        const endDate = form.querySelector('input[name="fecha_fin"]');

        [startDate, endDate].forEach(input => {
            input.addEventListener('change', function() {
                if (!validateDates(startDate.value, endDate.value)) {
                    showToast('La fecha de inicio debe ser anterior a la fecha de fin', 'danger');
                    this.value = '';
                }
            });
        });
    });
});
</script> 