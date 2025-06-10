<?php $subtitulo = isset($subtitulo) ? $subtitulo : 'Gestiona, visualiza y configura las alertas del sistema.'; ?>
<p class="subtitle text-md" style="margin-top: 0; margin-bottom: 0;">
  <?= htmlspecialchars($subtitulo) ?>
</p>
<div class="container-fluid">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Alertas</h1>
        <?php if (verificarPermiso('gestion_alertas_globales')): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalConfiguracionAlertas">
                <i class="fas fa-cog"></i> Configurar Alertas
            </button>
        <?php endif; ?>
    </div>

    <!-- Modal de Configuración de Alertas -->
    <div class="modal fade" id="modalConfiguracionAlertas" tabindex="-1" aria-labelledby="modalConfiguracionAlertasLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="max-height:80vh;overflow-y:auto;">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-semibold" id="modalConfiguracionAlertasLabel">
                        <i class="fas fa-bell me-2"></i>Configuración de Alertas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body pt-0">
                    <!-- DEPURACIÓN: Mostrar el array $config_alertas -->
                    <pre style="background:#222;color:#fff;padding:1em;max-height:300px;overflow:auto;z-index:9999;position:relative;">
<?php print_r($config_alertas); ?>
                    </pre>
                    <!-- Tabs superiores para seleccionar tipo de mascota -->
                    <ul class="nav nav-tabs mb-3" id="tabsMascota" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-gato" data-bs-toggle="tab" data-bs-target="#gato" type="button" role="tab" aria-controls="gato" aria-selected="true">
                                <i class="fas fa-cat me-1"></i> Gato
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-perro" data-bs-toggle="tab" data-bs-target="#perro" type="button" role="tab" aria-controls="perro" aria-selected="false">
                                <i class="fas fa-dog me-1"></i> Perro
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="tabsMascotaContent">
                        <!-- Tab Gato -->
                        <div class="tab-pane fade show active" id="gato" role="tabpanel" aria-labelledby="tab-gato">
                            <!-- Tabla de configuración para Gato -->
                            <div class="table-responsive">
                                <table class="table align-middle text-center mb-0">
                                    <thead style="background:var(--color-bg-header,#f5f6fa);font-family:'Poppins',sans-serif;font-weight:500;">
                                        <tr>
                                            <th></th>
                                            <th colspan="2">
                                                <span data-bs-toggle="tooltip" title="Temperatura corporal">
                                                    <i class="fas fa-thermometer-half text-danger"></i> Temperatura (°C)
                                                </span>
                                            </th>
                                            <th colspan="2">
                                                <span data-bs-toggle="tooltip" title="Ritmo cardíaco">
                                                    <i class="fas fa-heartbeat text-primary"></i> Ritmo Cardíaco (BPM)
                                                </span>
                                            </th>
                                            <th colspan="2">
                                                <span data-bs-toggle="tooltip" title="Nivel de batería">
                                                    <i class="fas fa-battery-half text-warning"></i> Batería (%)
                                                </span>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th style="width:7rem">Valor</th>
                                            <th style="width:16rem">Mensaje de Alerta</th>
                                            <th style="width:7rem">Valor</th>
                                            <th style="width:16rem">Mensaje de Alerta</th>
                                            <th style="width:7rem">Valor</th>
                                            <th style="width:16rem">Mensaje de Alerta</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Fila: Valor Mínimo Normal -->
                                        <tr>
                                            <th class="text-start" data-bs-toggle="tooltip" title="El valor mínimo considerado normal para el sensor.">Valor Mínimo Normal</th>
                                            <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="gato[temperatura][valor_minimo]" value="<?= htmlspecialchars($config_alertas['temperatura']['valor_minimo'] ?? '') ?>" required></td>
                                            <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="gato[temperatura][mensaje_min_normal]" value="<?= htmlspecialchars($config_alertas['temperatura']['mensaje_min_normal'] ?? '') ?>" required></td>
                                            <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="gato[ritmo_cardiaco][valor_minimo]" value="<?= htmlspecialchars($config_alertas['ritmo_cardiaco']['valor_minimo'] ?? '') ?>" required></td>
                                            <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="gato[ritmo_cardiaco][mensaje_min_normal]" value="<?= htmlspecialchars($config_alertas['ritmo_cardiaco']['mensaje_min_normal'] ?? '') ?>" required></td>
                                            <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="gato[bateria][valor_minimo]" value="<?= htmlspecialchars($config_alertas['bateria']['valor_minimo'] ?? '') ?>" required></td>
                                            <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="gato[bateria][mensaje_min_normal]" value="<?= htmlspecialchars($config_alertas['bateria']['mensaje_min_normal'] ?? '') ?>" required></td>
                                        </tr>
                                        <!-- Fila: Valor Máximo Normal -->
                                        <tr>
                                            <th class="text-start" data-bs-toggle="tooltip" title="El valor máximo considerado normal para el sensor.">Valor Máximo Normal</th>
                                            <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="gato[temperatura][valor_maximo]" value="<?= htmlspecialchars($config_alertas['temperatura']['valor_maximo'] ?? '') ?>" required></td>
                                            <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="gato[temperatura][mensaje_max_normal]" value="<?= htmlspecialchars($config_alertas['temperatura']['mensaje_max_normal'] ?? '') ?>" required></td>
                                            <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="gato[ritmo_cardiaco][valor_maximo]" value="<?= htmlspecialchars($config_alertas['ritmo_cardiaco']['valor_maximo'] ?? '') ?>" required></td>
                                            <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="gato[ritmo_cardiaco][mensaje_max_normal]" value="<?= htmlspecialchars($config_alertas['ritmo_cardiaco']['mensaje_max_normal'] ?? '') ?>" required></td>
                                            <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="gato[bateria][valor_maximo]" value="<?= htmlspecialchars($config_alertas['bateria']['valor_maximo'] ?? '') ?>" required></td>
                                            <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="gato[bateria][mensaje_max_normal]" value="<?= htmlspecialchars($config_alertas['bateria']['mensaje_max_normal'] ?? '') ?>" required></td>
                                        </tr>
                                        <!-- Fila: Valor Mínimo Crítico -->
                                        <tr>
                                            <th class="text-start" data-bs-toggle="tooltip" title="El valor mínimo considerado crítico para el sensor.">Valor Mínimo Crítico</th>
                                            <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="gato[temperatura][valor_critico_minimo]" value="<?= htmlspecialchars($config_alertas['temperatura']['valor_critico_minimo'] ?? '') ?>" required></td>
                                            <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="gato[temperatura][mensaje_critico_min]" value="<?= htmlspecialchars($config_alertas['temperatura']['mensaje_critico_min'] ?? '') ?>" required></td>
                                            <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="gato[ritmo_cardiaco][valor_critico_minimo]" value="<?= htmlspecialchars($config_alertas['ritmo_cardiaco']['valor_critico_minimo'] ?? '') ?>" required></td>
                                            <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="gato[ritmo_cardiaco][mensaje_critico_min]" value="<?= htmlspecialchars($config_alertas['ritmo_cardiaco']['mensaje_critico_min'] ?? '') ?>" required></td>
                                            <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="gato[bateria][valor_critico_minimo]" value="<?= htmlspecialchars($config_alertas['bateria']['valor_critico_minimo'] ?? '') ?>" required></td>
                                            <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="gato[bateria][mensaje_critico_min]" value="<?= htmlspecialchars($config_alertas['bateria']['mensaje_critico_min'] ?? '') ?>" required></td>
                                        </tr>
                                        <!-- Fila: Valor Máximo Crítico -->
                                        <tr>
                                            <th class="text-start" data-bs-toggle="tooltip" title="El valor máximo considerado crítico para el sensor.">Valor Máximo Crítico</th>
                                            <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="gato[temperatura][valor_critico_maximo]" value="<?= htmlspecialchars($config_alertas['temperatura']['valor_critico_maximo'] ?? '') ?>" required></td>
                                            <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="gato[temperatura][mensaje_critico_max]" value="<?= htmlspecialchars($config_alertas['temperatura']['mensaje_critico_max'] ?? '') ?>" required></td>
                                            <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="gato[ritmo_cardiaco][valor_critico_maximo]" value="<?= htmlspecialchars($config_alertas['ritmo_cardiaco']['valor_critico_maximo'] ?? '') ?>" required></td>
                                            <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="gato[ritmo_cardiaco][mensaje_critico_max]" value="<?= htmlspecialchars($config_alertas['ritmo_cardiaco']['mensaje_critico_max'] ?? '') ?>" required></td>
                                            <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="gato[bateria][valor_critico_maximo]" value="<?= htmlspecialchars($config_alertas['bateria']['valor_critico_maximo'] ?? '') ?>" required></td>
                                            <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="gato[bateria][mensaje_critico_max]" value="<?= htmlspecialchars($config_alertas['bateria']['mensaje_critico_max'] ?? '') ?>" required></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Aquí iría la validación en tiempo real con JS -->
                        </div>
                        <!-- Tab Perro (con subpestañas para tamaños) -->
                        <div class="tab-pane fade" id="perro" role="tabpanel" aria-labelledby="tab-perro">
                            <ul class="nav nav-pills mb-3" id="tabsPerro" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-perro-pequeño" data-bs-toggle="pill" data-bs-target="#perro-pequeño" type="button" role="tab" aria-controls="perro-pequeño" aria-selected="true">Pequeño</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-perro-mediano" data-bs-toggle="pill" data-bs-target="#perro-mediano" type="button" role="tab" aria-controls="perro-mediano" aria-selected="false">Mediano</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-perro-grande" data-bs-toggle="pill" data-bs-target="#perro-grande" type="button" role="tab" aria-controls="perro-grande" aria-selected="false">Grande</button>
                                </li>
                            </ul>
                            <div class="tab-content" id="tabsPerroContent">
                                <!-- Perro Pequeño -->
                                <div class="tab-pane fade show active" id="perro-pequeño" role="tabpanel" aria-labelledby="tab-perro-pequeño">
                                    <!-- Tabla de configuración para Perro Pequeño -->
                                    <?php $k = 'perro_pequeño'; ?>
                                    <div class="table-responsive">
                                        <table class="table align-middle text-center mb-0">
                                            <thead style="background:var(--color-bg-header,#f5f6fa);font-family:'Poppins',sans-serif;font-weight:500;">
                                                <tr>
                                                    <th></th>
                                                    <th colspan="2"><span data-bs-toggle="tooltip" title="Temperatura corporal"><i class="fas fa-thermometer-half text-danger"></i> Temperatura (°C)</span></th>
                                                    <th colspan="2"><span data-bs-toggle="tooltip" title="Ritmo cardíaco"><i class="fas fa-heartbeat text-primary"></i> Ritmo Cardíaco (BPM)</span></th>
                                                    <th colspan="2"><span data-bs-toggle="tooltip" title="Nivel de batería"><i class="fas fa-battery-half text-warning"></i> Batería (%)</span></th>
                                                </tr>
                                                <tr>
                                                    <th></th>
                                                    <th style="width:7rem">Valor</th>
                                                    <th style="width:16rem">Mensaje de Alerta</th>
                                                    <th style="width:7rem">Valor</th>
                                                    <th style="width:16rem">Mensaje de Alerta</th>
                                                    <th style="width:7rem">Valor</th>
                                                    <th style="width:16rem">Mensaje de Alerta</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th class="text-start" data-bs-toggle="tooltip" title="El valor mínimo considerado normal para el sensor.">Valor Mínimo Normal</th>
                                                    <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_pequeño[temperatura][valor_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['valor_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_pequeño[temperatura][mensaje_min_normal]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['mensaje_min_normal'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_pequeño[ritmo_cardiaco][valor_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['valor_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_pequeño[ritmo_cardiaco][mensaje_min_normal]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['mensaje_min_normal'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_pequeño[bateria][valor_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['valor_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_pequeño[bateria][mensaje_min_normal]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['mensaje_min_normal'] ?? '') ?>" required></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-start" data-bs-toggle="tooltip" title="El valor máximo considerado normal para el sensor.">Valor Máximo Normal</th>
                                                    <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_pequeño[temperatura][valor_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['valor_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_pequeño[temperatura][mensaje_max_normal]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['mensaje_max_normal'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_pequeño[ritmo_cardiaco][valor_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['valor_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_pequeño[ritmo_cardiaco][mensaje_max_normal]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['mensaje_max_normal'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_pequeño[bateria][valor_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['valor_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_pequeño[bateria][mensaje_max_normal]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['mensaje_max_normal'] ?? '') ?>" required></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-start" data-bs-toggle="tooltip" title="El valor mínimo considerado crítico para el sensor.">Valor Mínimo Crítico</th>
                                                    <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_pequeño[temperatura][valor_critico_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['valor_critico_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_pequeño[temperatura][mensaje_critico_min]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['mensaje_critico_min'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_pequeño[ritmo_cardiaco][valor_critico_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['valor_critico_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_pequeño[ritmo_cardiaco][mensaje_critico_min]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['mensaje_critico_min'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_pequeño[bateria][valor_critico_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['valor_critico_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_pequeño[bateria][mensaje_critico_min]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['mensaje_critico_min'] ?? '') ?>" required></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-start" data-bs-toggle="tooltip" title="El valor máximo considerado crítico para el sensor.">Valor Máximo Crítico</th>
                                                    <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_pequeño[temperatura][valor_critico_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['valor_critico_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_pequeño[temperatura][mensaje_critico_max]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['mensaje_critico_max'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_pequeño[ritmo_cardiaco][valor_critico_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['valor_critico_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_pequeño[ritmo_cardiaco][mensaje_critico_max]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['mensaje_critico_max'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_pequeño[bateria][valor_critico_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['valor_critico_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_pequeño[bateria][mensaje_critico_max]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['mensaje_critico_max'] ?? '') ?>" required></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- Perro Mediano -->
                                <div class="tab-pane fade" id="perro-mediano" role="tabpanel" aria-labelledby="tab-perro-mediano">
                                    <?php $k = 'perro_mediano'; ?>
                                    <div class="table-responsive">
                                        <table class="table align-middle text-center mb-0">
                                            <thead style="background:var(--color-bg-header,#f5f6fa);font-family:'Poppins',sans-serif;font-weight:500;">
                                                <tr>
                                                    <th></th>
                                                    <th colspan="2"><span data-bs-toggle="tooltip" title="Temperatura corporal"><i class="fas fa-thermometer-half text-danger"></i> Temperatura (°C)</span></th>
                                                    <th colspan="2"><span data-bs-toggle="tooltip" title="Ritmo cardíaco"><i class="fas fa-heartbeat text-primary"></i> Ritmo Cardíaco (BPM)</span></th>
                                                    <th colspan="2"><span data-bs-toggle="tooltip" title="Nivel de batería"><i class="fas fa-battery-half text-warning"></i> Batería (%)</span></th>
                                                </tr>
                                                <tr>
                                                    <th></th>
                                                    <th style="width:7rem">Valor</th>
                                                    <th style="width:16rem">Mensaje de Alerta</th>
                                                    <th style="width:7rem">Valor</th>
                                                    <th style="width:16rem">Mensaje de Alerta</th>
                                                    <th style="width:7rem">Valor</th>
                                                    <th style="width:16rem">Mensaje de Alerta</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th class="text-start" data-bs-toggle="tooltip" title="El valor mínimo considerado normal para el sensor.">Valor Mínimo Normal</th>
                                                    <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_mediano[temperatura][valor_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['valor_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_mediano[temperatura][mensaje_min_normal]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['mensaje_min_normal'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_mediano[ritmo_cardiaco][valor_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['valor_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_mediano[ritmo_cardiaco][mensaje_min_normal]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['mensaje_min_normal'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_mediano[bateria][valor_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['valor_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_mediano[bateria][mensaje_min_normal]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['mensaje_min_normal'] ?? '') ?>" required></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-start" data-bs-toggle="tooltip" title="El valor máximo considerado normal para el sensor.">Valor Máximo Normal</th>
                                                    <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_mediano[temperatura][valor_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['valor_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_mediano[temperatura][mensaje_max_normal]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['mensaje_max_normal'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_mediano[ritmo_cardiaco][valor_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['valor_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_mediano[ritmo_cardiaco][mensaje_max_normal]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['mensaje_max_normal'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_mediano[bateria][valor_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['valor_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_mediano[bateria][mensaje_max_normal]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['mensaje_max_normal'] ?? '') ?>" required></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-start" data-bs-toggle="tooltip" title="El valor mínimo considerado crítico para el sensor.">Valor Mínimo Crítico</th>
                                                    <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_mediano[temperatura][valor_critico_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['valor_critico_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_mediano[temperatura][mensaje_critico_min]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['mensaje_critico_min'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_mediano[ritmo_cardiaco][valor_critico_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['valor_critico_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_mediano[ritmo_cardiaco][mensaje_critico_min]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['mensaje_critico_min'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_mediano[bateria][valor_critico_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['valor_critico_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_mediano[bateria][mensaje_critico_min]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['mensaje_critico_min'] ?? '') ?>" required></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-start" data-bs-toggle="tooltip" title="El valor máximo considerado crítico para el sensor.">Valor Máximo Crítico</th>
                                                    <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_mediano[temperatura][valor_critico_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['valor_critico_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_mediano[temperatura][mensaje_critico_max]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['mensaje_critico_max'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_mediano[ritmo_cardiaco][valor_critico_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['valor_critico_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_mediano[ritmo_cardiaco][mensaje_critico_max]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['mensaje_critico_max'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_mediano[bateria][valor_critico_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['valor_critico_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_mediano[bateria][mensaje_critico_max]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['mensaje_critico_max'] ?? '') ?>" required></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- Perro Grande -->
                                <div class="tab-pane fade" id="perro-grande" role="tabpanel" aria-labelledby="tab-perro-grande">
                                    <?php $k = 'perro_grande'; ?>
                                    <div class="table-responsive">
                                        <table class="table align-middle text-center mb-0">
                                            <thead style="background:var(--color-bg-header,#f5f6fa);font-family:'Poppins',sans-serif;font-weight:500;">
                                                <tr>
                                                    <th></th>
                                                    <th colspan="2"><span data-bs-toggle="tooltip" title="Temperatura corporal"><i class="fas fa-thermometer-half text-danger"></i> Temperatura (°C)</span></th>
                                                    <th colspan="2"><span data-bs-toggle="tooltip" title="Ritmo cardíaco"><i class="fas fa-heartbeat text-primary"></i> Ritmo Cardíaco (BPM)</span></th>
                                                    <th colspan="2"><span data-bs-toggle="tooltip" title="Nivel de batería"><i class="fas fa-battery-half text-warning"></i> Batería (%)</span></th>
                                                </tr>
                                                <tr>
                                                    <th></th>
                                                    <th style="width:7rem">Valor</th>
                                                    <th style="width:16rem">Mensaje de Alerta</th>
                                                    <th style="width:7rem">Valor</th>
                                                    <th style="width:16rem">Mensaje de Alerta</th>
                                                    <th style="width:7rem">Valor</th>
                                                    <th style="width:16rem">Mensaje de Alerta</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th class="text-start" data-bs-toggle="tooltip" title="El valor mínimo considerado normal para el sensor.">Valor Mínimo Normal</th>
                                                    <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_grande[temperatura][valor_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['valor_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_grande[temperatura][mensaje_min_normal]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['mensaje_min_normal'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_grande[ritmo_cardiaco][valor_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['valor_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_grande[ritmo_cardiaco][mensaje_min_normal]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['mensaje_min_normal'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_grande[bateria][valor_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['valor_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_grande[bateria][mensaje_min_normal]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['mensaje_min_normal'] ?? '') ?>" required></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-start" data-bs-toggle="tooltip" title="El valor máximo considerado normal para el sensor.">Valor Máximo Normal</th>
                                                    <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_grande[temperatura][valor_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['valor_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_grande[temperatura][mensaje_max_normal]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['mensaje_max_normal'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_grande[ritmo_cardiaco][valor_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['valor_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_grande[ritmo_cardiaco][mensaje_max_normal]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['mensaje_max_normal'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_grande[bateria][valor_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['valor_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_grande[bateria][mensaje_max_normal]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['mensaje_max_normal'] ?? '') ?>" required></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-start" data-bs-toggle="tooltip" title="El valor mínimo considerado crítico para el sensor.">Valor Mínimo Crítico</th>
                                                    <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_grande[temperatura][valor_critico_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['valor_critico_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_grande[temperatura][mensaje_critico_min]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['mensaje_critico_min'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_grande[ritmo_cardiaco][valor_critico_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['valor_critico_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_grande[ritmo_cardiaco][mensaje_critico_min]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['mensaje_critico_min'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_grande[bateria][valor_critico_minimo]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['valor_critico_minimo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_grande[bateria][mensaje_critico_min]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['mensaje_critico_min'] ?? '') ?>" required></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-start" data-bs-toggle="tooltip" title="El valor máximo considerado crítico para el sensor.">Valor Máximo Crítico</th>
                                                    <td><input type="number" step="0.1" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_grande[temperatura][valor_critico_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['valor_critico_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_grande[temperatura][mensaje_critico_max]" value="<?= htmlspecialchars($config_alertas[$k]['temperatura']['mensaje_critico_max'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_grande[ritmo_cardiaco][valor_critico_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['valor_critico_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_grande[ritmo_cardiaco][mensaje_critico_max]" value="<?= htmlspecialchars($config_alertas[$k]['ritmo_cardiaco']['mensaje_critico_max'] ?? '') ?>" required></td>
                                                    <td><input type="number" class="form-control form-control-sm input-valor" style="height:2rem;" name="perro_grande[bateria][valor_critico_maximo]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['valor_critico_maximo'] ?? '') ?>" required></td>
                                                    <td><input type="text" class="form-control form-control-sm" style="height:2rem;" name="perro_grande[bateria][mensaje_critico_max]" value="<?= htmlspecialchars($config_alertas[$k]['bateria']['mensaje_critico_max'] ?? '') ?>" required></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light px-4 btn-cancelar" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" form="formConfiguracionAlertas" class="btn btn-primary px-4 btn-guardar">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Resto del contenido de alertas -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <!-- Filtros -->
            <form class="row g-2 mb-3">
                <div class="col-md-3">
                    <select class="form-select" name="tipo_alerta">
                        <option value="">Tipo de alerta</option>
                        <option value="temperatura">Temperatura</option>
                        <option value="ritmo_cardiaco">Ritmo cardíaco</option>
                        <option value="bateria">Batería baja</option>
                        <option value="inactividad">Inactividad</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="estado">
                        <option value="">Estado</option>
                        <option value="nueva">Nueva</option>
                        <option value="leida">Leída</option>
                        <option value="atendida">Atendida</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="mascota">
                        <option value="">Mascota</option>
                        <?php foreach ($mascotas as $mascota): ?>
                            <option value="<?= $mascota['id_mascota'] ?>"><?= htmlspecialchars($mascota['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="fecha">
                </div>
            </form>

            <div class="table-responsive">
                <table class="tabla-app" id="tablaAlertas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha/Hora</th>
                            <th>Dispositivo</th>
                            <th>Mascota</th>
                            <th>Dueño</th>
                            <th>Tipo</th>
                            <th>Mensaje</th>
                            <th>Leída</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($alertas)): ?>
                            <?php foreach ($alertas as $alerta): ?>
                            <tr>
                                <td><?= htmlspecialchars($alerta['id_alerta']) ?></td>
                                <td><?= htmlspecialchars($alerta['fecha_registro']) ?></td>
                                <td><?= htmlspecialchars($alerta['dispositivo_nombre'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($alerta['mascota_nombre'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($alerta['propietario_nombre'] ?? '-') ?></td>
                                <td>
                                    <?php if ($alerta['tipo_alerta'] == 'temperatura'): ?>
                                        <span class="badge bg-danger"><i class="fas fa-thermometer-half"></i> Temperatura</span>
                                    <?php elseif ($alerta['tipo_alerta'] == 'frecuencia_cardiaca'): ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-heartbeat"></i> Ritmo</span>
                                    <?php elseif ($alerta['tipo_alerta'] == 'bateria'): ?>
                                        <span class="badge bg-info text-dark"><i class="fas fa-battery-quarter"></i> Batería</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fas fa-unlink"></i> Inactividad</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($alerta['mensaje'] ?? '-') ?></td>
                                <td>
                                    <?php if (isset($alerta['estado']) && $alerta['estado'] == 'leida'): ?>
                                        <span class="badge bg-primary">Leída</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Nueva</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detalleAlerta<?= $alerta['id_alerta'] ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="marcarAtendida(<?= $alerta['id_alerta'] ?>)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                            <!-- Modal de detalle de alerta -->
                            <div class="modal fade" id="detalleAlerta<?= $alerta['id_alerta'] ?>" tabindex="-1" aria-labelledby="detalleAlertaLabel<?= $alerta['id_alerta'] ?>" aria-hidden="true" data-bs-backdrop="false">
                              <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="detalleAlertaLabel<?= $alerta['id_alerta'] ?>">Detalle de la Alerta</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                  </div>
                                  <div class="modal-body">
                                    <p><strong>Tipo:</strong> <?= htmlspecialchars($alerta['tipo_alerta']) ?></p>
                                    <p><strong>Mensaje:</strong> <?= htmlspecialchars($alerta['mensaje']) ?></p>
                                    <p><strong>Fecha/Hora:</strong> <?= htmlspecialchars($alerta['fecha_registro']) ?></p>
                                    <p><strong>Mascota:</strong> <?= htmlspecialchars($alerta['mascota_nombre'] ?? '-') ?></p>
                                    <p><strong>Dispositivo:</strong> <?= htmlspecialchars($alerta['dispositivo_nombre'] ?? '-') ?></p>
                                    <p><strong>Propietario:</strong> <?= htmlspecialchars($alerta['propietario_nombre'] ?? '-') ?></p>
                                  </div>
                                  <div class="modal-footer">
                                    <button class="btn btn-success" onclick="marcarAtendida(<?= $alerta['id_alerta'] ?>)">Marcar como atendida</button>
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No hay alertas registradas.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --color-bg-header: #f5f6fa;
        --color-bg-modal: #fff;
        --color-text: #222;
        --color-border: #e0e0e0;
        --color-primary: #2563eb;
        --color-cancel: #f3f4f6;
        --color-cancel-hover: #e5e7eb;
        --color-btn-hover: #1d4ed8;
    }
    [data-theme="dark"] {
        --color-bg-header: #23272f;
        --color-bg-modal: #181a20;
        --color-text: #f5f6fa;
        --color-border: #333;
        --color-primary: #60a5fa;
        --color-cancel: #23272f;
        --color-cancel-hover: #181a20;
        --color-btn-hover: #2563eb;
    }
    .modal-content { background: var(--color-bg-modal); color: var(--color-text); }
    .table thead th { background: var(--color-bg-header) !important; font-family: 'Poppins', sans-serif; font-weight: 500; }
    .form-control, .form-control:focus { font-family: 'Poppins', sans-serif; font-size: 1rem; }
    .input-valor { width: 6rem; min-width: 4rem; text-align: center; }
    .btn-cancelar { background: var(--color-cancel); color: var(--color-text); border: 1px solid var(--color-border); }
    .btn-cancelar:hover { background: var(--color-cancel-hover); }
    .btn-guardar { background: var(--color-primary); color: #fff; border: none; }
    .btn-guardar:hover { background: var(--color-btn-hover); }
    .spinner-border { vertical-align: middle; margin-right: 0.5rem; }
</style>
<script>
// Inicializar tooltips de Bootstrap
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.forEach(function (tooltipTriggerEl) {
  new bootstrap.Tooltip(tooltipTriggerEl);
});
// Validación en tiempo real de valores numéricos
const inputsValor = document.querySelectorAll('.input-valor');
inputsValor.forEach(input => {
  input.addEventListener('input', function() {
    if (this.value !== '' && (isNaN(this.value) || this.value < 0)) {
      this.classList.add('is-invalid');
    } else {
      this.classList.remove('is-invalid');
    }
  });
});
// Efecto loading en botón guardar
const btnGuardar = document.querySelector('.btn-guardar');
if (btnGuardar) {
  btnGuardar.addEventListener('click', function() {
    const spinner = this.querySelector('.spinner-border');
    if (spinner) spinner.classList.remove('d-none');
    setTimeout(() => { if (spinner) spinner.classList.add('d-none'); }, 2000);
  });
}
</script>

