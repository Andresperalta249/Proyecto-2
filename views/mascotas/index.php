<?php
// Permisos del usuario
$puedeCrear = true; // Permitimos que todos los usuarios puedan crear mascotas
$puedeEditar = in_array('editar_mascotas', $_SESSION['permissions'] ?? []);
$puedeEliminar = in_array('eliminar_mascotas', $_SESSION['permissions'] ?? []);
$esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2]); // 1: Superadmin, 2: Admin

// Determinar el título según permisos
$tituloMascotas = (function_exists('verificarPermiso') && verificarPermiso('ver_todas_mascotas')) ? 'Todas las Mascotas' : 'Mis Mascotas';

// Paginación clásica
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$totalMascotas = isset(
    $totalMascotas) ? $totalMascotas : count($mascotas);
$totalPages = ceil($totalMascotas / $perPage);
$start = ($page - 1) * $perPage;
$mascotasPagina = array_slice($mascotas, $start, $perPage);
?>
<!-- Page Content -->
<div class="container-fluid">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <!-- Optimización de carga de recursos -->
            <link rel="preload" href="https://code.jquery.com/jquery-3.7.1.min.js" as="script">
            <link rel="preload" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" as="style">
            <link rel="preload" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" as="style">
            
            <!-- CSS -->
            <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <link href="http://localhost/proyecto-2/assets/css/app.css" rel="stylesheet">

            <!-- Scripts -->
            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

            <!-- Input de búsqueda moderno -->
            <div class="mb-3">
                <input type="text" class="form-control" id="inputBusquedaMascota" placeholder="Buscar por nombre, especie, propietario o estado...">
            </div>
            <!-- Tabla de mascotas optimizada sin columna de acciones -->
            <div class="table-responsive p-0" style="overflow-y:unset; max-height:none;">
                <table class="tabla-app table table-hover align-middle" id="tablaMascotas" style="min-width:1000px;">
                    <thead>
                        <tr>
                            <th style="width: 48px;">ID</th>
                            <th style="width: 120px;">Nombre</th>
                            <th style="width: 90px;">Especie</th>
                            <th style="width: 80px;">Tamaño</th>
                            <th style="width: 70px;">Género</th>
                            <th style="width: 140px;">Propietario</th>
                            <th style="width: 60px; text-align:center;">Edad</th>
                            <th style="width: 90px;">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyMascotas">
                        <?php foreach ($mascotasPagina as $mascota): ?>
                        <tr data-id="<?= $mascota['id'] ?>">
                            <td><?= $mascota['id'] ?></td>
                            <td><?= $mascota['nombre'] ?></td>
                            <td><?= $mascota['especie'] ?></td>
                            <td><?= $mascota['tamano'] ?></td>
                            <td><?= $mascota['genero'] ?: '-' ?></td>
                            <td><?= htmlspecialchars($mascota['propietario_nombre'] ?? 'Sin propietario') ?></td>
                            <td style="text-align:center;">
                                <?php
                                if ($mascota['fecha_nacimiento']) {
                                    $nacimiento = new DateTime($mascota['fecha_nacimiento']);
                                    $hoy = new DateTime();
                                    $edad = $hoy->diff($nacimiento);
                                    echo $edad->y . ' año' . ($edad->y !== 1 ? 's' : '');
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <div class='form-check form-switch d-flex align-items-center mb-0'>
                                    <input class='form-check-input cambiar-estado-mascota' type='checkbox' data-id='<?= $mascota['id'] ?>' <?= $mascota['estado'] === 'activo' ? 'checked' : '' ?> >
                                    <label class='form-check-label ms-2'><?= ucfirst($mascota['estado']) ?></label>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Controles de paginación -->
            <nav aria-label="Paginación de mascotas">
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
</div>

<!-- Modal de Mascota (para crear y editar) -->
<div class="modal fade" id="modalMascota" tabindex="-1" aria-labelledby="modalMascotaLabel" aria-hidden="true" data-bs-backdrop="false">
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

<style>
/* Optimizaciones CSS */
.table-responsive {
    max-height: none !important;
    min-height: 200px;
    overflow-y: unset !important;
    contain: content;
}
.tabla-app th, .tabla-app td {
    padding: 0.45rem 0.7rem;
    font-size: 0.98rem;
    vertical-align: middle;
}
.tabla-app th {
    background: #f8fafc;
    font-weight: 600;
    border-bottom: 1.5px solid #e3e6f0;
}
.tabla-app tr {
    border-bottom: 1px solid #f1f1f1;
}
.tabla-app tr:hover {
    background: #f1f5fa;
    cursor: pointer;
}

/* Optimización de animaciones */
.fab-crear {
    will-change: transform;
    transform: translateZ(0);
}

/* Optimización de fuentes */
@font-face {
    font-display: swap;
}

/* Cuadro de búsqueda moderno */
.busqueda-moderna {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    width: 100%;
    margin-bottom: 1.5rem;
}
.busqueda-moderna .input-group {
    position: relative;
    width: 60%;
    min-width: 220px;
    max-width: 600px;
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border: 1px solid #e3e6f0;
    transition: box-shadow 0.2s;
    overflow: hidden;
}
.busqueda-moderna .input-group:focus-within {
    box-shadow: 0 4px 16px rgba(0,123,255,0.10);
    border-color: #007bff;
}
.busqueda-moderna .form-control {
    border: none;
    box-shadow: none;
    background: transparent;
    font-size: 1rem;
    padding: 0.5rem 0.5rem 0.5rem 2.2rem;
    width: 100%;
    border-radius: 1.5rem 0 0 1.5rem;
}
.busqueda-moderna .form-control:focus {
    outline: none;
    box-shadow: none;
}
.busqueda-moderna .input-group .input-icon {
    position: absolute;
    left: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    color: #b0b3b8;
    font-size: 1.1rem;
    pointer-events: none;
}
.busqueda-moderna .btn-buscar {
    background: #2563eb;
    color: #fff;
    border: none;
    border-radius: 0 1.5rem 1.5rem 0;
    padding: 0.5rem 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    transition: background 0.2s;
    box-shadow: 0 2px 8px rgba(30,64,175,0.08);
    margin-left: 0;
}
.busqueda-moderna .btn-buscar:hover {
    background: #1e40af;
}
@media (max-width: 900px) {
    .busqueda-moderna .input-group {
        width: 100%;
        max-width: 100%;
    }
}
@media (max-width: 600px) {
    .busqueda-moderna {
        flex-direction: column;
        align-items: stretch;
        gap: 0.7rem;
        margin-bottom: 1rem;
    }
    .busqueda-moderna .input-group {
        width: 100%;
        max-width: 100%;
    }
    .busqueda-moderna .btn-buscar {
        width: 100%;
        border-radius: 1.5rem;
        margin-left: 0;
    }
}
</style>

<script>
$(document).ready(function() {
    let searchTimeout;
    $('#inputBusquedaMascota').on('input', function() {
        clearTimeout(searchTimeout);
        const valor = $(this).val().trim();
        if (valor.length < 2 && valor.length !== 0) return; // Solo buscar con 2+ letras o vacío
        searchTimeout = setTimeout(() => {
            $.ajax({
                url: '/proyecto-2/mascotas/buscar',
                method: 'GET',
                data: { termino: valor },
                success: function(response) {
                    let filas = '';
                    if (response.mascotas && response.mascotas.length > 0) {
                        response.mascotas.forEach(function(mascota) {
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
                            filas += `<tr data-id="${mascota.id}">
                                <td>${mascota.id}</td>
                                <td>${mascota.nombre}</td>
                                <td>${mascota.especie}</td>
                                <td>${mascota.tamano}</td>
                                <td>${mascota.genero || '-'}</td>
                                <td>${mascota.propietario_nombre || 'Sin propietario'}</td>
                                <td style='text-align:center;'>${edad}</td>
                                <td><div class='form-check form-switch d-flex align-items-center mb-0'>
                                    <input class='form-check-input cambiar-estado-mascota' type='checkbox' data-id='${mascota.id}' ${(mascota.estado === 'activo') ? 'checked' : ''} >
                                    <label class='form-check-label ms-2'>${mascota.estado.charAt(0).toUpperCase() + mascota.estado.slice(1)}</label>
                                </div></td>
                            </tr>`;
                        });
                    } else {
                        filas = `<tr><td colspan='8' class='text-center text-muted py-4'>No hay mascotas registradas.</td></tr>`;
                    }
                    $('#tablaMascotas tbody').html(filas);
                },
                error: function(xhr) {
                    $('#tablaMascotas tbody').html(`<tr><td colspan='8' class='text-center text-danger py-4'>Error al buscar mascotas.</td></tr>`);
                }
            });
        }, 300);
    });

    let filaAccion = null;
    let botonesAccion = `<div class=\"btn-group\" role=\"group\">
        <a class=\"btn-accion btn-primary btnMonitorMascota\" title=\"Monitor en vivo\" data-bs-toggle=\"tooltip\"><i class=\"fas fa-chart-line\"></i></a>
        <button class=\"btn-accion btn-info btnEditarMascota\" title=\"Editar\" data-bs-toggle=\"tooltip\"><i class=\"fas fa-edit\"></i></button>
        <button class=\"btn-accion btn-danger btnEliminarMascota\" title=\"Eliminar\" data-bs-toggle=\"tooltip\"><i class=\"fas fa-trash-alt\"></i></button>
    </div>`;
    $('#tablaMascotas tbody').on('click', 'tr', function(e) {
        // Evitar que se dispare al hacer clic en los botones
        if ($(e.target).closest('.btn-group, button').length) return;
        if (filaAccion) filaAccion.remove();
        if ($(this).hasClass('accion-activa')) {
            $(this).removeClass('accion-activa');
            filaAccion = null;
            return;
        }
        $('#tablaMascotas tbody tr').removeClass('accion-activa');
        $(this).addClass('accion-activa');
        let colspan = $(this).children('td').length;
        filaAccion = $(`<tr class='fila-acciones'><td colspan='${colspan}' style='padding-left: 0.7rem;'><div style='display: flex; align-items: center;'>${botonesAccion}</div></td></tr>`);
        $(this).after(filaAccion);
    });
    // Evento para el botón de monitor en vivo
    $(document).on('click', '.btnMonitorMascota', function(e) {
        e.stopPropagation();
        const id = $(this).closest('tr').prev().data('id');
        // Consultar si la mascota tiene dispositivo IoT asignado
        $.ajax({
            url: `/proyecto-2/mascotas/get/${id}`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.success && response.mascota) {
                    const dispositivos = Array.isArray(response.mascota.dispositivos) ? response.mascota.dispositivos : [];
                    if (dispositivos.length > 0) {
                        window.location.href = `/proyecto-2/monitor/device/${dispositivos[0].id}`;
                    } else {
                        Swal.fire('Sin dispositivo IoT', 'Esta mascota no tiene un dispositivo IoT asignado.', 'info');
                    }
                } else {
                    Swal.fire('Error', (response && response.error) || 'No se pudo obtener la información de la mascota.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo obtener la información de la mascota.', 'error');
            }
        });
    });
    // --- EDICIÓN DE MASCOTA ---
    $(document).on('click', '.btnEditarMascota', function(e) {
        e.stopPropagation();
        let id = $(this).closest('tr').prev().data('id');
        if (!id) {
            id = $(this).closest('tr').data('id');
        }
        $('#modalMascotaLabel').text('Editar Mascota');
        // Limpiar el formulario antes de llenarlo
        $('#formMascota')[0].reset();
        $('#formMascota input[name=id]').val(id);
        // Cargar los datos de la mascota en el formulario
        $.ajax({
            url: `/proyecto-2/mascotas/get/${id}`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.success && response.mascota) {
                    const mascota = response.mascota;
                    $('#formMascota input[name=nombre]').val(mascota.nombre || '');
                    $('#formMascota select[name=especie]').val((mascota.especie || '').toLowerCase());
                    // Normaliza el valor de tamaño para que coincida con el select
                    function normalizaTamano(valor) {
                        if (!valor) return '';
                        valor = valor.toLowerCase();
                        if (valor.includes('peque')) return 'Pequeño';
                        if (valor.includes('med')) return 'Mediano';
                        if (valor.includes('gran')) return 'Grande';
                        return valor.charAt(0).toUpperCase() + valor.slice(1);
                    }
                    $('#formMascota select[name=tamano]').val(normalizaTamano(mascota.tamano));
                    $('#formMascota input[name=fecha_nacimiento]').val(mascota.fecha_nacimiento || '');
                    $('#formMascota select[name=genero]').val(mascota.genero || '');
                    $('#formMascota select[name=propietario_id]').val(mascota.propietario_id || '');
                    $('#formMascota select[name=estado]').val(mascota.estado || '');
                } else {
                    Swal.fire('Error', (response && response.error) || 'Error al cargar los datos de la mascota', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo cargar los datos de la mascota. Intenta de nuevo.', 'error');
            }
        });
        var modal = new bootstrap.Modal(document.getElementById('modalMascota'));
        modal.show();
    });
    // --- ELIMINACIÓN DE MASCOTA ---
    $(document).on('click', '.btnEliminarMascota', function(e) {
        e.stopPropagation();
        let id = $(this).closest('tr').prev().data('id');
        if (!id) {
            id = $(this).closest('tr').data('id');
        }
        // Obtener información de la mascota y dispositivos asociados
        $.ajax({
            url: `/proyecto-2/mascotas/get/${id}`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.success && response.mascota) {
                    const dispositivos = Array.isArray(response.mascota.dispositivos) ? response.mascota.dispositivos : [];
                    let mensaje = '';
                    if (dispositivos.length > 0) {
                        mensaje = 'Esta mascota tiene dispositivos IoT asociados.<br>Si la eliminas, el/los dispositivo(s) quedarán libres y <b>se eliminarán todos los datos históricos asociados</b>.<br><b>¿Deseas continuar?</b>';
                    } else {
                        mensaje = '¿Seguro que deseas eliminar esta mascota? Esta acción no se puede deshacer.';
                    }
                    Swal.fire({
                        title: 'Eliminar Mascota',
                        html: mensaje,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            eliminarMascota(id);
                        }
                    });
                } else {
                    Swal.fire('Error', (response && response.error) || 'No se pudo obtener la información de la mascota.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo obtener la información de la mascota.', 'error');
            }
        });
    });

    function eliminarMascota(id) {
        $.ajax({
            url: `/proyecto-2/mascotas/delete/${id}`,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: response.message || 'Mascota eliminada correctamente',
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', (response && response.error) || 'No se pudo eliminar la mascota.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo eliminar la mascota. Intenta de nuevo.', 'error');
            }
        });
    }

    // --- SUBMIT FORMULARIO MASCOTA ---
    $(document).on('submit', '#formMascota', function(e) {
        e.preventDefault();
        var form = $(this);
        var datos = form.serialize();
        var id = form.find('input[name=id]').val().trim();
        // Solo usar editar si el id es un número válido
        var url = (id && !isNaN(id) && Number(id) > 0) ? '/proyecto-2/mascotas/edit/' + id : '/proyecto-2/mascotas/create';
        $.ajax({
            url: url,
            type: 'POST',
            data: datos,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: '¡Guardado!',
                        text: response.message || 'Mascota actualizada correctamente',
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', response.error || 'Error al guardar la mascota', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo guardar la mascota. Intenta de nuevo.', 'error');
            }
        });
    });

    // Evento para el botón flotante de agregar mascota
    $(document).on('click', '#btnNuevaMascotaFlotante', function() {
        $('#formMascota')[0].reset();
        $('#modalMascotaLabel').text('Nueva Mascota');
        $('#formMascota input[name=id]').val('');
        $('#formMascota input[type=text], #formMascota input[type=date]').val('');
        $('#formMascota select').val('');
        $('#formMascota').attr('autocomplete', 'off');
        var modal = new bootstrap.Modal(document.getElementById('modalMascota'));
        modal.show();
    });

    // --- CAMBIO DE ESTADO DE MASCOTA ---
    $(document).on('change', '.cambiar-estado-mascota', function() {
        const id = $(this).data('id');
        const estado = $(this).is(':checked') ? 'activo' : 'inactivo';
        const $checkbox = $(this);
        const $label = $checkbox.siblings('.form-check-label');
        $.ajax({
            url: `/proyecto-2/mascotas/cambiarEstado/${id}`,
            type: 'POST',
            data: { estado },
            dataType: 'json',
            beforeSend: function() {
                $checkbox.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    $label.text(estado.charAt(0).toUpperCase() + estado.slice(1));
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
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'No se pudo cambiar el estado. Intenta de nuevo.',
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
});
</script> 