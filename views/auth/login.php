<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - PetMonitoring IoT</title>
    <link rel="stylesheet" href="/proyecto-2/assets/css/device-monitor.css">
    <link rel="stylesheet" href="/proyecto-2/assets/css/typography.css">
    <link rel="stylesheet" href="/proyecto-2/assets/css/sidebar.css">
</head>
<body>
    <!-- Login Moderno PetMonitoring IoT -->
    <div class="container d-flex align-items-center justify-content-center min-vh-100" style="background: #f6f8fc;">
        <div class="card shadow-lg border-0 rounded-4 p-4" style="max-width: 400px; width: 100%;">
            <div class="text-center mb-4">
                <img src="https://cdn-icons-png.flaticon.com/512/616/616408.png" alt="Logo mascota" style="width: 80px;">
                <h2 class="mt-3 mb-1" style="font-weight: 700; color: #0D47A1;">PetMonitoring IoT</h2>
                <p class="text-muted mb-0">¡Bienvenido! Ingresa para continuar</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center py-2 mb-3"><?php echo $error; ?></div>
            <?php endif; ?>
            <form id="loginForm" method="POST" autocomplete="off" novalidate>
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Correo electrónico" required autofocus>
                    <label for="email"><i class="fas fa-envelope me-2"></i>Correo electrónico</label>
                    <div class="invalid-feedback" id="emailError">Ingrese un correo válido</div>
                </div>
                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Contraseña</label>
                    <span class="position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer;" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </span>
                    <div class="invalid-feedback" id="passwordError">Ingrese su contraseña</div>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-2" style="font-weight:600;">Iniciar sesión</button>
                <div class="d-flex justify-content-between">
                    <a href="<?= APP_URL ?>/auth/forgot-password" class="small">¿Olvidaste tu contraseña?</a>
                    <a href="<?= APP_URL ?>/auth/register" class="small">Regístrate</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mostrar/ocultar contraseña
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // Validación en tiempo real
        const emailInput = document.getElementById('email');
        const emailError = document.getElementById('emailError');
        emailInput.addEventListener('input', function() {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!re.test(this.value)) {
                this.classList.add('is-invalid');
                emailError.style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                emailError.style.display = 'none';
            }
        });
        const passwordError = document.getElementById('passwordError');
        passwordInput.addEventListener('input', function() {
            if (!this.value) {
                this.classList.add('is-invalid');
                passwordError.style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                passwordError.style.display = 'none';
            }
        });

        // Envío por AJAX (usa la función global handleFormSubmit si existe)
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (typeof window.handleFormSubmit === 'function') {
                    window.handleFormSubmit(this, this.action || window.location.href);
                } else {
                    this.submit();
                }
            });
        }
    });
    </script>
    <style>
    body { background: #f6f8fc !important; }
    .card { border-radius: 1.5rem !important; transition: none !important; }
    .card:hover, .card:focus, .card:active { box-shadow: 0 2px 12px rgba(13,71,161,0.07) !important; transform: none !important; }
    .form-control:focus { border-color: #0D47A1; box-shadow: 0 0 0 0.2rem rgba(13,71,161,.15); }
    .btn-primary { background: #0D47A1; border: none; }
    .btn-primary:hover { background: #1565C0; }
    #togglePassword i { color: #888; }
    </style>
</body>
</html> 