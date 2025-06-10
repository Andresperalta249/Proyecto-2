<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del servidor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .error-500-container {
            padding: 2rem 0;
        }
        .error-page {
            background: white;
            padding: 3rem;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container error-500-container">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center">
                <div class="error-page">
                    <img src="https://cdn-icons-png.flaticon.com/512/5948/5948565.png" alt="Error 500" style="width:120px; margin-bottom:1.5rem; opacity:0.85;">
                    <h1 class="display-1 fw-bold" style="font-size:5rem;">500</h1>
                    <h2 class="mb-3" style="font-weight:700;">¡Ups! Error del servidor</h2>
                    <p class="text-muted mb-4" style="font-size:1.15rem;">Lo sentimos, ha ocurrido un error inesperado.<br>Por favor, intenta nuevamente más tarde.</p>
                    <a href="<?= APP_URL ?>/" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-home me-2"></i>
                        Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 