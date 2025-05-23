<?php
// Permisos del usuario
$puedeCrear = true; // Permitimos que todos los usuarios puedan crear mascotas
$puedeEditar = in_array('editar_mascotas', $_SESSION['permissions'] ?? []);
$puedeEliminar = in_array('eliminar_mascotas', $_SESSION['permissions'] ?? []);
$esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2]); // 1: Superadmin, 2: Admin

// Determinar el título según permisos
$tituloMascotas = (function_exists('verificarPermiso') && verificarPermiso('ver_todas_mascotas')) ? 'Todas las Mascotas' : 'Mis Mascotas';
?>
<!-- Page Content -->
<div class="container-fluid">
    <!-- jQuery y otras dependencias -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="http://localhost/proyecto-2/assets/css/app.css" rel="stylesheet">

    <!-- Sustituir por estructura de títulos tipo monitor/device -->
    <div class="mb-2">
        <div class="text-secondary small mb-1">Gestión de Mascotas</div>
        <?php if ($tituloMascotas === 'Todas las Mascotas'): ?>
            <h1 class="fw-bold mb-0">Todas las Mascotas</h1>
        <?php endif; ?>
    </div>
    <!-- Tabla de mascotas -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <!-- Barra de búsqueda -->
            <form id="formBuscarMascota" class="form-filtros d-flex align-items-end gap-2 mb-3">
                <div class="flex-grow-1">
                    <input type="text" class="form-control" name="nombre" placeholder="Buscar por nombre...">
                </div>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalFiltrosMascotasPHP">
                    <i class="fas fa-filter"></i> Filtros
                </button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
            </form>
            <div class="table-responsive">
                <table class="tabla-app" id="tablaMascotas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Especie</th>
                            <th>Tamaño</th>
                            <th>Género</th>
                            <th>Propietario</th>
                            <th>Edad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mascotas as $mascota): ?>
                        <?php
                            $propietario = '';
                            if (!empty($mascota['propietario_id']) && !empty($usuarios)) {
                                foreach ($usuarios as $usuario) {
                                    if ($usuario['id'] == $mascota['propietario_id']) {
                                        $propietario = htmlspecialchars($usuario['nombre']);
                                        break;
                                    }
                                }
                            }
                        ?>
                        <tr class="fila-mascota" data-id="<?= $mascota['id'] ?>">
                            <td style="width: 48px;"><?= $mascota['id'] ?></td>
                            <td style="max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($mascota['nombre']) ?>"><?= htmlspecialchars($mascota['nombre']) ?></td>
                            <td style="width: 80px;"><?= htmlspecialchars($mascota['especie']) ?></td>
                            <td style="width: 80px;"><?= htmlspecialchars($mascota['tamano']) ?></td>
                            <td style="width: 60px;"><?= htmlspecialchars($mascota['genero'] ?? '-') ?></td>
                            <td style="max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= $propietario ?>"><?= $propietario ?: '-' ?></td>
                            <td style="width: 60px; text-align:center;">
                                <?php
                                if (!empty($mascota['fecha_nacimiento'])) {
                                    $nacimiento = new DateTime($mascota['fecha_nacimiento']);
                                    $hoy = new DateTime();
                                    $edad = $hoy->diff($nacimiento)->y;
                                    echo $edad . ' año' . ($edad != 1 ? 's' : '');
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td style="width: 110px;">
                                <div class="form-check form-switch d-flex align-items-center mb-0">
                                    <input class="form-check-input cambiar-estado-mascota <?= $mascota['estado'] === 'inactivo' ? 'switch-inactivo' : '' ?>"
                                        type="checkbox"
                                        data-id="<?= $mascota['id'] ?>"
                                        <?= $mascota['estado'] === 'activo' ? 'checked' : '' ?> >
                                    <label class="form-check-label ms-2">
                                        <?= ucfirst($mascota['estado']) ?>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Mascota (para crear y editar) -->
<div class="modal fade" id="modalMascota" tabindex="-1" aria-labelledby="modalMascotaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalMascotaLabel">Nueva Mascota</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php include __DIR__ . '/edit_modal.php'; ?>
      </div>
    </div>
  </div>
</div>

<!-- Botón flotante para agregar mascota -->
<?php if ($puedeCrear ?? true): ?>
<button class="fab-crear" id="btnNuevaMascotaFlotante">
    <i class="fas fa-plus"></i>
    <span class="fab-text">Agregar Mascota</span>
</button>
<?php endif; ?>

<!-- Modal de Filtros Avanzados -->
<div class="modal fade" id="modalFiltrosMascotasPHP" tabindex="-1" aria-labelledby="modalFiltrosMascotasPHPLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFiltrosMascotasPHPLabel">Filtros Avanzados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formFiltrosMascotasPHP">
                    <div class="mb-3">
                        <label for="filtroEstadoMascota" class="form-label">Estado</label>
                        <select class="form-select" id="filtroEstadoMascota" name="estado">
                            <option value="">Todos</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filtroEspecieMascota" class="form-label">Especie</label>
                        <select class="form-select" id="filtroEspecieMascota" name="especie">
                            <option value="">Todas</option>
                            <option value="perro">Perro</option>
                            <option value="gato">Gato</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filtroDuenoMascota" class="form-label">Dueño</label>
                        <select class="form-select" id="filtroDuenoMascota" name="dueno">
                            <option value="">Todos</option>
                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="aplicarFiltrosMascotasPHP">Aplicar Filtros</button>
            </div>
        </div>
    </div>
</div>

<script>
// Asegurarse de que jQuery esté disponible antes de ejecutar cualquier código
if (typeof jQuery === 'undefined') {
    console.error('jQuery no está disponible');
} else {
    $(document).ready(function() {
        // Inicializar Select2 en cualquier select que exista
        if ($.fn.select2) {
            $('.select2').select2({
                dropdownParent: $('#modalMascota')
            });
        }

        // Botón flotante para abrir el modal de nueva mascota
        $('#btnNuevaMascotaFlotante').on('click', function() {
            $('#modalMascotaLabel').text('Nueva Mascota');
            $('#formMascota')[0].reset();
            $('#formMascota input[name=id]').val('');
            $('#alertaMascota').html('');
            var modal = new bootstrap.Modal(document.getElementById('modalMascota'));
            modal.show();
        });

        // Manejar cambios en el campo especie
        $('#especie').on('change', function() {
            var especie = $(this).val();
            var $tamanoSelect = $('#tamano');
            
            if (especie === 'Gato') {
                $tamanoSelect.val('Pequeño');
                $tamanoSelect.prop('disabled', true);
            } else {
                $tamanoSelect.prop('disabled', false);
            }
        });

        // Hacer global la función para que esté disponible en scripts cargados por AJAX
        window.recargarTablaMascotas = function() {
            const formData = $('#formBuscarMascota').serialize();
            $.ajax({
                url: '/proyecto-2/mascotas/tabla?' + formData,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const mascotas = response.mascotas;
                    const usuarios = response.usuarios;
                    const permisos = response.permisos;
                    let filas = '';
                    if (mascotas.length > 0) {
                        mascotas.forEach(function(mascota) {
                            let propietario = '-';
                            if (mascota.propietario_id && usuarios.length > 0) {
                                const usuario = usuarios.find(u => u.id == mascota.propietario_id);
                                if (usuario) propietario = usuario.nombre;
                            }
                            let edad = '-';
                            if (mascota.fecha_nacimiento) {
                                const nacimiento = new Date(mascota.fecha_nacimiento);
                                const hoy = new Date();
                                let anios = hoy.getFullYear() - nacimiento.getFullYear();
                                const m = hoy.getMonth() - nacimiento.getMonth();
                                if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) {
                                    anios--;
                                }
                                edad = anios + ' año' + (anios !== 1 ? 's' : '');
                            }
                            let bateria = (mascota.bateria !== undefined && mascota.bateria !== null && mascota.bateria !== '') ? `<span style='color:#222;font-weight:500;'>${mascota.bateria}%</span>` : '-';
                            let estadoSwitch = `<div class='form-check form-switch d-flex align-items-center mb-0'>
                                <input class='form-check-input cambiar-estado-mascota ${(mascota.estado === 'inactivo' ? 'switch-inactivo' : '')}'
                                    type='checkbox'
                                    data-id='${mascota.id}'
                                    ${(mascota.estado === 'activo') ? 'checked' : ''} >
                                <label class='form-check-label ms-2'>${mascota.estado.charAt(0).toUpperCase() + mascota.estado.slice(1)}</label>
                            </div>`;
                            filas += `<tr class='fila-mascota' data-id='${mascota.id}'>
                                <td style='width: 48px;'>${mascota.id}</td>
                                <td style='max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;' title='${mascota.nombre}'>${mascota.nombre}</td>
                                <td style='width: 80px;'>${mascota.especie}</td>
                                <td style='width: 80px;'>${mascota.tamano}</td>
                                <td style='width: 60px;'>${mascota.genero || '-'}</td>
                                <td style='max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;' title='${propietario}'>${propietario}</td>
                                <td style='width: 60px; text-align:center;'>${edad}</td>
                                <td style='width: 110px;'>${estadoSwitch}</td>
                            </tr>`;
                        });
                    } else {
                        filas = `<tr><td colspan='10' class='text-center text-muted py-4'>No hay mascotas registradas.</td></tr>`;
                    }
                    $('#tablaMascotas tbody').html(filas);
                    // Reinicializar el evento de click en las filas para mostrar/ocultar botones
                    $('#tablaMascotas tbody').off('click', 'tr.fila-mascota');
                    $('#tablaMascotas tbody').on('click', 'tr.fila-mascota', function (e) {
                        if ($(e.target).closest('.btnEditarMascota, .btnEliminarMascota').length) return;
                        var tr = $(this);
                        var row = table.row(tr);
                        if (row.child.isShown()) {
                            row.child.hide();
                            tr.removeClass('shown');
                        } else {
                            table.rows('.shown').every(function() {
                                this.child.hide();
                                $(this.node()).removeClass('shown');
                            });
                            row.child(formatMascota(row.data())).show();
                            tr.addClass('shown');
                        }
                    });
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo recargar la tabla de mascotas.', 'error');
                }
            });
        }

        // Manejador para el formulario de búsqueda
        $('#formBuscarMascota').on('submit', function(e) {
            e.preventDefault();
            recargarTablaMascotas();
        });

        // Botón editar: abrir modal de edición por AJAX
        $('#tablaMascotas').on('click', '.btnEditarMascota', function(e) {
            e.preventDefault();
            var btn = $(e.target).closest('.btnEditarMascota');
            var id = btn.data('id');
            console.log('ID para editar:', id);
            
            $.ajax({
                url: 'mascotas/editarModal',
                type: 'GET',
                data: { id: id },
                success: function(response) {
                    // Solo reemplazar el contenido de .modal-body
                    $('#modalMascota .modal-body').html(response);
                    $('#modalMascotaLabel').text('Editar Mascota');
                    var modal = new bootstrap.Modal(document.getElementById('modalMascota'));
                    modal.show();
                },
                error: function(xhr) {
                    Swal.fire('Error', 'No se pudo cargar el formulario de edición', 'error');
                }
            });
        });

        // Manejar el envío del formulario de mascota
        $(document).on('submit', '#formMascota', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            var id = formData.get('id');
            var url = id ? 'mascotas/edit/' + id : 'mascotas/create';

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Éxito',
                            text: response.message || 'Operación realizada con éxito',
                            icon: 'success'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.error || 'Error al procesar la solicitud', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Error al procesar la solicitud', 'error');
                }
            });
        });

        // Delegar eventos para los botones de eliminar (por recarga AJAX)
        $('#tablaMascotas').on('click', '.btnEliminarMascota', function() {
            var id = $(this).data('id');
            var nombreMascota = $(this).closest('tr').find('td:nth-child(2)').text();
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/proyecto-2/mascotas/delete/' + id,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                recargarTablaMascotas();
                                Swal.fire({
                                    title: '¡Eliminado!',
                                    text: response.message || 'Mascota "' + nombreMascota + '" eliminada correctamente',
                                    icon: 'success',
                                    customClass: { confirmButton: 'btn btn-primary' },
                                    buttonsStyling: false
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: response.error || 'Error al eliminar la mascota',
                                    icon: 'error',
                                    customClass: { confirmButton: 'btn btn-primary' },
                                    buttonsStyling: false
                                });
                            }
                        },
                        error: function(xhr) {
                            let isHtml = xhr.responseText && xhr.responseText.trim().startsWith('<');
                            let msg = isHtml
                                ? 'Tu sesión ha expirado o hubo un error inesperado. Por favor, inicia sesión nuevamente.'
                                : 'No se pudo eliminar la mascota. Intenta de nuevo.';
                            Swal.fire({
                                title: 'Error',
                                text: msg,
                                icon: 'error',
                                customClass: { confirmButton: 'btn btn-primary' },
                                buttonsStyling: false
                            });
                        }
                    });
                }
            });
        });

        // Cambiar estado de mascota vía AJAX (ahora con switch)
        $('#tablaMascotas').on('change', '.cambiar-estado-mascota', function() {
            var $switch = $(this);
            var $label = $switch.closest('.form-switch').find('.form-check-label');
            var id = $switch.data('id');
            var nuevoEstado = $switch.is(':checked') ? 'activo' : 'inactivo';
            $switch.prop('disabled', true);

            $.ajax({
                url: '/proyecto-2/mascotas/cambiarEstado/' + id,
                type: 'POST',
                data: { estado: nuevoEstado },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Actualiza solo el switch y la etiqueta
                        if (nuevoEstado === 'activo') {
                            $switch.removeClass('switch-inactivo');
                            $switch.prop('checked', true);
                            $label.text('Activo');
                        } else {
                            $switch.addClass('switch-inactivo');
                            $switch.prop('checked', false);
                            $label.text('Inactivo');
                        }
                        Swal.fire({
                            title: '¡Actualizado!',
                            text: response.message || 'Estado actualizado correctamente',
                            icon: 'success',
                            customClass: { confirmButton: 'btn btn-primary' },
                            buttonsStyling: false
                        });
                    } else {
                        // Revertir el cambio visual si falla
                        $switch.prop('checked', !$switch.is(':checked'));
                        Swal.fire({
                            title: 'Error',
                            text: response.error || 'No se pudo actualizar el estado',
                            icon: 'error',
                            customClass: { confirmButton: 'btn btn-primary' },
                            buttonsStyling: false
                        });
                    }
                },
                error: function() {
                    $switch.prop('checked', !$switch.is(':checked'));
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo actualizar el estado. Intenta de nuevo.',
                        icon: 'error',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    });
                },
                complete: function() {
                    $switch.prop('disabled', false);
                }
            });
        });

        // Lógica para aplicar filtros avanzados en mascotas
        document.getElementById('aplicarFiltrosMascotasPHP').addEventListener('click', function() {
            const estado = document.getElementById('filtroEstadoMascota').value;
            const especie = document.getElementById('filtroEspecieMascota').value;
            const dueno = document.getElementById('filtroDuenoMascota').value;
            // Por ahora solo cierra el modal
            var modal = bootstrap.Modal.getInstance(document.getElementById('modalFiltrosMascotasPHP'));
            if (modal) modal.hide();
            // Aquí puedes agregar la lógica para filtrar la tabla según los valores seleccionados
        });

        var table = $('#tablaMascotas').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            responsive: false,
            scrollCollapse: true,
            paging: false,
            info: false,
            searching: false,
            dom: 't',
            order: [[0, 'asc']]
        });

        function formatMascota(rowData) {
            var id = $(rowData[0]).text() || rowData[0];
            return `
                <div style=\"padding:0.5rem 1rem;\">
                    <div class=\"btn-group\" role=\"group\">
                        <?php if ($puedeEditarCualquiera || $puedeEditarPropias): ?>
                        <button class=\"btn btn-sm btn-info me-1 btnEditarMascota\" data-id=\"${id}\"><i class=\"fas fa-edit\"></i> Editar</button>
                        <?php endif; ?>
                        <?php if ($puedeEliminar): ?>
                        <button class=\"btn btn-sm btn-danger btnEliminarMascota\" data-id=\"${id}\"><i class=\"fas fa-trash-alt\"></i> Eliminar</button>
                        <?php endif; ?>
                    </div>
                </div>
            `;
        }
    });
}
</script>

<?php
function calcularEdad($fechaNacimiento) {
    $nacimiento = new DateTime($fechaNacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($nacimiento);
    return $edad->y;
}
?> 