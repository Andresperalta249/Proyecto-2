<?php
require_once 'config/database.php';

function getDb() {
    static $db = null;
    if ($db === null) {
        $db = new PDO('mysql:host=localhost;dbname=iot_pets', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $db;
}

function generarDatosSensores($dispositivo_id, $dias = 30) {
    $db = getDb();
    $fecha_base = strtotime('-' . ($dias - 1) . ' days');
    $stmt = $db->prepare('INSERT INTO datos_sensores (dispositivo_id, fecha, latitude, longitude, altitude, speed, bpm, temperatura, bateria, ultima_conexion, creado_en) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    
    // Generar datos para cada día
    for ($dia = 0; $dia < $dias; $dia++) {
        // Generar datos para cada hora del día (0-23)
        for ($hora = 0; $hora < 24; $hora++) {
            $fecha = date('Y-m-d H:i:s', strtotime("+$dia days +$hora hours", $fecha_base));
            $latitude = 4.6 + (rand(-1000, 1000) / 10000);
            $longitude = -74.1 + (rand(-1000, 1000) / 10000);
            $altitude = rand(200, 300);
            $speed = rand(0, 20) + (rand(0, 99) / 100);
            $bpm = rand(60, 180);
            $temperatura = rand(370, 395) / 10;
            $bateria = rand(10, 100);
            $ultima_conexion = $fecha;
            $creado_en = $fecha;
            
            $stmt->execute([
                $dispositivo_id, 
                $fecha, 
                $latitude, 
                $longitude, 
                $altitude, 
                $speed, 
                $bpm, 
                $temperatura, 
                $bateria, 
                $ultima_conexion, 
                $creado_en
            ]);
        }
    }
}

// Obtener todos los dispositivos
$db = getDb();
$dispositivos = $db->query('SELECT id FROM dispositivos')->fetchAll(PDO::FETCH_ASSOC);

// Generar datos para cada dispositivo
foreach ($dispositivos as $dispositivo) {
    echo "Generando datos para dispositivo ID: " . $dispositivo['id'] . "\n";
    generarDatosSensores($dispositivo['id'], 30); // 30 días de datos
}

echo "\n¡Proceso completado! Se han generado 720 registros (24 horas × 30 días) para cada dispositivo.\n"; 