<?php
require_once __DIR__ . '/../controllers/ConfiguracionAlertaController.php';
require_once __DIR__ . '/../models/ConfiguracionAlerta.php';
require_once __DIR__ . '/../models/Dispositivo.php';

class ConfiguracionAlertaControllerTest extends PHPUnit\Framework\TestCase {
    private $controller;
    private $configuracionModel;
    private $dispositivoModel;

    protected function setUp(): void {
        $this->controller = new ConfiguracionAlertaController();
        $this->configuracionModel = $this->getMockBuilder(ConfiguracionAlerta::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dispositivoModel = $this->getMockBuilder(Dispositivo::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testIndexActionSinSesion() {
        // Simular que no hay sesión
        $_SESSION = [];
        
        // Capturar la salida
        ob_start();
        $this->controller->indexAction();
        $output = ob_get_clean();
        
        // Verificar redirección
        $this->assertStringContainsString('Location: /proyecto-2/auth/login', $output);
    }

    public function testCrearAction() {
        // Simular datos POST
        $_POST = [
            'dispositivo_id' => 1,
            'tipo_alerta' => 'temperatura',
            'valor_minimo' => 35.5,
            'valor_maximo' => 40.0,
            'prioridad' => 'alta'
        ];

        // Simular sesión de usuario
        $_SESSION['user_id'] = 1;

        // Configurar el mock para crearConfiguracion
        $this->configuracionModel->expects($this->once())
            ->method('crearConfiguracion')
            ->willReturn(true);

        // Capturar la salida
        ob_start();
        $this->controller->crearAction();
        $output = ob_get_clean();

        // Verificar mensaje de éxito
        $this->assertEquals('Configuración creada exitosamente', $_SESSION['success']);
    }

    public function testActualizarAction() {
        // Simular datos POST
        $_POST = [
            'id' => 1,
            'valor_minimo' => 36.0,
            'valor_maximo' => 39.0,
            'prioridad' => 'media',
            'estado' => true
        ];

        // Simular sesión de usuario
        $_SESSION['user_id'] = 1;

        // Configurar el mock para actualizarConfiguracion
        $this->configuracionModel->expects($this->once())
            ->method('actualizarConfiguracion')
            ->willReturn(true);

        // Capturar la salida
        ob_start();
        $this->controller->actualizarAction();
        $output = ob_get_clean();

        // Verificar mensaje de éxito
        $this->assertEquals('Configuración actualizada exitosamente', $_SESSION['success']);
    }

    public function testEliminarAction() {
        // Simular sesión de usuario
        $_SESSION['user_id'] = 1;

        // Configurar el mock para eliminarConfiguracion
        $this->configuracionModel->expects($this->once())
            ->method('eliminarConfiguracion')
            ->with(1)
            ->willReturn(true);

        // Capturar la salida
        ob_start();
        $this->controller->eliminarAction(1);
        $output = ob_get_clean();

        // Verificar mensaje de éxito
        $this->assertEquals('Configuración eliminada exitosamente', $_SESSION['success']);
    }
} 