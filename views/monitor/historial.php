<div class="card mb-3">
    <div class="card-header">Historial de Registros</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Temperatura</th>
                        <th>Batería</th>
                        <th>Ritmo Cardíaco</th>
                        <th>Ubicación</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($ultimosDatos)): ?>
                    <?php foreach (array_slice($ultimosDatos, 0, 8) as $dato): ?>
                    <tr>
                        <td><?= isset($dato['fecha']) ? date('d/m/Y H:i:s', strtotime($dato['fecha'])) : '-' ?></td>
                        <td><?= isset($dato['temperatura']) ? number_format($dato['temperatura'], 1) . '°C' : '-' ?></td>
                        <td><?= isset($dato['bateria']) ? number_format($dato['bateria'], 1) . '%' : '-' ?></td>
                        <td><?= isset($dato['bpm']) ? intval($dato['bpm']) . ' bpm' : '-' ?></td>
                        <td>
                            <?php
                                $lat = isset($dato['latitude']) ? $dato['latitude'] : (isset($dato['lat']) ? $dato['lat'] : null);
                                $lng = isset($dato['longitude']) ? $dato['longitude'] : (isset($dato['lng']) ? $dato['lng'] : null);
                                echo ($lat !== null && $lng !== null) ? $lat . ', ' . $lng : '<span class="text-muted">-</span>';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted">No hay datos registrados.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <button class="btn btn-link w-100 mt-2">Ver historial completo</button>
    </div>
</div> 