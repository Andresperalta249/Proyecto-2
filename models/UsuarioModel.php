<?php
/**
 * Modelo para la gestión de datos de usuarios.
 * Proporciona métodos para interactuar con la tabla `usuarios` en la base de datos.
 */
class UsuarioModel extends Model {
    protected $table = 'usuarios';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Obtiene un usuario por su dirección de email.
     * @param string $email El email del usuario.
     * @return array|null Los datos del usuario o null si no se encuentra.
     */
    public function getUsuarioByEmail($email) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = :email";
            $result = $this->query($sql, [':email' => $email]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Error en getUsuarioByEmail: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     * @param array $data Los datos del usuario a crear.
     * @return int|false El ID del nuevo usuario o false si falla.
     */
    public function crearUsuario($data) {
        try {
            $sql = "INSERT INTO {$this->table} (nombre, email, password, rol_id, telefono, direccion) 
                    VALUES (:nombre, :email, :password, :rol_id, :telefono, :direccion)";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':email' => $data['email'],
                ':password' => $data['password'],
                ':rol_id' => $data['rol_id'],
                ':telefono' => $data['telefono'] ?? null,
                ':direccion' => $data['direccion'] ?? null
            ]);
            
            return $this->db->getConnection()->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crearUsuario: " . $e->getMessage());
            // Devolver el mensaje de error específico de PDO
            return $e->getMessage();
        }
    }

    /**
     * Registra la hora del último inicio de sesión de un usuario.
     * @param int $id_usuario El ID del usuario.
     * @return bool True en éxito, false en fallo.
     */
    public function registrarInicioSesion($id_usuario) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET ultimo_acceso = NOW() 
                    WHERE id_usuario = :id_usuario";
            return $this->query($sql, [':id_usuario' => $id_usuario]);
        } catch (Exception $e) {
            error_log("Error en registrarInicioSesion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los datos de un usuario existente.
     * @param int $id_usuario El ID del usuario a actualizar.
     * @param array $data Un array asociativo con los campos y nuevos valores.
     * @return bool True en éxito, false en fallo.
     */
    public function update($id_usuario, $data) {
        try {
            $sql = "UPDATE {$this->table} SET ";
            $params = [];
            $updates = [];

            foreach ($data as $key => $value) {
                $updates[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }

            $sql .= implode(', ', $updates);
            $sql .= " WHERE id_usuario = :id_usuario";
            $params[':id_usuario'] = $id_usuario;

            return $this->query($sql, $params);
        } catch (Exception $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un usuario de la base de datos.
     * @param int $id_usuario El ID del usuario a eliminar.
     * @return bool True si se eliminó, false si no.
     */
    public function delete($id_usuario) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id_usuario = :id_usuario";
            $stmt = $this->db->getConnection()->prepare($sql);
            $result = $stmt->execute([':id_usuario' => $id_usuario]);
            
            // Verificar si se afectó al menos una fila
            return $result && $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error en delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca un usuario por su ID.
     * @param int $id_usuario El ID del usuario.
     * @return array|null Los datos del usuario o null si no se encuentra.
     */
    public function find($id_usuario) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id_usuario = :id_usuario";
            $result = $this->query($sql, [':id_usuario' => $id_usuario]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Error en find: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene todos los usuarios del sistema.
     * @return array Un array con todos los usuarios.
     */
    public function getAll() {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY nombre";
            return $this->query($sql);
        } catch (Exception $e) {
            error_log("Error en getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca usuarios por un término de búsqueda en nombre o email.
     * @param string $termino El término a buscar.
     * @return array Un array de usuarios que coinciden con la búsqueda.
     */
    public function buscar($termino) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE nombre LIKE :termino 
                    OR email LIKE :termino 
                    ORDER BY nombre";
            return $this->query($sql, [':termino' => "%{$termino}%"]);
        } catch (Exception $e) {
            error_log("Error en buscar: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una solicitud de reseteo de contraseña por su token.
     * @param string $token El token de reseteo.
     * @return array|null Los datos del reseteo o null si no es válido.
     */
    public function getPasswordReset($token) {
        try {
            $sql = "SELECT * FROM password_resets 
                    WHERE token = :token 
                    AND expires_at > NOW()";
            $result = $this->query($sql, [':token' => $token]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Error en getPasswordReset: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea un nuevo registro de reseteo de contraseña.
     * @param int $id_usuario ID del usuario.
     * @param string $token El token generado.
     * @param string $expiresAt La fecha de expiración.
     * @return bool True en éxito, false en fallo.
     */
    public function createPasswordReset($id_usuario, $token, $expiresAt) {
        try {
            $sql = "INSERT INTO password_resets (user_id, token, expires_at) 
                    VALUES (:user_id, :token, :expires_at)";
            return $this->query($sql, [
                ':user_id' => $id_usuario,
                ':token' => $token,
                ':expires_at' => $expiresAt
            ]);
        } catch (Exception $e) {
            error_log("Error en createPasswordReset: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un token de reseteo de contraseña una vez usado.
     * @param string $token El token a eliminar.
     * @return bool True en éxito, false en fallo.
     */
    public function deletePasswordReset($token) {
        try {
            $sql = "DELETE FROM password_resets WHERE token = :token";
            return $this->query($sql, [':token' => $token]);
        } catch (Exception $e) {
            error_log("Error en deletePasswordReset: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cuenta el número total de usuarios, opcionalmente filtrando por un término de búsqueda.
     * Usado para la paginación de DataTables.
     * @param string $search El término de búsqueda opcional.
     * @return int El número total de usuarios.
     */
    public function contarUsuarios($search = '') {
        try {
            $sql = "SELECT COUNT(u.id_usuario) as total 
                    FROM {$this->table} u 
                    JOIN roles r ON u.rol_id = r.id_rol";
            $params = [];

            if (!empty($search)) {
                $sql .= " WHERE LOWER(u.nombre) LIKE :search1 
                          OR LOWER(u.email) LIKE :search2 
                          OR LOWER(r.nombre) LIKE :search3";
                $searchValue = "%" . strtolower($search) . "%";
                $params[':search1'] = $searchValue;
                $params[':search2'] = $searchValue;
                $params[':search3'] = $searchValue;
            }

            $result = $this->query($sql, $params);
            return (int) $result[0]['total'];
        } catch (Exception $e) {
            error_log("Error en contarUsuarios: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene una lista paginada de usuarios para mostrar en DataTables.
     * @param int $start El registro inicial desde el que empezar.
     * @param int $length El número de registros a devolver.
     * @param string $search El término de búsqueda.
     * @return array La lista de usuarios.
     */
    public function obtenerUsuariosPaginados($start, $length, $search) {
        try {
            $sql = "SELECT u.id_usuario, u.nombre, u.email, u.telefono, u.direccion, r.nombre as rol_nombre, u.estado 
                    FROM {$this->table} u 
                    JOIN roles r ON u.rol_id = r.id_rol";
            $params = [];

            if (!empty($search)) {
                $sql .= " WHERE LOWER(u.nombre) LIKE :search1 
                          OR LOWER(u.email) LIKE :search2 
                          OR LOWER(r.nombre) LIKE :search3";
                $searchValue = "%" . strtolower($search) . "%";
                $params[':search1'] = $searchValue;
                $params[':search2'] = $searchValue;
                $params[':search3'] = $searchValue;
            }

            $sql .= " ORDER BY u.id_usuario DESC LIMIT " . (int)$start . ", " . (int)$length;
            
            error_log("SQL obtenerUsuariosPaginados: " . $sql);
            error_log("Params: " . print_r($params, true));
            
            return $this->query($sql, $params);
        } catch (Exception $e) {
            error_log("Error en obtenerUsuariosPaginados: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verifica si un email ya existe en la base de datos.
     * @param string $email El email a verificar.
     * @param int|null $id_actual El ID del usuario actual, para ignorarlo en la comprobación.
     * @return bool True si el email existe, false si no.
     */
    public function emailExiste($email, $id_actual = null) {
        try {
            $sql = "SELECT id_usuario FROM {$this->table} WHERE email = :email";
            $params = [':email' => $email];
            if ($id_actual) {
                $sql .= " AND id_usuario != :id_usuario";
                $params[':id_usuario'] = $id_actual;
            }
            $result = $this->query($sql, $params);
            return !empty($result);
        } catch (Exception $e) {
            error_log("Error en emailExiste: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los datos de un usuario específico.
     * @param array $data Los datos del usuario, debe incluir 'id_usuario'.
     * @return bool True si la actualización fue exitosa, false si no.
     */
    public function actualizarUsuario($data) {
        try {
            $sql = "UPDATE {$this->table} SET nombre = :nombre, email = :email, telefono = :telefono, direccion = :direccion, rol_id = :rol_id, estado = :estado";
            $params = [
                ':nombre' => $data['nombre'],
                ':email' => $data['email'],
                ':telefono' => $data['telefono'],
                ':direccion' => $data['direccion'],
                ':rol_id' => $data['rol_id'],
                ':estado' => $data['estado'],
                ':id_usuario' => $data['id_usuario']
            ];

            if (!empty($data['password'])) {
                $sql .= ", password = :password";
                $params[':password'] = $data['password'];
            }

            $sql .= " WHERE id_usuario = :id_usuario";
            
            error_log("EDITAR USUARIO - SQL: " . $sql);
            error_log("EDITAR USUARIO - PARAMS: " . print_r($params, true));

            $stmt = $this->db->getConnection()->prepare($sql);
            $result = $stmt->execute($params);
            
            // Verificar si se afectó al menos una fila
            return $result && $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error en actualizarUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene un usuario por su ID.
     * @param int $id El ID del usuario.
     * @return array|null Los datos del usuario o null.
     */
    public function obtenerUsuarioPorId($id) {
        return $this->find($id);
    }

    /**
     * Cambia el estado (activo/inactivo) de un usuario.
     * @param int $id El ID del usuario.
     * @param string $estado El nuevo estado ('activo' or 'inactivo').
     * @return bool True si se actualizó, false si no.
     */
    public function cambiarEstado($id, $estado) {
        try {
            $sql = "UPDATE {$this->table} SET estado = :estado WHERE id_usuario = :id_usuario";
            $stmt = $this->db->getConnection()->prepare($sql);
            $result = $stmt->execute([':estado' => $estado, ':id_usuario' => $id]);
            
            // Verificar si se afectó al menos una fila
            return $result && $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error en cambiarEstado: " . $e->getMessage());
            return false;
        }
    }
} 