<!-- Recuperar Contraseña Moderno PetMonitoring IoT -->
<div class="container d-flex align-items-center justify-content-center min-vh-100" style="background: #f6f8fc;">
  <div class="card shadow-lg border-0 rounded-4 p-4" style="max-width: 400px; width: 100%;">
    <div class="text-center mb-4">
      <img src="https://cdn-icons-png.flaticon.com/512/616/616408.png" alt="Logo mascota" style="width: 80px;">
      <h2 class="mt-3 mb-1 fw-bold" style="color: #0D47A1;">¿Olvidaste tu contraseña?</h2>
      <p class="text-muted mb-0">Ingresa tu correo y te enviaremos instrucciones para restablecerla</p>
    </div>
    <form id="forgotForm" method="POST" autocomplete="off" novalidate>
      <div class="form-floating mb-3">
        <input type="email" class="form-control" id="email" name="email" placeholder="Correo electrónico" required autofocus>
        <label for="email"><i class="fas fa-envelope me-2"></i>Correo electrónico</label>
        <div class="invalid-feedback" id="emailError">Ingrese un correo válido</div>
      </div>
      <button type="submit" class="btn btn-primary w-100 mb-2 fw-semibold">Enviar instrucciones</button>
      <div class="d-flex justify-content-between">
        <a href="<?= APP_URL ?>/auth/login" class="small">Volver al login</a>
        <a href="<?= APP_URL ?>/auth/register" class="small">Regístrate</a>
      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
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
  // Envío por AJAX (usa la función global handleFormSubmit si existe)
  const forgotForm = document.getElementById('forgotForm');
  if (forgotForm) {
    forgotForm.addEventListener('submit', function(e) {
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
</style> 