<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' - ' . APP_NAME : APP_NAME ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
    
    <?php if (isset($extra_css)): ?>
        <?= $extra_css ?>
    <?php endif; ?>

    <style>
        /* Estilos adicionales para el dashboard */
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .bg-primary, .bg-success, .bg-warning, .bg-info {
            background: linear-gradient(45deg, var(--bs-primary) 0%, var(--bs-primary-rgb) 100%);
        }
        .bg-success {
            background: linear-gradient(45deg, var(--bs-success) 0%, var(--bs-success-rgb) 100%) !important;
        }
        .bg-warning {
            background: linear-gradient(45deg, var(--bs-warning) 0%, var(--bs-warning-rgb) 100%) !important;
        }
        .bg-info {
            background: linear-gradient(45deg, var(--bs-info) 0%, var(--bs-info-rgb) 100%) !important;
        }
        .card-body h2 {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .list-group-item {
            border-left: 4px solid transparent;
        }
        .list-group-item:hover {
            border-left-color: var(--bs-primary);
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-brand">
                <h4 class="mb-0"><?= APP_NAME ?></h4>
            </div>
            
            <div class="sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    </li>
                    
                    <?php if (verificarPermiso('gestionar_mascotas') || verificarPermiso('ver_mascotas')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/mascotas">
                            <i class="fas fa-paw"></i>
                            Mascotas
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (verificarPermiso('ver_dispositivos') || verificarPermiso('ver_todos_dispositivo')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/dispositivos">
                            <i class="fas fa-microchip"></i>
                            Dispositivos
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (verificarPermiso('ver_alertas')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/alertas">
                            <i class="fas fa-bell"></i>
                            Alertas
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (verificarPermiso('ver_monitor')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/monitor">
                            <i class="fas fa-desktop"></i>
                            Monitor en Vivo
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (
                        verificarPermiso('ver_usuarios') ||
                        verificarPermiso('crear_usuarios') ||
                        verificarPermiso('editar_usuarios') ||
                        verificarPermiso('eliminar_usuarios') ||
                        verificarPermiso('cambiar_estado_usuarios')
                    ): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/usuarios">
                            <i class="fas fa-users"></i>
                            Usuarios
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (
                        verificarPermiso('ver_roles') ||
                        verificarPermiso('crear_roles') ||
                        verificarPermiso('editar_roles') ||
                        verificarPermiso('eliminar_roles')
                    ): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/roles">
                            <i class="fas fa-user-tag"></i>
                            Roles y Permisos
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (verificarPermiso('ver_reportes')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/reportes">
                            <i class="fas fa-chart-bar"></i>
                            Reportes
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (verificarPermiso('ver_configuracion')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/configuracion">
                            <i class="fas fa-cog"></i>
                            Configuración
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <nav class="topbar">
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-link d-md-none" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="d-flex align-items-center">
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i>
                                <?= $_SESSION['user_name'] ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?= APP_URL ?>/perfil">
                                        <i class="fas fa-user-cog"></i>
                                        Mi Perfil
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?= APP_URL ?>/auth/logout">
                                        <i class="fas fa-sign-out-alt"></i>
                                        Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Page Content -->
            <div class="container-fluid">
                <?php if (isset($title)): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
                    <?php if (isset($header_buttons)): ?>
                        <?= $header_buttons ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?= $content ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Auth Content -->
        <?= $content ?>
    <?php endif; ?>
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= APP_URL ?>/assets/js/main.js"></script>
    
    <?php if (isset($extra_js)): ?>
        <?= $extra_js ?>
    <?php endif; ?>
    
    <script>
        // Toggle Sidebar
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.main-content').classList.toggle('active');
        });

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
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1500);
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
</body>
</html> 