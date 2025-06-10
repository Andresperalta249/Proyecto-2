<?php
require_once 'core/Database.php';

try {
    $db = new PDO('mysql:host=localhost;dbname=iot_pets', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Verificando estructura actual...\n";

    // Verificar si la columna id_dispositivo ya existe
    $stmt = $db->query("SHOW COLUMNS FROM mascotas LIKE 'id_dispositivo'");
    $columna_existe = $stmt->rowCount() > 0;

    if (!$columna_existe) {
        echo "Aplicando cambios en la base de datos...\n";

        // 1. Modificar la tabla mascotas
        $db->exec("ALTER TABLE mascotas
                   DROP COLUMN IF EXISTS tiene_dispositivo,
                   DROP COLUMN IF EXISTS ultima_actualizacion_dispositivo,
                   DROP COLUMN IF EXISTS tiene_iot,
                   ADD COLUMN id_dispositivo INT NULL,
                   ADD FOREIGN KEY (id_dispositivo) REFERENCES dispositivos(id_dispositivo) ON DELETE SET NULL");
        
        echo "1. Tabla mascotas modificada\n";
    } else {
        echo "La columna id_dispositivo ya existe\n";
        // Eliminar la columna tiene_iot si existe
        $db->exec("ALTER TABLE mascotas DROP COLUMN IF EXISTS tiene_iot");
        echo "Columna tiene_iot eliminada\n";
    }

    // 2. Eliminar triggers existentes
    $db->exec("DROP TRIGGER IF EXISTS after_dispositivo_insert");
    $db->exec("DROP TRIGGER IF EXISTS after_dispositivo_delete");
    $db->exec("DROP TRIGGER IF EXISTS after_dispositivo_update");
    
    echo "2. Triggers antiguos eliminados\n";

    // 3. Crear nuevos triggers
    $db->exec("CREATE TRIGGER after_dispositivo_insert
               AFTER INSERT ON dispositivos
               FOR EACH ROW
               BEGIN
                   UPDATE mascotas 
                   SET id_dispositivo = NEW.id_dispositivo
                   WHERE id_mascota = NEW.mascota_id;
               END");

    $db->exec("CREATE TRIGGER after_dispositivo_delete
               AFTER DELETE ON dispositivos
               FOR EACH ROW
               BEGIN
                   UPDATE mascotas 
                   SET id_dispositivo = NULL
                   WHERE id_dispositivo = OLD.id_dispositivo;
               END");

    $db->exec("CREATE TRIGGER after_dispositivo_update
               AFTER UPDATE ON dispositivos
               FOR EACH ROW
               BEGIN
                   IF NEW.mascota_id != OLD.mascota_id THEN
                       UPDATE mascotas 
                       SET id_dispositivo = NULL
                       WHERE id_dispositivo = OLD.id_dispositivo;
                       
                       UPDATE mascotas 
                       SET id_dispositivo = NEW.id_dispositivo
                       WHERE id_mascota = NEW.mascota_id;
                   END IF;
               END");
    
    echo "3. Nuevos triggers creados\n";
    echo "Cambios aplicados exitosamente\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 