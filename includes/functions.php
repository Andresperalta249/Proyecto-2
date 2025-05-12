<?php
require_once __DIR__ . '/../config/database.php';

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
            JOIN roles r ON u.rol_id = r.id 
            JOIN roles_permisos rp ON r.id = rp.rol_id 
            JOIN permisos p ON rp.permiso_id = p.id 
            WHERE u.id = ? AND p.codigo = ?";
    $stmt = $db->prepare($sql);
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
    $db = Database::getInstance();
    $sql = "SELECT p.codigo 
            FROM usuarios u 
            JOIN roles r ON u.rol_id = r.id 
            JOIN roles_permisos rp ON r.id = rp.rol_id 
            JOIN permisos p ON rp.permiso_id = p.id 
            WHERE u.id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$usuario_id]);
    $permisos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $permisos[] = $row['codigo'];
    }
    return $permisos;
}

/**
 * Verifica si el usuario actual tiene un permiso específico
 * @param string $permiso_codigo Código del permiso a verificar
 * @return bool True si el usuario tiene el permiso, False en caso contrario
 */
function verificarPermiso($permiso_codigo) {
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }
    return tienePermiso($_SESSION['usuario_id'], $permiso_codigo);
} 