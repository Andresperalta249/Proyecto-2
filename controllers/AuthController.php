<?php
// Validación de contraseña en PHP
if (!function_exists('validatePassword')) {
    function validatePassword($password) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[@$!%*?&])[A-Za-z\\d@$!%*?&]{8,}$/', $password);
    }
}

class AuthController extends Controller {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = $this->loadModel('User');
    }
    
    public function loginAction() {
        // Si la petición es AJAX y ya está autenticado, responder con JSON de éxito
        if (isset($_SESSION['user_id']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->jsonResponse([
                'success' => true,
                'redirect' => APP_URL . '/dashboard'
            ]);
        }
        // Si ya está autenticado y NO es AJAX, redirigir normalmente
        if (isset($_SESSION['user_id'])) {
            redirect('/dashboard');
        }
        
        require_once __DIR__ . '/../includes/functions.php'; // Aseguramos que la función global esté disponible
        $loginError = '';
        
        // Solo procesar el formulario si es una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            
            if ($email && $password) {
                $user = $this->userModel->findByEmail($email);
                
                if ($user) {
                    if (password_verify($password, $user['password'])) {
                        if ($user['estado'] === 'activo') {
                            // Establecer variables de sesión
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user'] = $user;
                            $_SESSION['user_role'] = $user['rol_id'];
                            
                            // Registrar el inicio de sesión
                            $this->userModel->logLogin($user['id']);
                            
                            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                                file_put_contents(dirname(__DIR__) . '/logs/error.log', "[".date('Y-m-d H:i:s')."] loginAction: Respondiendo JSON de éxito AJAX\n", FILE_APPEND);
                                $this->jsonResponse([
                                    'success' => true,
                                    'message' => '¡Bienvenido ' . $user['nombre'] . '!',
                                    'redirect' => APP_URL . '/dashboard'
                                ]);
                            } else {
                                file_put_contents(dirname(__DIR__) . '/logs/error.log', "[".date('Y-m-d H:i:s')."] loginAction: Redirigiendo a dashboard (no AJAX)\n", FILE_APPEND);
                                redirect('/dashboard');
                            }
                        } else {
                            $loginError = 'Tu cuenta está inactiva. Contacta al administrador.';
                        }
                    } else {
                        $loginError = 'La contraseña ingresada es incorrecta.';
                    }
                } else {
                    $loginError = 'El correo ingresado no corresponde a ningún usuario registrado.';
                }
            } else {
                $loginError = 'Por favor, completa todos los campos correctamente.';
            }
            
            // Si hay error y es una solicitud AJAX
            if ($loginError && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                file_put_contents(dirname(__DIR__) . '/logs/error.log', "[".date('Y-m-d H:i:s')."] loginAction: Respondiendo JSON de error AJAX: $loginError\n", FILE_APPEND);
                $this->jsonResponse([
                    'success' => false,
                    'error' => $loginError
                ], 400);
            }
        }
        
        // Mostrar vista de login
        $title = 'Iniciar Sesión';
        $content = '
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="card shadow-lg border-0 rounded-lg mt-5">
                        <div class="card-header">
                            <h3 class="text-center font-weight-light my-4">Iniciar Sesión</h3>
                        </div>
                        <div class="card-body">
                            <form id="loginForm" method="POST" action="' . APP_URL . '/auth/login">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="email" name="email" type="email" placeholder="name@example.com" required />
                                    <label for="email">Correo electrónico</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="password" name="password" type="password" placeholder="Contraseña" required />
                                    <label for="password">Contraseña</label>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                    <a class="small" href="' . APP_URL . '/auth/forgot-password">¿Olvidaste tu contraseña?</a>
                                    <button class="btn btn-primary" type="submit">Iniciar Sesión</button>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer text-center py-3">
                            <div class="small">
                                <a href="' . APP_URL . '/auth/register">¿Necesitas una cuenta? ¡Regístrate!</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
        document.getElementById("loginForm").addEventListener("submit", function(e) {
            if (!e.submitter || e.submitter.type === "submit") {
                e.preventDefault();
                handleFormSubmit(this, "' . APP_URL . '/auth/login");
            }
        });
        </script>';
        
        require_once 'views/layouts/main.php';
    }
    
    public function registerAction() {
        file_put_contents(dirname(__DIR__) . '/logs/error.log', "[".date('Y-m-d H:i:s')."] Intento de acceso a registerAction\n", FILE_APPEND);
        // Si ya está autenticado, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            file_put_contents(dirname(__DIR__) . '/logs/error.log', "[".date('Y-m-d H:i:s')."] Usuario ya autenticado, redirigiendo.\n", FILE_APPEND);
            redirect('dashboard');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            file_put_contents(dirname(__DIR__) . '/logs/error.log', "[".date('Y-m-d H:i:s')."] POST recibido en registerAction. Datos: ".json_encode($_POST)."\n", FILE_APPEND);
            $data = $this->validateRequest(['nombre', 'email', 'password', 'confirm_password', 'telefono', 'direccion']);
            file_put_contents(dirname(__DIR__) . '/logs/error.log', "[".date('Y-m-d H:i:s')."] Resultado de validateRequest: ".json_encode($data)."\n", FILE_APPEND);
            if ($data) {
                // Validar contraseña
                if ($data['password'] !== $data['confirm_password']) {
                    file_put_contents(dirname(__DIR__) . '/logs/error.log', "[".date('Y-m-d H:i:s')."] Contraseñas no coinciden.\n", FILE_APPEND);
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'Las contraseñas no coinciden'
                    ], 400);
                }
                if (!validatePassword($data['password'])) {
                    file_put_contents(dirname(__DIR__) . '/logs/error.log', "[".date('Y-m-d H:i:s')."] Contraseña no cumple requisitos.\n", FILE_APPEND);
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'La contraseña no cumple con los requisitos de seguridad'
                    ], 400);
                }
                // Verificar si el email ya existe
                if ($this->userModel->findByEmail($data['email'])) {
                    file_put_contents(dirname(__DIR__) . '/logs/error.log', "[".date('Y-m-d H:i:s')."] Email ya registrado: ".$data['email']."\n", FILE_APPEND);
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'El correo electrónico ya está registrado'
                    ], 400);
                }
                // Crear usuario
                $userData = [
                    'nombre' => $data['nombre'],
                    'email' => $data['email'],
                    'password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]),
                    'telefono' => $data['telefono'],
                    'direccion' => $data['direccion'],
                    'rol_id' => 3, // Rol de usuario normal
                    'estado' => 'activo',
                    'creado_en' => date('Y-m-d H:i:s')
                ];
                file_put_contents(dirname(__DIR__) . '/logs/error.log', "[".date('Y-m-d H:i:s')."] Intentando crear usuario: ".json_encode($userData)."\n", FILE_APPEND);
                if ($this->userModel->create($userData)) {
                    file_put_contents(dirname(__DIR__) . '/logs/error.log', "[".date('Y-m-d H:i:s')."] Usuario creado exitosamente.\n", FILE_APPEND);
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Registro exitoso. Por favor, inicia sesión.',
                        'redirect' => APP_URL . '/auth/login'
                    ]);
                } else {
                    $errorMsg = '[' . date('Y-m-d H:i:s') . "] Error al crear usuario:\n";
                    $errorMsg .= "Datos del usuario: " . print_r($userData, true) . "\n";
                    if (isset($this->userModel->lastError)) {
                        $errorMsg .= "Error de base de datos: " . $this->userModel->lastError . "\n";
                    }
                    if (isset($this->db->lastError)) {
                        $errorMsg .= "Error de conexión: " . $this->db->lastError . "\n";
                    }
                    $errorMsg .= "----------------------------------------\n";
                    $logPath = dirname(__DIR__) . '/logs/error.log';
                    $logDir = dirname($logPath);
                    if (!is_dir($logDir)) {
                        if (!mkdir($logDir, 0777, true)) {
                            error_log("No se pudo crear el directorio de logs: " . $logDir);
                        }
                    }
                    if (file_put_contents($logPath, $errorMsg, FILE_APPEND | LOCK_EX) === false) {
                        error_log("No se pudo escribir en el archivo de log: " . $logPath);
                        error_log("Error de PHP: " . error_get_last()['message']);
                    }
                    error_log("Error al crear usuario: " . print_r($userData, true));
                    if (isset($this->userModel->lastError)) {
                        error_log("Error de base de datos: " . $this->userModel->lastError);
                    }
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'Error al crear la cuenta. Por favor, intente nuevamente.'
                    ], 500);
                }
            } else {
                file_put_contents(dirname(__DIR__) . '/logs/error.log', "[".date('Y-m-d H:i:s')."] Datos no válidos en validateRequest.\n", FILE_APPEND);
            }
        }
        
        // Mostrar vista de registro
        $title = 'Registro';
        $content = '
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="card shadow-lg border-0 rounded-lg mt-5">
                        <div class="card-header">
                            <h3 class="text-center font-weight-light my-4">Crear Cuenta</h3>
                        </div>
                        <div class="card-body">
                            <form id="registerForm" method="POST" autocomplete="off">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <input class="form-control" id="nombre" name="nombre" type="text" placeholder="Ingrese su nombre" required minlength="3" pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ ]{3,}$" />
                                            <label for="nombre">Nombre completo</label>
                                            <div class="invalid-feedback" id="nombreError"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="email" name="email" type="email" placeholder="name@example.com" required />
                                    <label for="email">Correo electrónico</label>
                                    <div class="invalid-feedback" id="emailError"></div>
                                </div>
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="telefono" name="telefono" type="text" placeholder="Teléfono" required pattern="^[0-9]{7,15}$" />
                                    <label for="telefono">Teléfono</label>
                                    <div class="invalid-feedback" id="telefonoError"></div>
                                </div>
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="direccion" name="direccion" type="text" placeholder="Dirección" required minlength="5" />
                                    <label for="direccion">Dirección</label>
                                    <div class="invalid-feedback" id="direccionError"></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <input class="form-control" id="password" name="password" type="password" placeholder="Crear contraseña" required minlength="8" />
                                            <label for="password">Contraseña</label>
                                            <div class="invalid-feedback" id="passwordError"></div>
                                            <ul class="list-unstyled mt-2 mb-0" id="passwordChecklist">
                                                <li id="chk-length" class="text-danger">Mínimo 8 caracteres</li>
                                                <li id="chk-mayus" class="text-danger">Al menos una mayúscula</li>
                                                <li id="chk-minus" class="text-danger">Al menos una minúscula</li>
                                                <li id="chk-num" class="text-danger">Al menos un número</li>
                                                <li id="chk-especial" class="text-danger">Al menos un carácter especial</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <input class="form-control" id="confirm_password" name="confirm_password" type="password" placeholder="Confirmar contraseña" required />
                                            <label for="confirm_password">Confirmar contraseña</label>
                                            <div class="invalid-feedback" id="confirmPasswordError"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 mb-0">
                                    <div class="d-grid">
                                        <button class="btn btn-primary btn-block" id="btnRegister" type="submit" disabled>Crear Cuenta</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer text-center py-3">
                            <div class="small">
                                <a href="' . BASE_URL . 'auth/login">¿Ya tienes una cuenta? ¡Inicia sesión!</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ';
        $content .= '
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const nombre = document.getElementById("nombre");
            const email = document.getElementById("email");
            const telefono = document.getElementById("telefono");
            const direccion = document.getElementById("direccion");
            const password = document.getElementById("password");
            const confirmPassword = document.getElementById("confirm_password");
            const btnRegister = document.getElementById("btnRegister");
            const nombreError = document.getElementById("nombreError");
            const emailError = document.getElementById("emailError");
            const telefonoError = document.getElementById("telefonoError");
            const direccionError = document.getElementById("direccionError");
            const passwordError = document.getElementById("passwordError");
            const confirmPasswordError = document.getElementById("confirmPasswordError");
            const checklist = {
                length: document.getElementById("chk-length"),
                mayus: document.getElementById("chk-mayus"),
                minus: document.getElementById("chk-minus"),
                num: document.getElementById("chk-num"),
                especial: document.getElementById("chk-especial")
            };
            function validarNombre() {
                const val = nombre.value.trim();
                if (val.length < 3 || /[^A-Za-zÁÉÍÓÚáéíóúÑñ ]/.test(val)) {
                    nombre.classList.add("is-invalid");
                    nombreError.textContent = "Nombre inválido (mínimo 3 letras, solo letras y espacios)";
                    return false;
                }
                nombre.classList.remove("is-invalid");
                nombreError.textContent = "";
                return true;
            }
            function validarEmail() {
                const val = email.value.trim();
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!re.test(val)) {
                    email.classList.add("is-invalid");
                    emailError.textContent = "Ingrese un correo válido";
                    return false;
                }
                email.classList.remove("is-invalid");
                emailError.textContent = "";
                return true;
            }
            function validarTelefono() {
                const val = telefono.value.trim();
                if (!/^[0-9]{7,15}$/.test(val)) {
                    telefono.classList.add("is-invalid");
                    telefonoError.textContent = "Ingrese un teléfono válido (7-15 dígitos)";
                    return false;
                }
                telefono.classList.remove("is-invalid");
                telefonoError.textContent = "";
                return true;
            }
            function validarDireccion() {
                const val = direccion.value.trim();
                if (val.length < 5) {
                    direccion.classList.add("is-invalid");
                    direccionError.textContent = "Ingrese una dirección válida (mínimo 5 caracteres)";
                    return false;
                }
                direccion.classList.remove("is-invalid");
                direccionError.textContent = "";
                return true;
            }
            function validarPassword() {
                const val = password.value;
                let valid = true;
                if (val.length >= 8) {
                    checklist.length.classList.remove("text-danger");
                    checklist.length.classList.add("text-success");
                } else {
                    checklist.length.classList.add("text-danger");
                    checklist.length.classList.remove("text-success");
                    valid = false;
                }
                if (/[A-Z]/.test(val)) {
                    checklist.mayus.classList.remove("text-danger");
                    checklist.mayus.classList.add("text-success");
                } else {
                    checklist.mayus.classList.add("text-danger");
                    checklist.mayus.classList.remove("text-success");
                    valid = false;
                }
                if (/[a-z]/.test(val)) {
                    checklist.minus.classList.remove("text-danger");
                    checklist.minus.classList.add("text-success");
                } else {
                    checklist.minus.classList.add("text-danger");
                    checklist.minus.classList.remove("text-success");
                    valid = false;
                }
                if (/[0-9]/.test(val)) {
                    checklist.num.classList.remove("text-danger");
                    checklist.num.classList.add("text-success");
                } else {
                    checklist.num.classList.add("text-danger");
                    checklist.num.classList.remove("text-success");
                    valid = false;
                }
                if (/[@$!%*?&]/.test(val)) {
                    checklist.especial.classList.remove("text-danger");
                    checklist.especial.classList.add("text-success");
                } else {
                    checklist.especial.classList.add("text-danger");
                    checklist.especial.classList.remove("text-success");
                    valid = false;
                }
                if (!valid) {
                    password.classList.add("is-invalid");
                    passwordError.textContent = "La contraseña no cumple los requisitos";
                } else {
                    password.classList.remove("is-invalid");
                    passwordError.textContent = "";
                }
                return valid;
            }
            function validarConfirmPassword() {
                if (confirmPassword.value !== password.value || !confirmPassword.value) {
                    confirmPassword.classList.add("is-invalid");
                    confirmPasswordError.textContent = "Las contraseñas no coinciden";
                    return false;
                }
                confirmPassword.classList.remove("is-invalid");
                confirmPasswordError.textContent = "";
                return true;
            }
            function validarFormulario() {
                const v1 = validarNombre();
                const v2 = validarEmail();
                const v3 = validarTelefono();
                const v4 = validarDireccion();
                const v5 = validarPassword();
                const v6 = validarConfirmPassword();
                btnRegister.disabled = !(v1 && v2 && v3 && v4 && v5 && v6);
            }
            nombre.addEventListener("input", validarFormulario);
            email.addEventListener("input", validarFormulario);
            telefono.addEventListener("input", validarFormulario);
            direccion.addEventListener("input", validarFormulario);
            password.addEventListener("input", validarFormulario);
            confirmPassword.addEventListener("input", validarFormulario);
            document.getElementById("registerForm").addEventListener("submit", function(e) {
                e.preventDefault();
                if (btnRegister.disabled) return;
                handleFormSubmit(this, "' . BASE_URL . 'auth/register");
            });
        });
        </script>';
        
        require_once 'views/layouts/main.php';
    }
    
    public function forgotPasswordAction() {
        // Si ya está autenticado, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            redirect('dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateRequest(['email']);
            
            if ($data) {
                $user = $this->userModel->findByEmail($data['email']);
                
                if ($user) {
                    // Generar token
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                    
                    if ($this->userModel->createPasswordReset($user['id'], $token, $expires)) {
                        // Enviar correo
                        $resetLink = BASE_URL . 'auth/reset-password/' . $token;
                        $to = $user['email'];
                        $subject = 'Recuperación de contraseña';
                        $message = "Hola {$user['nombre']},\n\n";
                        $message .= "Has solicitado restablecer tu contraseña. ";
                        $message .= "Haz clic en el siguiente enlace para continuar:\n\n";
                        $message .= $resetLink . "\n\n";
                        $message .= "Este enlace expirará en 30 minutos.\n\n";
                        $message .= "Si no solicitaste este cambio, ignora este mensaje.\n\n";
                        $message .= "Saludos,\n" . SITE_NAME;
                        
                        $headers = 'From: ' . SMTP_USER . "\r\n" .
                                 'Reply-To: ' . SMTP_USER . "\r\n" .
                                 'X-Mailer: PHP/' . phpversion();
                        
                        if (mail($to, $subject, $message, $headers)) {
                            $this->jsonResponse([
                                'success' => true,
                                'message' => 'Se ha enviado un enlace de recuperación a tu correo'
                            ]);
                        } else {
                            $this->jsonResponse([
                                'success' => false,
                                'error' => 'Error al enviar el correo'
                            ], 500);
                        }
                    } else {
                        $this->jsonResponse([
                            'success' => false,
                            'error' => 'Error al generar el token de recuperación'
                        ], 500);
                    }
                } else {
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'No existe una cuenta con ese correo'
                    ], 404);
                }
            }
        }
        
        // Mostrar vista de recuperación
        $title = 'Recuperar Contraseña';
        $content = '
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="card shadow-lg border-0 rounded-lg mt-5">
                        <div class="card-header">
                            <h3 class="text-center font-weight-light my-4">Recuperar Contraseña</h3>
                        </div>
                        <div class="card-body">
                            <div class="small mb-3 text-muted">
                                Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                            </div>
                            <form id="forgotPasswordForm" onsubmit="return handleFormSubmit(this, \'' . BASE_URL . 'auth/forgot-password\')">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="email" name="email" type="email" placeholder="name@example.com" required />
                                    <label for="email">Correo electrónico</label>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                    <a class="small" href="' . BASE_URL . 'auth/login">Volver al inicio de sesión</a>
                                    <button class="btn btn-primary" type="submit">Enviar enlace</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ';
        
        require_once 'views/layouts/main.php';
    }
    
    public function resetPasswordAction($token = null) {
        // Si ya está autenticado, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            redirect('dashboard');
        }
        
        if (!$token) {
            redirect('auth/login');
        }
        
        // Verificar token
        $reset = $this->userModel->getPasswordReset($token);
        
        if (!$reset || strtotime($reset['expires']) < time()) {
            $title = 'Enlace Inválido';
            $content = '
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-5">
                        <div class="card shadow-lg border-0 rounded-lg mt-5">
                            <div class="card-header">
                                <h3 class="text-center font-weight-light my-4">Enlace Inválido</h3>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <p class="mb-4">El enlace de recuperación no es válido o ha expirado.</p>
                                    <a href="' . BASE_URL . 'auth/forgot-password" class="btn btn-primary">
                                        Solicitar nuevo enlace
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ';
            
            require_once 'views/layouts/main.php';
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateRequest(['password', 'confirm_password']);
            
            if ($data) {
                // Validar contraseña
                if ($data['password'] !== $data['confirm_password']) {
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'Las contraseñas no coinciden'
                    ], 400);
                }
                
                if (!validatePassword($data['password'])) {
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'La contraseña no cumple con los requisitos de seguridad'
                    ], 400);
                }
                
                // Actualizar contraseña
                $userData = [
                    'password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST])
                ];
                
                if ($this->userModel->update($reset['user_id'], $userData)) {
                    // Eliminar token
                    $this->userModel->deletePasswordReset($token);
                    
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Contraseña actualizada correctamente',
                        'redirect' => BASE_URL . 'auth/login'
                    ]);
                } else {
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'Error al actualizar la contraseña'
                    ], 500);
                }
            }
        }
        
        // Mostrar vista de restablecimiento
        $title = 'Restablecer Contraseña';
        $content = '
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="card shadow-lg border-0 rounded-lg mt-5">
                        <div class="card-header">
                            <h3 class="text-center font-weight-light my-4">Restablecer Contraseña</h3>
                        </div>
                        <div class="card-body">
                            <form id="resetPasswordForm" onsubmit="return handleFormSubmit(this, \'' . BASE_URL . 'auth/reset-password/' . $token . '\')">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="password" name="password" type="password" placeholder="Nueva contraseña" required />
                                    <label for="password">Nueva contraseña</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="confirm_password" name="confirm_password" type="password" placeholder="Confirmar contraseña" required />
                                    <label for="confirm_password">Confirmar contraseña</label>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                    <a class="small" href="' . BASE_URL . 'auth/login">Volver al inicio de sesión</a>
                                    <button class="btn btn-primary" type="submit">Restablecer contraseña</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ';
        
        require_once 'views/layouts/main.php';
    }
    
    public function logoutAction() {
        // Destruir sesión
        session_destroy();
        
        // Redirigir al login
        redirect('auth/login');
    }
    
    public function testdbAction() {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=iot_pets', 'root', '');
            echo '<div style="padding:2rem;font-size:1.5rem;color:green;">Conexión exitosa a la base de datos.</div>';
        } catch (PDOException $e) {
            echo '<div style="padding:2rem;font-size:1.5rem;color:red;">Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit;
    }
}
?> 