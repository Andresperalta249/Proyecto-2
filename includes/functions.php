<?php
require_once __DIR__ . '/../core/Database.php';

/**
 * Verifica si un usuario tiene un permiso específico
 * @param int $usuario_id ID del usuario
 * @param string $permiso_codigo Código del permiso a verificar
 * @return bool True si el usuario tiene el permiso, False en caso contrario
 */
function tienePermiso($usuario_id, $permiso_codigo) {
    $db = Database::getInstance();
    $sql = "SELECT COUNT(*) as tiene_permiso 
            FROM usuarios u 
            JOIN roles r ON u.rol_id = r.id_rol 
            JOIN roles_permisos rp ON r.id_rol = rp.rol_id 
            JOIN permisos p ON rp.permiso_id = p.id_permiso 
            WHERE u.id_usuario = ? AND p.codigo = ?";
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute([$usuario_id, $permiso_codigo]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row && $row['tiene_permiso'] > 0;
}

/**
 * Obtiene todos los permisos de un usuario
 * @param int $usuario_id ID del usuario
 * @return array Array con los códigos de los permisos
 */
function obtenerPermisosUsuario($usuario_id) {
    try {
        $db = Database::getInstance();
        $sql = "SELECT p.codigo 
                FROM usuarios u 
                JOIN roles r ON u.rol_id = r.id_rol 
                JOIN roles_permisos rp ON r.id_rol = rp.rol_id 
                JOIN permisos p ON rp.permiso_id = p.id_permiso 
                WHERE u.id_usuario = :usuario_id";
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute(['usuario_id' => $usuario_id]);
        $permisos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $permisos[] = $row['codigo'];
        }
        return $permisos;
    } catch (Exception $e) {
        error_log("Error al obtener permisos del usuario: " . $e->getMessage());
        return [];
    }
}

/**
 * Verifica si el usuario actual tiene un permiso específico
 * @param string $permiso_codigo Código del permiso a verificar
 * @return bool True si el usuario tiene el permiso, False en caso contrario
 */
function verificarPermiso($permiso_codigo) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    // Si es super admin (rol_id == 1), tiene todos los permisos
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1) {
        return true;
    }
    // Usar el array de permisos en sesión
    return in_array($permiso_codigo, $_SESSION['permissions'] ?? []);
} 