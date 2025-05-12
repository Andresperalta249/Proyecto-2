<?php
// Permisos del usuario
$puedeCrear = true; // Permitimos que todos los usuarios puedan crear mascotas
$puedeEditar = in_array('editar_mascotas', $_SESSION['permissions'] ?? []);
$puedeEliminar = in_array('eliminar_mascotas', $_SESSION['permissions'] ?? []);
$esAdmin = in_array($_SESSION['user_role'] ?? 0, [1,2]); // 1: Superadmin, 2: Admin
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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <!-- <h1 class="h3 mb-0 text-gray-800">Mis Mascotas</h1> -->
    </div>

    <!-- Barra de búsqueda -->
    <form id="formBuscarMascota" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" name="nombre" placeholder="Buscar por nombre...">
        </div>
        <div class="col-md-3">
            <select class="form-select" name="especie">
                <option value="">Todas las especies</option>
                <option value="Perro">Perro</option>
                <option value="Gato">Gato</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="estado">
                <option value="">Todos los estados</option>
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary w-100">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
    </form>

    <!-- Tabla de mascotas -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaMascotas">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Especie</th>
                            <th>Tamaño</th>
                            <th>Género</th>
                            <th>Propietario</th>
                            <th>Edad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mascotas as $mascota): ?>
                        <tr>
                            <td><?= $mascota['id'] ?></td>
                            <td><?= htmlspecialchars($mascota['nombre']) ?></td>
                            <td><?= htmlspecialchars($mascota['especie']) ?></td>
                            <td><?= htmlspecialchars($mascota['tamano']) ?></td>
                            <td><?= htmlspecialchars($mascota['genero'] ?? '-') ?></td>
                            <td>
                                <?php
                                $propietario = '';
                                if (!empty($mascota['propietario_id'])) {
                                    foreach ($usuarios as $usuario) {
                                        if ($usuario['id'] == $mascota['propietario_id']) {
                                            $propietario = htmlspecialchars($usuario['nombre']);
                                            break;
                                        }
                                    }
                                }
                                echo $propietario ?: '-';
                                ?>
                            </td>
                            <td>
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
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input cambiar-estado-mascota" type="checkbox"
                                        data-id="<?= $mascota['id'] ?>"
                                        <?= $mascota['estado'] === 'activo' ? 'checked' : '' ?>>
                                    <label class="form-check-label">
                                        <?= ucfirst($mascota['estado']) ?>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <?php if ($puedeEditar): ?>
                                <button class="btn btn-sm btn-info me-1 btnEditarMascota" data-id="<?= $mascota['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php endif; ?>
                                <?php if ($puedeEliminar): ?>
                                <button class="btn btn-sm btn-danger btnEliminarMascota" data-id="<?= $mascota['id'] ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <?php endif; ?>
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
<style>
.fab-mascota {
    position: fixed;
    bottom: 32px;
    right: 32px;
    z-index: 999;
    background: #0d6efd;
    color: #fff;
    border: none;
    border-radius: 50px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    padding: 0 20px 0 16px;
    height: 56px;
    min-width: 56px;
    display: flex;
    align-items: center;
    font-size: 1.3em;
    font-weight: 500;
    transition: min-width 0.4s cubic-bezier(.4,0,.2,1), padding-right 0.4s cubic-bezier(.4,0,.2,1), border-radius 0.4s cubic-bezier(.4,0,.2,1), box-shadow 0.3s;
    overflow: hidden;
    cursor: pointer;
}
.fab-mascota .fab-text {
    opacity: 0;
    width: 0;
    margin-left: 0;
    transition: opacity 0.4s cubic-bezier(.4,0,.2,1), width 0.4s cubic-bezier(.4,0,.2,1), margin-left 0.4s cubic-bezier(.4,0,.2,1);
    white-space: nowrap;
}
.fab-mascota:hover, .fab-mascota:focus {
    min-width: 180px;
    border-radius: 50px;
    padding-right: 24px;
}
.fab-mascota:hover .fab-text, .fab-mascota:focus .fab-text {
    opacity: 1;
    width: auto;
    margin-left: 12px;
}
</style>

<button class="fab-mascota" id="btnNuevaMascotaFlotante">
    <i class="fas fa-plus"></i>
    <span class="fab-text">Agregar Mascota</span>
</button>

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
                success: function(data) {
                    $('#tablaMascotas tbody').html(data);
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
            var id = $(this).data('id');
            var nuevoEstado = $(this).is(':checked') ? 'activo' : 'inactivo';
            $.ajax({
                url: '/proyecto-2/mascotas/cambiarEstado/' + id,
                type: 'POST',
                data: { estado: nuevoEstado },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        recargarTablaMascotas();
                        Swal.fire({
                            title: '¡Actualizado!',
                            text: response.message || 'Estado actualizado correctamente',
                            icon: 'success',
                            customClass: { confirmButton: 'btn btn-primary' },
                            buttonsStyling: false
                        });
                    } else {
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
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo actualizar el estado. Intenta de nuevo.',
                        icon: 'error',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    });
                }
            });
        });
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