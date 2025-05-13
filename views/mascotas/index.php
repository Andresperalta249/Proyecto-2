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
    <link href="http://localhost/proyecto-2/assets/css/app.css" rel="stylesheet">

    <h1 class="mb-4">Mis Mascotas</h1>
    <!-- Tabla de mascotas -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
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
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
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
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mascotas as $mascota): ?>
                        <tr>
                            <td class="id-azul"><?= $mascota['id'] ?></td>
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
                            <td>
                                <?php if ($puedeEditar): ?>
                                <button class="btn-accion btn-info btnEditarMascota" data-id="<?= $mascota['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php endif; ?>
                                <?php if ($puedeEliminar): ?>
                                <button class="btn-accion btn-danger btnEliminarMascota" data-id="<?= $mascota['id'] ?>">
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
<?php if ($puedeCrear ?? true): ?>
<button class="fab-crear" id="btnNuevaMascotaFlotante">
    <i class="fas fa-plus"></i>
    <span class="fab-text">Agregar Mascota</span>
</button>
<?php endif; ?>

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