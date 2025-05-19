<?php
// Script para poblar dispositivos y datos de sensores
require_once 'config/database.php';

function getDb() {
    static $db = null;
    if ($db === null) {
        $db = new PDO('mysql:host=localhost;dbname=iot_pets', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $db;
}

function getMascotas() {
    $db = getDb();
    return $db->query('SELECT id, usuario_id, nombre FROM mascotas')->fetchAll(PDO::FETCH_ASSOC);
}

function crearDispositivo($mascota, $index) {
    $db = getDb();
    $nombre = 'Dispositivo de ' . $mascota['nombre'];
    $mac = sprintf('00:1A:2B:%02X:%02X:%02X', rand(0,255), rand(0,255), $index);
    $tipo = 'collar';
    $identificador = 'DEV-' . strtoupper(uniqid());
    $descripcion = 'Dispositivo IoT para mascota';
    $mascota_id = $mascota['id'];
    $usuario_id = $mascota['usuario_id'];
    $estado = 'activo';
    $bateria = rand(50, 100);
    $creado_en = date('Y-m-d H:i:s');
    $ultima_conexion = $creado_en;
    $stmt = $db->prepare('INSERT INTO dispositivos (nombre, mac, tipo, identificador, descripcion, mascota_id, usuario_id, estado, bateria, creado_en, ultima_conexion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$nombre, $mac, $tipo, $identificador, $descripcion, $mascota_id, $usuario_id, $estado, $bateria, $creado_en, $ultima_conexion]);
    return $db->lastInsertId();
}

function crearDatosSensores($dispositivo_id, $cantidad = 700) {
    $db = getDb();
    $fecha_base = strtotime('-29 days');
    $stmt = $db->prepare('INSERT INTO datos_sensores (dispositivo_id, fecha, latitude, longitude, altitude, speed, bpm, temperatura, bateria, ultima_conexion, creado_en) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    for ($i = 0; $i < $cantidad; $i++) {
        $fecha = date('Y-m-d H:i:s', $fecha_base + ($i * 60 * 60)); // 1 registro por hora
        $latitude = 4.6 + (rand(-1000, 1000) / 10000);
        $longitude = -74.1 + (rand(-1000, 1000) / 10000);
        $altitude = rand(200, 300);
        $speed = rand(0, 20) + (rand(0, 99) / 100);
        $bpm = rand(60, 180);
        $temperatura = rand(370, 395) / 10;
        $bateria = rand(10, 100);
        $ultima_conexion = $fecha;
        $creado_en = $fecha;
        $stmt->execute([$dispositivo_id, $fecha, $latitude, $longitude, $altitude, $speed, $bpm, $temperatura, $bateria, $ultima_conexion, $creado_en]);
    }
}

$mascotas = getMascotas();
if (!$mascotas) {
    echo "No hay mascotas registradas.\n";
    exit;
}

foreach ($mascotas as $i => $mascota) {
    // Verificar si ya tiene dispositivo
    $db = getDb();
    $stmt = $db->prepare('SELECT id FROM dispositivos WHERE mascota_id = ?');
    $stmt->execute([$mascota['id']]);
    $dispositivo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($dispositivo) {
        $dispositivo_id = $dispositivo['id'];
    } else {
        $dispositivo_id = crearDispositivo($mascota, $i);
    }
    crearDatosSensores($dispositivo_id, 700);
    echo "Mascota {$mascota['nombre']} (ID {$mascota['id']}) - Dispositivo ID: $dispositivo_id - Datos generados.\n";
}

echo "\n¡Proceso completado!\n"; 