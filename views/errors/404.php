<?php
$title = 'Página no encontrada';
$content = '
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="error-page">
                <h1 class="display-1">404</h1>
                <h2 class="mb-4">Página no encontrada</h2>
                <p class="text-muted mb-4">Lo sentimos, la página que estás buscando no existe o ha sido movida.</p>
                <a href="' . BASE_URL . '" class="btn btn-primary">
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