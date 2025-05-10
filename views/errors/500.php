<?php
$title = 'Error del servidor';
$content = '
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="error-page">
                <h1 class="display-1">500</h1>
                <h2 class="mb-4">Error del servidor</h2>
                <p class="text-muted mb-4">Lo sentimos, ha ocurrido un error en el servidor. Por favor, intenta nuevamente más tarde.</p>
                <a href="' . APP_URL . '/" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Volver al inicio
                </a>
            </div>
        </div>
    </div>
</div>
';

require_once 'views/layouts/main.php';
?> 