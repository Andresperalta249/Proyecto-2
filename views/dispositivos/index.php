<?php
$header_buttons = '<div class="d-flex gap-2">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDispositivo">
        <i class="fas fa-plus"></i> Gestionar Dispositivo
    </button>
</div>';
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
                                <?php include __DIR__ . '/_fila.php'; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Aquí irá el modal unificado Gestionar Dispositivo -->
<div class="modal fade" id="modalDispositivo" tabindex="-1">
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
            $('#tablaDispositivos').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                responsive: false,
                scrollX: true,
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
<div class="modal fade" id="modalFiltrosDispositivosPHP" tabindex="-1" aria-labelledby="modalFiltrosDispositivosPHPLabel" aria-hidden="true">
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