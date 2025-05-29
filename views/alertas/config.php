<?php
// Vista de Configuración de Alertas (Administrador)
// Ejemplo visual y funcional
?>
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-cog me-2"></i>Configuración de Alertas</h2>
    <form id="formConfigAlertas">
        <div class="row g-4">
            <?php
            $especies = [
                'gato' => 'Gato',
                'perro_pequeno' => 'Perro Pequeño',
                'perro_mediano' => 'Perro Mediano',
                'perro_grande' => 'Perro Grande',
            ];
            $valores = [
                'gato' => [
                    'ritmo_min' => 120, 'ritmo_max' => 180,
                    'temp_min' => 38, 'temp_max' => 39.2,
                    'bateria_min' => 20, 'frecuencia' => 10
                ],
                'perro_pequeno' => [
                    'ritmo_min' => 100, 'ritmo_max' => 160,
                    'temp_min' => 37.5, 'temp_max' => 39.2,
                    'bateria_min' => 20, 'frecuencia' => 10
                ],
                'perro_mediano' => [
                    'ritmo_min' => 80, 'ritmo_max' => 120,
                    'temp_min' => 37.5, 'temp_max' => 39.2,
                    'bateria_min' => 20, 'frecuencia' => 10
                ],
                'perro_grande' => [
                    'ritmo_min' => 60, 'ritmo_max' => 100,
                    'temp_min' => 37.5, 'temp_max' => 39.2,
                    'bateria_min' => 20, 'frecuencia' => 10
                ],
            ];
            foreach ($especies as $key => $nombre):
            ?>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-light fw-bold">
                        <?= $nombre ?>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label">Ritmo cardiaco (lpm)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="ritmo_min[<?= $key ?>]" value="<?= $valores[$key]['ritmo_min'] ?>" min="0" max="300" required>
                                <span class="input-group-text">-</span>
                                <input type="number" class="form-control" name="ritmo_max[<?= $key ?>]" value="<?= $valores[$key]['ritmo_max'] ?>" min="0" max="300" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Temperatura corporal (°C)</label>
                            <div class="input-group">
                                <input type="number" step="0.1" class="form-control" name="temp_min[<?= $key ?>]" value="<?= $valores[$key]['temp_min'] ?>" min="30" max="45" required>
                                <span class="input-group-text">-</span>
                                <input type="number" step="0.1" class="form-control" name="temp_max[<?= $key ?>]" value="<?= $valores[$key]['temp_max'] ?>" min="30" max="45" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Frecuencia de reporte (min)</label>
                            <input type="number" class="form-control" name="frecuencia[<?= $key ?>]" value="<?= $valores[$key]['frecuencia'] ?>" min="1" max="120" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Nivel mínimo de batería (%)</label>
                            <input type="number" class="form-control" name="bateria_min[<?= $key ?>]" value="<?= $valores[$key]['bateria_min'] ?>" min="1" max="100" required>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="d-flex justify-content-between mt-3">
            <button type="button" class="btn btn-outline-secondary" id="btnRestablecer">Restablecer valores recomendados</button>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
    </form>
</div>
<script>
// Aquí puedes agregar la lógica JS para restablecer valores y guardar
$('#btnRestablecer').on('click', function() {
    // Lógica para restablecer valores predeterminados (puede ser por AJAX o recarga)
    location.reload();
});
$('#formConfigAlertas').on('submit', function(e) {
    e.preventDefault();
    // Lógica para guardar cambios (AJAX recomendado)
    Swal.fire('Guardado', 'Los parámetros de alertas han sido actualizados.', 'success');
});
</script> 