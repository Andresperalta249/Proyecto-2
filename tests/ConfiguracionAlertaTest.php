<?php
require_once __DIR__ . '/../models/ConfiguracionAlerta.php';
require_once __DIR__ . '/../models/Model.php';

class ConfiguracionAlertaTest extends PHPUnit\Framework\TestCase {
    private $configuracionModel;
    private $pdo;

    protected function setUp(): void {
        // Configurar base de datos de prueba
        $this->pdo = new PDO(
            'mysql:host=localhost;dbname=proyecto2_test',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Crear tabla de prueba
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS configuraciones_alertas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                dispositivo_id INT NOT NULL,
                tipo_alerta VARCHAR(50) NOT NULL,
                valor_minimo DECIMAL(10,2) NOT NULL,
                valor_maximo DECIMAL(10,2) NOT NULL,
                prioridad ENUM('baja', 'media', 'alta') NOT NULL,
                estado BOOLEAN DEFAULT true,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        $this->configuracionModel = new ConfiguracionAlerta();
    }

    protected function tearDown(): void {
        // Limpiar base de datos de prueba
        $this->pdo->exec("DROP TABLE IF EXISTS configuraciones_alertas");
    }

    public function testCrearConfiguracion() {
        $data = [
            'dispositivo_id' => 1,
            'tipo_alerta' => 'temperatura',
            'valor_minimo' => 35.5,
            'valor_maximo' => 40.0,
            'prioridad' => 'alta',
            'estado' => true
        ];

        $resultado = $this->configuracionModel->crearConfiguracion($data);
        $this->assertTrue($resultado);
    }

    public function testValidarValores() {
        // Crear configuración de prueba
        $data = [
            'dispositivo_id' => 1,
            'tipo_alerta' => 'temperatura',
            'valor_minimo' => 35.5,
            'valor_maximo' => 40.0,
            'prioridad' => 'alta',
            'estado' => true
        ];
        $this->configuracionModel->crearConfiguracion($data);

        // Probar valores dentro del rango
        $this->assertTrue($this->configuracionModel->validarValores('temperatura', 37.5));

        // Probar valores fuera del rango
        $this->assertFalse($this->configuracionModel->validarValores('temperatura', 41.0));
        $this->assertFalse($this->configuracionModel->validarValores('temperatura', 34.0));
    }

    public function testActualizarConfiguracion() {
        // Crear configuración inicial
        $data = [
            'dispositivo_id' => 1,
            'tipo_alerta' => 'temperatura',
            'valor_minimo' => 35.5,
            'valor_maximo' => 40.0,
            'prioridad' => 'alta',
            'estado' => true
        ];
        $id = $this->configuracionModel->crearConfiguracion($data);

        // Actualizar configuración
        $nuevosDatos = [
            'valor_minimo' => 36.0,
            'valor_maximo' => 39.0,
            'prioridad' => 'media',
            'estado' => false
        ];

        $resultado = $this->configuracionModel->actualizarConfiguracion($id, $nuevosDatos);
        $this->assertTrue($resultado);
    }

    public function testEliminarConfiguracion() {
        // Crear configuración para eliminar
        $data = [
            'dispositivo_id' => 1,
            'tipo_alerta' => 'temperatura',
            'valor_minimo' => 35.5,
            'valor_maximo' => 40.0,
            'prioridad' => 'alta',
            'estado' => true
        ];
        $id = $this->configuracionModel->crearConfiguracion($data);

        // Eliminar configuración
        $resultado = $this->configuracionModel->eliminarConfiguracion($id);
        $this->assertTrue($resultado);
    }

    public function testGetConfiguracionesByDispositivo() {
        // Crear configuraciones de prueba
        $data1 = [
            'dispositivo_id' => 1,
            'tipo_alerta' => 'temperatura',
            'valor_minimo' => 35.5,
            'valor_maximo' => 40.0,
            'prioridad' => 'alta',
            'estado' => true
        ];
        $data2 = [
            'dispositivo_id' => 1,
            'tipo_alerta' => 'ritmo_cardiaco',
            'valor_minimo' => 60,
            'valor_maximo' => 100,
            'prioridad' => 'media',
            'estado' => true
        ];
        $this->configuracionModel->crearConfiguracion($data1);
        $this->configuracionModel->crearConfiguracion($data2);

        // Obtener configuraciones
        $configuraciones = $this->configuracionModel->getConfiguracionesByDispositivo(1);
        $this->assertCount(2, $configuraciones);
    }
} 