<?php
spl_autoload_register(function ($class) {
    // Directorios donde buscar las clases
    $directories = [
        'controllers/',
        'models/',
        'core/'
    ];
    
    // Buscar la clase en cada directorio
    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
?> 