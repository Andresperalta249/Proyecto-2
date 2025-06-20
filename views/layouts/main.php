<?php
$content = $GLOBALS['content'] ?? '';
$title = $GLOBALS['title'] ?? '';
$loginError = $GLOBALS['loginError'] ?? '';
$menuActivo = $GLOBALS['menuActivo'] ?? null;

// Log para depuración del layout
$logMsg = '['.date('Y-m-d H:i:s').'] Layout: content="' . (empty($content) ? 'VACIO' : 'LLENO') . '", ruta="' . ($_SERVER['REQUEST_URI'] ?? '') . '", usuario="' . (isset($_SESSION['user_id']) ? 'AUTENTICADO' : 'NO AUTENTICADO') . "\n";
file_put_contents(__DIR__ . '/../../logs/error.log', $logMsg, FILE_APPEND);
?>
<?php require_once __DIR__ . '/../../includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Sistema de Monitoreo'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
    
    <!-- Google Fonts - Roboto (o Open Sans si prefieres) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- jQuery y Select2 cargados primero para evitar conflictos -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Chart.js -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
    <!-- Chart.js DataLabels -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script> -->
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    
    <!-- Base CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/base/variables.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/base/reset.css">
    
    <!-- Componentes CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/components/buttons.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/components/tables.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/components/forms.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/components/typography.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/components/accordion.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/components/timeline.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/components/tabs-pills.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/components/pagination.css">
    
    <!-- Layout CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/layout/sidebar-modern.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/layout/modals.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/layout/header.css">
    
    <!-- FAB CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/components/fab.css">

    <!-- Páginas CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/pages/dashboard.css">
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/pages/<?php echo $page_css; ?>.css">
    <?php endif; ?>
    
    <!-- CSS Extra -->
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Leaflet CSS para mapas -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Leaflet JavaScript para mapas -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Daterangepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <!-- Daterangepicker JS y moment.js -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
</head>
<body>
<?php
if (!isset($rolNombre)) {
    $rolNombre = $_SESSION['rol_nombre'] ?? 'Usuario';
}
if (!isset($badgeColor)) {
    switch (strtolower($rolNombre)) {
        case 'superadministrador': $badgeColor = 'primary'; break;
        case 'administrador': $badgeColor = 'success'; break;
        case 'usuario': $badgeColor = 'info'; break;
        default: $badgeColor = 'secondary'; break;
    }
}
if (!isset($content)) {
    $content = '';
}
?>
    <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
        <!-- Layout Wrapper para flexbox -->
        <div class="layout-wrapper">
            <!-- Sidebar Moderno -->
            <nav class="sidebar" id="sidebar">
                <div class="sidebar-header d-flex align-items-center p-3">
                    <span class="sidebar-logo me-2"><i class="fas fa-dog"></i></span>
                    <span class="sidebar-title">PetMonitoring IoT</span>
                </div>
                <div class="sidebar-menu">
                    <div class="sidebar-section">
                        <a href="<?= APP_URL ?>/dashboard" class="sidebar-item<?= ($menuActivo === 'dashboard' ? ' active' : '') ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </div>
                    <div class="sidebar-section">
                        <div class="sidebar-section-title px-3 py-2 text-muted small text-uppercase">
                            <i class="fas fa-folder me-2"></i> Administración
                        </div>
                        <a href="<?= APP_URL ?>/usuarios" class="sidebar-item<?= ($menuActivo === 'usuarios' ? ' active' : '') ?>">
                            <i class="fas fa-users"></i> Usuarios
                        </a>
                        <a href="<?= APP_URL ?>/roles" class="sidebar-item<?= ($menuActivo === 'roles' ? ' active' : '') ?>">
                            <i class="fas fa-user-tag"></i> Roles y Permisos
                        </a>
                    </div>
                    <div class="sidebar-section">
                        <div class="sidebar-section-title px-3 py-2 text-muted small text-uppercase">
                            <i class="fas fa-broadcast-tower me-2"></i> Monitoreo
                        </div>
                        <a href="<?= APP_URL ?>/monitor" class="sidebar-item<?= ($menuActivo === 'monitor' ? ' active' : '') ?>">
                            <i class="fas fa-desktop"></i> Monitor
                        </a>
                        <a href="<?= APP_URL ?>/mascotas" class="sidebar-item<?= ($menuActivo === 'mascotas' ? ' active' : '') ?>">
                            <i class="fas fa-paw"></i> Mascotas
                        </a>
                        <a href="<?= APP_URL ?>/dispositivos" class="sidebar-item<?= ($menuActivo === 'dispositivos' ? ' active' : '') ?>">
                            <i class="fas fa-microchip"></i> Dispositivos
                        </a>
                        <a href="<?= APP_URL ?>/reporte/monitoreo" class="sidebar-item<?= ($menuActivo === 'reporte' ? ' active' : '') ?>">
                            <i class="fas fa-chart-line"></i> Reporte IoT
                        </a>
                    </div>
                </div>
                <div class="sidebar-footer">
                    <div class="d-flex align-items-center p-3">
                        <i class="fas fa-user-circle fs-4 me-2"></i>
                        <div>
                            <div class="fw-bold"><?= htmlspecialchars($_SESSION['user']['nombre_real'] ?? $_SESSION['user']['nombre'] ?? 'Usuario') ?></div>
                            <div class="small text-muted">
                                <?= htmlspecialchars($_SESSION['rol_nombre'] ?? $rolNombre) ?>
                            </div>
                        </div>
                    </div>
                    <a href="<?= APP_URL ?>/auth/logout" class="logout-btn">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
                    </a>
                </div>
            </nav>
            <!-- Overlay para móviles -->
            <div class="sidebar-overlay" id="sidebarOverlay"></div>
            <!-- Fin Sidebar Moderno -->
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Page Content -->
                <div class="container-fluid dashboard-compact">
                    <?php if (isset($titulo) && !(isset($_SERVER['REQUEST_URI']) && preg_match('#/monitor/device/#', $_SERVER['REQUEST_URI']))): ?>
                    <div class="mb-4">
                        <h1 class="fw-bold mb-0 title-h1"><?= htmlspecialchars($titulo) ?></h1>
                        <?php if (isset($subtitulo)): ?>
                            <div class="text-secondary small text-sm mb-1"><?= htmlspecialchars($subtitulo) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?= $content ?>
                </div>
            </div>
        </div> <!-- Fin layout-wrapper -->
    <?php else: ?>
        <!-- Auth Content -->
        <?php if (!empty($content)): ?>
            <?= $content ?>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Scripts principales al final para asegurar disponibilidad -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

    <?php if (isset($extra_js)): ?>
        <?= $extra_js ?>
    <?php endif; ?>

    <script>
        // Sidebar colapsable y móvil mejorado
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        // Solo ejecutar el código del sidebar si los elementos existen
        if (sidebar && sidebarToggle && sidebarOverlay) {
            function openSidebarMobile() {
                sidebar.classList.add('open');
                sidebarOverlay.classList.add('open');
            }
            function closeSidebarMobile() {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('open');
            }
            
            sidebarToggle.addEventListener('click', function() {
                if (window.innerWidth <= 900) {
                    if (sidebar.classList.contains('open')) {
                        closeSidebarMobile();
                    } else {
                        openSidebarMobile();
                    }
                } else {
                    sidebar.classList.toggle('sidebar-collapsed');
                }
            });
            
            sidebarOverlay.addEventListener('click', closeSidebarMobile);
            
            window.addEventListener('resize', function() {
                if (window.innerWidth > 900) {
                    closeSidebarMobile();
                    sidebar.classList.remove('sidebar-collapsed');
                }
            });

            // Cerrar sidebar móvil al hacer clic en cualquier enlace del menú
            document.querySelectorAll('.sidebar-item').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 991) {
                        closeSidebarMobile();
                    }
                });
            });
        }

        // Función para mostrar mensajes con SweetAlert2 como modal centrado
        function showMessage(type, message) {
            Swal.fire({
                icon: type, // 'success', 'error', 'warning', 'info'
                title: type === 'success' ? '¡Éxito!' : 'Error',
                text: message,
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
        }

        // Manejar mensajes de sesión si existen
        <?php if (isset($_SESSION['message'])): ?>
            showMessage('<?= $_SESSION['message']['type'] ?>', '<?= $_SESSION['message']['text'] ?>');
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        // Manejar el envío del formulario de login
        window.handleFormSubmit = function(form, url) {
            event.preventDefault();
            
            $.ajax({
                url: url,
                type: 'POST',
                data: $(form).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.message);
                        if (response.redirect) {
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1500);
                        }
                    } else {
                        showMessage('error', response.error);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error al procesar la solicitud';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    showMessage('error', errorMessage);
                }
            });
            
            return false;
        };
    </script>
    <script src="<?= APP_URL ?>/assets/js/roles.js"></script>

    <!-- Modal Genérico para Contenido Dinámico -->
    <div class="modal fade" id="modal-generico" tabindex="-1" aria-labelledby="modal-generico-label" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <!-- El contenido se cargará aquí -->
        </div>
      </div>
    </div>
</body>
</html> 