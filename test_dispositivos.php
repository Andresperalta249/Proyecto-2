<?php
require_once 'core/Database.php';

try {
    $db = Database::getInstance();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Crear una mascota de prueba
    $stmt = $db->getConnection()->prepare("INSERT INTO mascotas (nombre, especie, usuario_id) VALUES (?, ?, ?)");
    $stmt->execute(['Rex', 'Perro', 1]);
    $mascota_id = $db->getConnection()->lastInsertId();
    echo "Mascota creada con ID: " . $mascota_id . "\n";

    // 2. Verificar estado inicial (debe ser FALSE)
    $stmt = $db->getConnection()->prepare("SELECT tiene_dispositivo FROM mascotas WHERE id_mascota = ?");
    $stmt->execute([$mascota_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Estado inicial del dispositivo: " . ($result['tiene_dispositivo'] ? 'TRUE' : 'FALSE') . "\n";

    // 3. Crear un dispositivo para la mascota
    $stmt = $db->getConnection()->prepare("INSERT INTO dispositivos (nombre, mac, usuario_id, mascota_id) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Collar Smart', '00:1A:2B:3C:4D:5E', 1, $mascota_id]);
    echo "Dispositivo creado y asignado a la mascota\n";

    // 4. Verificar que el estado se actualizó automáticamente
    $stmt = $db->getConnection()->prepare("SELECT tiene_dispositivo, ultima_actualizacion_dispositivo FROM mascotas WHERE id_mascota = ?");
    $stmt->execute([$mascota_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Estado después de asignar dispositivo: " . ($result['tiene_dispositivo'] ? 'TRUE' : 'FALSE') . "\n";
    echo "Última actualización: " . $result['ultima_actualizacion_dispositivo'] . "\n";

    // 5. Eliminar el dispositivo
    $stmt = $db->getConnection()->prepare("DELETE FROM dispositivos WHERE mascota_id = ?");
    $stmt->execute([$mascota_id]);
    echo "Dispositivo eliminado\n";

    // 6. Verificar que el estado se actualizó automáticamente
    $stmt = $db->getConnection()->prepare("SELECT tiene_dispositivo, ultima_actualizacion_dispositivo FROM mascotas WHERE id_mascota = ?");
    $stmt->execute([$mascota_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Estado después de eliminar dispositivo: " . ($result['tiene_dispositivo'] ? 'TRUE' : 'FALSE') . "\n";
    echo "Última actualización: " . $result['ultima_actualizacion_dispositivo'] . "\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 