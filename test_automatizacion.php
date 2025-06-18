<?php
require_once 'core/Database.php';

try {
    $db = Database::getInstance();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Iniciando prueba de automatización ===\n\n";

    // 1. Crear una mascota de prueba
    $stmt = $db->getConnection()->prepare("INSERT INTO mascotas (nombre, especie, usuario_id) VALUES (?, ?, ?)");
    $stmt->execute(['TestPet', 'Perro', 1]);
    $mascota_id = $db->getConnection()->lastInsertId();
    echo "1. Mascota creada con ID: " . $mascota_id . "\n";

    // 2. Verificar estado inicial
    $stmt = $db->getConnection()->prepare("SELECT id_dispositivo FROM mascotas WHERE id_mascota = ?");
    $stmt->execute([$mascota_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "2. Estado inicial del dispositivo: " . ($result['id_dispositivo'] ? 'Tiene dispositivo (ID: ' . $result['id_dispositivo'] . ')' : 'Sin dispositivo') . "\n\n";

    // 3. Asignar dispositivo
    $stmt = $db->getConnection()->prepare("INSERT INTO dispositivos (nombre, mac, usuario_id, mascota_id) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Collar Test', '00:1A:2B:3C:4D:5F', 1, $mascota_id]);
    $dispositivo_id = $db->getConnection()->lastInsertId();
    echo "3. Dispositivo asignado (ID: " . $dispositivo_id . ")\n";

    // 4. Verificar estado después de asignar dispositivo
    $stmt = $db->getConnection()->prepare("SELECT m.id_dispositivo, d.nombre as dispositivo_nombre 
                         FROM mascotas m 
                         LEFT JOIN dispositivos d ON m.id_mascota = d.mascota_id 
                         WHERE m.id_mascota = ?");
    $stmt->execute([$mascota_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "4. Estado después de asignar dispositivo: " . 
         ($result['id_dispositivo'] ? 'Tiene dispositivo (ID: ' . $result['id_dispositivo'] . ', Nombre: ' . $result['dispositivo_nombre'] . ')' : 'Sin dispositivo') . "\n\n";

    // 5. Eliminar dispositivo
    $stmt = $db->getConnection()->prepare("DELETE FROM dispositivos WHERE id_dispositivo = ?");
    $stmt->execute([$dispositivo_id]);
    echo "5. Dispositivo eliminado\n";

    // 6. Verificar estado final
    $stmt = $db->getConnection()->prepare("SELECT id_dispositivo FROM mascotas WHERE id_mascota = ?");
    $stmt->execute([$mascota_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "6. Estado final: " . ($result['id_dispositivo'] ? 'Tiene dispositivo (ID: ' . $result['id_dispositivo'] . ')' : 'Sin dispositivo') . "\n\n";

    echo "=== Prueba completada ===\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Código de error: " . $e->getCode() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 