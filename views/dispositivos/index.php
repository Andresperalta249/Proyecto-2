<?php
// Eliminar encabezados y subtítulos, incluyendo el include del header_titulo.php
?>
<!-- Botón flotante para agregar dispositivo -->
<?php if (verificarPermiso('crear_dispositivos')): ?>
<button class="fab-crear" id="btnNuevoDispositivoFlotante" data-bs-toggle="modal" data-bs-target="#modalDispositivo">
    <i class="fas fa-plus"></i>
    <span class="fab-text">Gestionar Dispositivo</span>
</button>
<?php endif; ?>
<!-- Barra de búsqueda y filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form id="formBuscarDispositivos" class="form-filtros d-flex align-items-end gap-2 mb-3">
            <div class="flex-grow-1">
                <input type="text" class="form-control" id="buscarDispositivo" name="busqueda" placeholder="Buscar por nombre, MAC o identificador...">
            </div>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalFiltrosMascotasPHP">
                <i class="fas fa-filter"></i> Filtros
            </button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
        </form>
        <!-- Modal de Filtros Avanzados eliminado completamente -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="tabla-app" id="tablaDispositivos">
                        <thead>
                            <tr>
                                <!-- <th></th> -->
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>MAC</th>
                                <th>Dueño</th>
                                <th>Disponible</th>
                                <th>Estado</th>
                                <th>Batería</th>
                                <th>Mascota</th>
                                <th>Última Lectura</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyDispositivos">
                            <?php
                            // Paginación
                            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                            $perPage = isset($perPage) ? $perPage : 10;
                            $totalDispositivos = isset($totalDispositivos) ? $totalDispositivos : count($dispositivos);
                            $totalPages = ceil($totalDispositivos / $perPage);
                            ?>
                            <?php foreach ($dispositivos as $dispositivo): ?>
                            <tr class="fila-dispositivo" data-id="<?= $dispositivo['id'] ?>">
                                <!-- <td class="dt-control" style="cursor:pointer;"></td> -->
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
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Controles de paginación -->
        <nav aria-label="Paginación de dispositivos">
          <ul class="pagination justify-content-center mt-3">
            <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $page - 1 ?>">Anterior</a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <li class="page-item<?= $i == $page ? ' active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item<?= $page >= $totalPages ? ' disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $page + 1 ?>">Siguiente</a>
            </li>
          </ul>
        </nav>
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
// Esperar a que el documento esté listo y jQuery esté disponible
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
        var i = 0;
        function next() {
            if (i < scripts.length) {
                var s = document.createElement('script');
                s.src = scripts[i++];
                s.onload = next;
                document.head.appendChild(s);
            } else {
                callback();
            }
        }
        next();
    }

    // Función principal que se ejecutará cuando todo esté cargado
    function ejecutarDispositivosJS() {
        // Inicializar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Cambiar estado
        $(document).on('change', '.cambiar-estado-dispositivo', function() {
            const id = $(this).data('id');
            const estado = $(this).is(':checked') ? 'activo' : 'inactivo';
            const $checkbox = $(this);
            const $label = $checkbox.siblings('.form-check-label');
            
            $.ajax({
                url: '<?= BASE_URL ?>dispositivos/cambiarEstado',
                type: 'POST',
                data: { id, estado },
                dataType: 'json',
                beforeSend: function() {
                    $checkbox.prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        $label.text(estado.charAt(0).toUpperCase() + estado.slice(1));
                        $label.removeClass('estado-activo estado-inactivo')
                              .addClass(estado === 'activo' ? 'estado-activo' : 'estado-inactivo');
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Estado actualizado correctamente',
                            showConfirmButton: false,
                            timer: 1800,
                            timerProgressBar: true
                        });
                    } else {
                        $checkbox.prop('checked', !$checkbox.prop('checked'));
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: response.error || 'No se pudo cambiar el estado',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    }
                },
                error: function(xhr) {
                    $checkbox.prop('checked', !$checkbox.prop('checked'));
                    let errorMsg = 'No se pudo cambiar el estado. Intenta de nuevo.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    }
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: errorMsg,
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                },
                complete: function() {
                    $checkbox.prop('disabled', false);
                }
            });
        });
        // Eliminar dispositivo
        $(document).on('click', '.eliminar-dispositivo', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= BASE_URL ?>dispositivos/delete/' + id,
                        type: 'POST',
                        success: function(response) {
                            if (typeof response === 'string') {
                                response = JSON.parse(response);
                            }
                            if (response.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: response.message,
                                    showConfirmButton: false,
                                    timer: 1800,
                                    timerProgressBar: true
                                }).then(() => { location.reload(); });
                            } else {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: response.error || 'No se pudo eliminar',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    timerProgressBar: true
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'No se pudo eliminar el dispositivo',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true
                            });
                        }
                    });
                }
            });
        });
        // Cargar mascotas disponibles al seleccionar usuario
        $('#usuario_id_asignar, #usuario_id_nuevo').on('change', function() {
            var usuarioId = $(this).val();
            if (usuarioId) {
                $.get('<?= BASE_URL ?>dispositivos/obtenerMascotasSinDispositivo/' + usuarioId, function(response) {
                    var options = '<option value="">Seleccione una mascota (opcional)...</option>';
                    if (response.success && response.data) {
                        response.data.forEach(function(mascota) {
                            options += '<option value="' + mascota.id + '">' + mascota.nombre + '</option>';
                        });
                    }
                    $('#mascota_id_asignar, #mascota_id_nuevo').html(options);
                });
            } else {
                $('#mascota_id_asignar, #mascota_id_nuevo').html('<option value="">Seleccione una mascota (opcional)...</option>');
            }
        });
        // Cargar dispositivos disponibles al abrir el modal
        $('#modalDispositivo').on('show.bs.modal', function() {
            $.get('<?= BASE_URL ?>dispositivos/obtenerDispositivosDisponibles', function(dispositivos) {
                var options = '<option value="">Seleccione un dispositivo disponible...</option>';
                dispositivos.forEach(function(dispositivo) {
                    options += '<option value="' + dispositivo.id + '">' + dispositivo.nombre + '</option>';
                });
                $('#dispositivo_id_asignar').html(options);
            });
        });
        // Enviar formulario Nuevo Dispositivo
        $('#formNuevoDispositivo').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            var nombreUnico = 'Dispositivo_' + Math.random().toString(36).substr(2, 9);
            formData.set('nombre', nombreUnico);
            var formDataObj = {};
            formData.forEach((value, key) => formDataObj[key] = value);
            $.ajax({
                url: '<?= BASE_URL ?>dispositivos/create',
                type: 'POST',
                data: formDataObj,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 1800,
                            timerProgressBar: true
                        }).then(() => { location.reload(); });
                    } else {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: response.error || 'No se pudo crear el dispositivo',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'No se pudo crear el dispositivo. Por favor, intente nuevamente.',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                }
            });
        });
        // Enviar formulario Asignar/Reasignar Dispositivo
        $('#formAsignarDispositivo').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '<?= BASE_URL ?>dispositivos/asignar',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 1800,
                            timerProgressBar: true
                        }).then(() => { location.reload(); });
                    } else {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: response.error || 'No se pudo asignar el dispositivo',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'No se pudo asignar el dispositivo',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                }
            });
        });
        // Filtros y búsqueda AJAX
        $('#formBuscarDispositivos').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var datos = form.serialize();
            $('#tbodyDispositivos').html('<tr><td colspan="10" class="text-center">Cargando...</td></tr>');
            $.ajax({
                url: '<?= BASE_URL ?>dispositivos/filtrar',
                type: 'POST',
                data: datos,
                dataType: 'json',
                success: function(res) {
                    if (res.success && res.html) {
                        $('#tbodyDispositivos').html(res.html);
                        // Reinicializar tooltips y otros JS
                        const dropdownTooltipList = [].slice.call(document.querySelectorAll('.dropdown-menu [data-bs-toggle="tooltip"]'));
                        dropdownTooltipList.map(function (el) { return new bootstrap.Tooltip(el); });
                        const nombreTooltipList = [].slice.call(document.querySelectorAll('td[data-bs-toggle="tooltip"]'));
                        nombreTooltipList.map(function (el) { return new bootstrap.Tooltip(el); });
                    } else {
                        $('#tbodyDispositivos').html('<tr><td colspan="10" class="text-center">No se encontraron resultados</td></tr>');
                    }
                },
                error: function() {
                    $('#tbodyDispositivos').html('<tr><td colspan="10" class="text-center text-danger">Error al buscar dispositivos</td></tr>');
                }
            });
        });
        // Mostrar todos los dispositivos si los filtros están vacíos
        $('#formBuscarDispositivos input, #formBuscarDispositivos select').on('change', function() {
            var vacio = true;
            $('#formBuscarDispositivos input, #formBuscarDispositivos select').each(function() {
                if ($(this).val() && $(this).val() !== '') {
                    vacio = false;
                }
            });
            if (vacio) {
                $('#formBuscarDispositivos').trigger('submit');
            }
        });
        // Inicializar DataTable solo en escritorio
        if (window.innerWidth > 430) {
            var table = $('#tablaDispositivos').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                responsive: false,
                scrollCollapse: true,
                paging: false,
                info: false,
                searching: false,
                dom: 't',
                order: [[0, 'asc']],
                columnDefs: [
                    {
                        targets: [0, 1], // ID y Nombre
                        className: 'fixed-column'
                    }
                ]
            });

            // Formato de los botones de acción
            function format(rowData) {
                var id = $(rowData[0]).text() || rowData[0];
                return `
                    <div style=\"padding:0.5rem 1rem;\">
                        <div class=\"btn-group\" role=\"group\">
                            <a class=\"btn-accion btn-primary\" href=\"<?= BASE_URL ?>monitor/device/${id}\" data-bs-toggle=\"tooltip\" title=\"Monitor en vivo\">
                                <i class=\"fas fa-chart-line\"></i>
                            </a>
                            <?php if (verificarPermiso('editar_dispositivos')): ?>
                            <button class=\"btn-accion btn-info editar-dispositivo\" data-id=\"${id}\" data-bs-toggle=\"tooltip\" title=\"Editar\">
                                <i class=\"fas fa-edit\"></i>
                            </button>
                            <?php endif; ?>
                            <?php if (verificarPermiso('eliminar_dispositivos')): ?>
                            <button class=\"btn-accion btn-danger eliminar-dispositivo\" data-id=\"${id}\" data-bs-toggle=\"tooltip\" title=\"Eliminar\">
                                <i class=\"fas fa-trash-alt\"></i>
                            </button>
                            <?php endif; ?>
                            <?php if (verificarPermiso('editar_dispositivos')): ?>
                            <button class=\"btn-accion btn-dark asignar-dispositivo\" data-id=\"${id}\" data-bs-toggle=\"tooltip\" title=\"Asignar/Reasignar\">
                                <i class=\"fas fa-user-plus\"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                `;
            }

            // Evento para expandir/cerrar child row al hacer clic en la fila (excepto en los botones)
            $('#tablaDispositivos tbody').on('click', 'tr.fila-dispositivo', function (e) {
                // Evitar que el clic en los botones de acción dispare el evento
                if ($(e.target).closest('.btn-accion').length) return;
                var tr = $(this);
                var row = table.row(tr);
                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    // Cierra otros child rows abiertos
                    table.rows('.shown').every(function() {
                        this.child.hide();
                        $(this.node()).removeClass('shown');
                    });
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });
        }
    }

    // Cargar jQuery si no está disponible
    if (typeof $ === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
        script.onload = function() {
            cargarDataTablesYPlugins(ejecutarDispositivosJS);
        };
        document.head.appendChild(script);
    } else if (typeof $.fn.DataTable === 'undefined') {
        cargarDataTablesYPlugins(ejecutarDispositivosJS);
    } else {
        ejecutarDispositivosJS();
    }

    // Máscara dinámica para MAC con ceros como guía y validación AJAX
    const macInput = document.getElementById('mac_nuevo');
    const macError = document.getElementById('macError');
    macInput.addEventListener('input', function(e) {
        let value = macInput.value.replace(/[^0-9A-Fa-f]/g, '').toUpperCase();
        if (value.length > 12) value = value.slice(0, 12);
        let formatted = '';
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 2 === 0) formatted += ':';
            formatted += value[i];
        }
        macInput.value = formatted;
        const macPattern = /^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/;
        if (macPattern.test(formatted)) {
            macInput.classList.remove('is-invalid');
            macInput.classList.add('is-valid');
            $.post('<?= BASE_URL ?>dispositivos/verificarMac', {mac: formatted}, function(res) {
                if (res.exists) {
                    macError.style.display = 'block';
                    macError.textContent = '⚠️ Esta dirección MAC ya está registrada en otro dispositivo.';
                    macInput.classList.remove('is-valid');
                    macInput.classList.add('is-invalid');
                } else {
                    macError.style.display = 'none';
                    macError.textContent = '';
                }
            }, 'json');
        } else {
            macInput.classList.remove('is-valid');
            macInput.classList.add('is-invalid');
            macError.style.display = 'none';
            macError.textContent = '';
        }
    });

    // Función para cargar detalles del dispositivo
    function cargarDetallesDispositivo(id) {
        $.get('<?= BASE_URL ?>dispositivos/obtenerDetalles/' + id, function(response) {
            if (response.success) {
                const dispositivo = response.data;
                $('#detalleId').text(dispositivo.id);
                $('#detalleNombre').text(dispositivo.nombre);
                $('#detalleMac').text(dispositivo.mac);
                $('#detalleEstado').text(dispositivo.estado);
                $('#detalleBateria').text(dispositivo.bateria || '-');
                $('#detalleUltimaLectura').text(dispositivo.ultima_lectura || '-');
                $('#detalleUsuario').text(dispositivo.usuario_nombre || '-');
                $('#detalleMascota').text(dispositivo.mascota_nombre || '-');
                $('#detalleFechaAsignacion').text(dispositivo.fecha_asignacion || '-');
                $('#modalDetallesDispositivo').modal('show');
            } else {
                Swal.fire('Error', 'No se pudieron cargar los detalles del dispositivo', 'error');
            }
        });
    }

    // Función para cargar datos del dispositivo para edición
    function cargarDatosEdicion(id) {
        $.get('<?= BASE_URL ?>dispositivos/obtenerDetalles/' + id, function(response) {
            if (response.success) {
                const dispositivo = response.data;
                $('#edit_id').val(dispositivo.id);
                $('#edit_nombre').val(dispositivo.nombre);
                $('#edit_mac').val(dispositivo.mac);
                $('#edit_estado').val(dispositivo.estado);
                $('#modalEditarDispositivo').modal('show');
            } else {
                Swal.fire('Error', 'No se pudieron cargar los datos del dispositivo', 'error');
            }
        });
    }

    // Función para preparar modal de asignación
    function prepararAsignacion(id) {
        $('#asignar_dispositivo_id').val(id);
        $('#usuario_id_asignar').val('');
        $('#mascota_id_asignar').html('<option value="">Seleccione una mascota...</option>');
        $('#modalAsignarDispositivo').modal('show');
    }

    // Eventos para los botones de acción
    $(document).on('click', '.ver-detalles', function() {
        const id = $(this).data('id');
        cargarDetallesDispositivo(id);
    });

    $(document).on('click', '.editar-dispositivo', function() {
        const id = $(this).data('id');
        cargarDatosEdicion(id);
    });

    $(document).on('click', '.asignar-dispositivo', function() {
        const id = $(this).data('id');
        prepararAsignacion(id);
    });

    // Manejo del formulario de edición
    $('#formEditarDispositivo').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.ajax({
            url: '<?= BASE_URL ?>dispositivos/update',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire('¡Éxito!', 'Dispositivo actualizado correctamente', 'success').then(() => {
                        $('#modalEditarDispositivo').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.error || 'No se pudo actualizar el dispositivo', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo actualizar el dispositivo', 'error');
            }
        });
    });

    // Validación de MAC en edición
    $('#edit_mac').on('input', function() {
        let value = $(this).val().replace(/[^0-9A-Fa-f]/g, '').toUpperCase();
        if (value.length > 12) value = value.slice(0, 12);
        let formatted = '';
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 2 === 0) formatted += ':';
            formatted += value[i];
        }
        $(this).val(formatted);
        
        const macPattern = /^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/;
        if (macPattern.test(formatted)) {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $.post('<?= BASE_URL ?>dispositivos/verificarMac', {mac: formatted, id: $('#edit_id').val()}, function(res) {
                if (res.exists) {
                    $('#edit_macError').show().text('⚠️ Esta dirección MAC ya está registrada en otro dispositivo.');
                    $('#edit_mac').removeClass('is-valid').addClass('is-invalid');
                } else {
                    $('#edit_macError').hide();
                }
            }, 'json');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
            $('#edit_macError').hide();
        }
    });

    // Inicializar tooltips de Bootstrap para los íconos del dropdown
    const dropdownTooltipList = [].slice.call(document.querySelectorAll('.dropdown-menu [data-bs-toggle="tooltip"]'));
    dropdownTooltipList.map(function (el) { return new bootstrap.Tooltip(el); });

    // Inicializar tooltips para los nombres truncados
    const nombreTooltipList = [].slice.call(document.querySelectorAll('td[data-bs-toggle="tooltip"]'));
    nombreTooltipList.map(function (el) { return new bootstrap.Tooltip(el); });

    // Acordeón de detalles en mobile
    function isMobile() {
        return window.innerWidth <= 430;
    }
    $(document).on('click', '.fila-dispositivo', function() {
        if (!isMobile()) return;
        var $fila = $(this);
        var $detalle = $fila.next('.detalle-mobile');
        if ($detalle.is(':visible')) {
            $detalle.slideUp(150);
        } else {
            $('.detalle-mobile').slideUp(150); // Cierra otros
            $detalle.slideDown(180);
        }
    });
    // Al cambiar de tamaño de pantalla, oculta los detalles
    $(window).on('resize', function() {
        if (!isMobile()) {
            $('.detalle-mobile').hide();
        }
    });

    $(document).ready(function() {
        $('#tablaDispositivos').on('click', '.fila-dispositivo', function() {
            if (window.innerWidth > 430) {
                // Solo en escritorio: mostrar fila de acciones
                $('.fila-acciones').hide();
                var $acciones = $(this).next('.fila-acciones');
                $acciones.toggle();
            }
            // En mobile, el acordeón de detalles ya está implementado más arriba
        });
        // Ocultar la fila de acciones al hacer clic fuera de la tabla (solo escritorio)
        $(document).on('click', function(e) {
            if (window.innerWidth > 430 && !$(e.target).closest('#tablaDispositivos').length) {
                $('.fila-acciones').hide();
            }
        });
    });
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