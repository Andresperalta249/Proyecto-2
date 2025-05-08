<?php
class MascotaController extends Controller {
    private $mascotaModel;
    private $dispositivoModel;
    private $logModel;
    private $notificacionModel;

    public function __construct() {
        parent::__construct();
        $this->mascotaModel = $this->loadModel('Mascota');
        $this->dispositivoModel = $this->loadModel('Dispositivo');
        $this->logModel = $this->loadModel('Log');
        $this->notificacionModel = $this->loadModel('Notificacion');
    }

    public function indexAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        // Obtener mascotas del usuario
        $mascotas = $this->mascotaModel->getMascotasByUser($_SESSION['user_id']);

        $title = 'Mis Mascotas';
        $content = $this->render('mascotas/index', ['mascotas' => $mascotas]);
        require_once 'views/layouts/main.php';
    }

    public function createAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateRequest(['nombre', 'especie', 'raza', 'fecha_nacimiento']);
            $data['usuario_id'] = $_SESSION['user_id'];

            // Manejar subida de imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $imagen = $this->handleImageUpload($_FILES['imagen']);
                if ($imagen) {
                    $data['imagen'] = $imagen;
                }
            }

            if ($this->mascotaModel->createMascota($data)) {
                $this->logModel->crearLog($_SESSION['user_id'], 'Creación de mascota: ' . $data['nombre']);
                
                // Crear notificación
                $this->notificacionModel->crearNotificacion(
                    $_SESSION['user_id'],
                    'Nueva Mascota Registrada',
                    'Has registrado exitosamente a ' . $data['nombre'],
                    'exito',
                    BASE_URL . 'mascotas/view/' . $this->mascotaModel->getLastInsertId()
                );
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Mascota registrada correctamente',
                    'redirect' => BASE_URL . 'mascotas'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Error al registrar la mascota'
                ], 500);
            }
        }

        $title = 'Nueva Mascota';
        $content = $this->render('mascotas/create');
        require_once 'views/layouts/main.php';
    }

    public function editAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            redirect('auth/login');
        }

        // Verificar que la mascota pertenezca al usuario
        $mascota = $this->mascotaModel->findById($id);
        if (!$mascota || $mascota['usuario_id'] != $_SESSION['user_id']) {
            redirect('mascotas');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateRequest(['nombre', 'especie', 'raza', 'fecha_nacimiento']);

            // Manejar subida de imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $imagen = $this->handleImageUpload($_FILES['imagen']);
                if ($imagen) {
                    // Eliminar imagen anterior si existe
                    if ($mascota['imagen']) {
                        $this->deleteImage($mascota['imagen']);
                    }
                    $data['imagen'] = $imagen;
                }
            }

            if ($this->mascotaModel->updateMascota($id, $data)) {
                $this->logModel->crearLog($_SESSION['user_id'], 'Actualización de mascota: ' . $data['nombre']);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Mascota actualizada correctamente',
                    'redirect' => BASE_URL . 'mascotas'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Error al actualizar la mascota'
                ], 500);
            }
        }

        // Obtener dispositivos asignados
        $dispositivos = $this->dispositivoModel->getDispositivosByMascota($id);

        $title = 'Editar Mascota';
        $content = $this->render('mascotas/edit', [
            'mascota' => $mascota,
            'dispositivos' => $dispositivos
        ]);
        require_once 'views/layouts/main.php';
    }

    public function deleteAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }

        // Verificar que la mascota pertenezca al usuario
        $mascota = $this->mascotaModel->findById($id);
        if (!$mascota || $mascota['usuario_id'] != $_SESSION['user_id']) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Mascota no encontrada'
            ], 404);
        }

        // Eliminar imagen si existe
        if ($mascota['imagen']) {
            $this->deleteImage($mascota['imagen']);
        }

        if ($this->mascotaModel->deleteMascota($id)) {
            $this->logModel->crearLog($_SESSION['user_id'], 'Eliminación de mascota: ' . $mascota['nombre']);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Mascota eliminada correctamente'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error al eliminar la mascota'
            ], 500);
        }
    }

    public function viewAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            redirect('auth/login');
        }

        // Verificar que la mascota pertenezca al usuario
        $mascota = $this->mascotaModel->findById($id);
        if (!$mascota || $mascota['usuario_id'] != $_SESSION['user_id']) {
            redirect('mascotas');
        }

        // Obtener dispositivos asignados
        $dispositivos = $this->dispositivoModel->getDispositivosByMascota($id);
        
        // Obtener historial médico
        $historial = $this->mascotaModel->getHistorialMedico($id);

        $title = 'Detalles de Mascota';
        $content = $this->render('mascotas/view', [
            'mascota' => $mascota,
            'dispositivos' => $dispositivos,
            'historial' => $historial
        ]);
        require_once 'views/layouts/main.php';
    }

    public function addHistorialAction($id = null) {
        if (!isset($_SESSION['user_id']) || !$id) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado'
            ], 403);
        }

        // Verificar que la mascota pertenezca al usuario
        $mascota = $this->mascotaModel->findById($id);
        if (!$mascota || $mascota['usuario_id'] != $_SESSION['user_id']) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Mascota no encontrada'
            ], 404);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateRequest(['fecha', 'tipo', 'descripcion']);
            $data['mascota_id'] = $id;

            // Manejar subida de documentos
            if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
                $documento = $this->handleDocumentUpload($_FILES['documento']);
                if ($documento) {
                    $data['documento'] = $documento;
                }
            }

            if ($this->mascotaModel->addHistorialMedico($data)) {
                $this->logModel->crearLog($_SESSION['user_id'], 'Registro de historial médico para mascota: ' . $mascota['nombre']);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Historial médico registrado correctamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Error al registrar el historial médico'
                ], 500);
            }
        }
    }

    public function estadisticasAction() {
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $mascotaModel = new Mascota();

        // Obtener estadísticas generales
        $estadisticas = $mascotaModel->getEstadisticasAvanzadas($userId);
        
        // Obtener estado de mascotas
        $mascotas_estado = $mascotaModel->getMascotasPorEstado($userId);
        
        // Obtener próximas vacunas
        $proximas_vacunas = [];
        foreach ($mascotas_estado as $mascota) {
            $vacunas = $mascotaModel->getProximasVacunas($mascota['id']);
            $proximas_vacunas = array_merge($proximas_vacunas, $vacunas);
        }
        
        // Ordenar vacunas por fecha
        usort($proximas_vacunas, function($a, $b) {
            return strtotime($a['fecha']) - strtotime($b['fecha']);
        });

        // Cargar la vista
        require_once 'views/mascotas/estadisticas.php';
    }

    private function handleImageUpload($file) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        if ($file['size'] > $maxSize) {
            return false;
        }

        $uploadDir = 'uploads/mascotas/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = uniqid() . '_' . basename($file['name']);
        $uploadFile = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            return $filename;
        }

        return false;
    }

    private function handleDocumentUpload($file) {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        if ($file['size'] > $maxSize) {
            return false;
        }

        $uploadDir = 'uploads/documentos/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = uniqid() . '_' . basename($file['name']);
        $uploadFile = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            return $filename;
        }

        return false;
    }

    private function deleteImage($filename) {
        $filepath = 'uploads/mascotas/' . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
}
?> 