<?php
// require_once 'vendor/autoload.php';
// use Dompdf\Dompdf;
// use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteController extends Controller {
    private $mascotaModel;
    private $dispositivoModel;
    private $logModel;

    public function __construct() {
        parent::__construct();
        $this->mascotaModel = $this->loadModel('Mascota');
        $this->dispositivoModel = $this->loadModel('Dispositivo');
        $this->logModel = $this->loadModel('Log');
    }

    public function indexAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        $title = 'Generar Reportes';
        $content = $this->render('reportes/index');
        require_once 'views/layouts/main.php';
    }

    public function mascotasAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $formato = $_GET['formato'] ?? 'pdf';

        $mascotas = $this->mascotaModel->getMascotasByUser($_SESSION['user_id']);
        $estadisticas = $this->mascotaModel->getEstadisticas($_SESSION['user_id']);

        if ($formato === 'pdf') {
            $this->generarPDFMascotas($mascotas, $estadisticas, $fechaInicio, $fechaFin);
        } else {
            $this->generarExcelMascotas($mascotas, $estadisticas, $fechaInicio, $fechaFin);
        }
    }

    public function dispositivosAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $formato = $_GET['formato'] ?? 'pdf';

        $dispositivos = $this->dispositivoModel->getDispositivosByUser($_SESSION['user_id']);
        $lecturas = $this->dispositivoModel->getLecturasByPeriodo($fechaInicio, $fechaFin);

        if ($formato === 'pdf') {
            $this->generarPDFDispositivos($dispositivos, $lecturas, $fechaInicio, $fechaFin);
        } else {
            $this->generarExcelDispositivos($dispositivos, $lecturas, $fechaInicio, $fechaFin);
        }
    }

    public function monitoreoAction() {
        if (!verificarPermiso('ver_reportes')) {
            $this->view->render('errors/403');
            return;
        }

        $this->view->setLayout('main');
        $this->view->setData('titulo', 'Reporte de Monitoreo IoT');
        $this->view->setData('subtitulo', 'Consulta y filtra el histórico de sensores de todas las mascotas.');
        $this->view->render('reportes/monitoreo');
    }

    private function generarPDFMascotas($mascotas, $estadisticas, $fechaInicio, $fechaFin) {
        // Comentado temporalmente - requiere librería Dompdf
        /*
        $dompdf = new Dompdf();
        
        $html = $this->render('reportes/mascotas_pdf', [
            'mascotas' => $mascotas,
            'estadisticas' => $estadisticas,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin
        ], true);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('reporte_mascotas.pdf', ['Attachment' => true]);
        */
        echo "Funcionalidad de PDF temporalmente deshabilitada";
    }

    private function generarExcelMascotas($mascotas, $estadisticas, $fechaInicio, $fechaFin) {
        // Comentado temporalmente - requiere librería PhpSpreadsheet
        /*
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar encabezados
        $sheet->setCellValue('A1', 'Reporte de Mascotas');
        $sheet->setCellValue('A2', 'Período: ' . $fechaInicio . ' al ' . $fechaFin);
        
        $sheet->setCellValue('A4', 'Nombre');
        $sheet->setCellValue('B4', 'Especie');
        $sheet->setCellValue('C4', 'Raza');
        $sheet->setCellValue('D4', 'Edad');
        $sheet->setCellValue('E4', 'Peso');
        $sheet->setCellValue('F4', 'Dispositivos');

        // Llenar datos
        $row = 5;
        foreach ($mascotas as $mascota) {
            $sheet->setCellValue('A' . $row, $mascota['nombre']);
            $sheet->setCellValue('B' . $row, $mascota['especie']);
            $sheet->setCellValue('C' . $row, $mascota['raza']);
            $sheet->setCellValue('D' . $row, calcularEdad($mascota['fecha_nacimiento']));
            $sheet->setCellValue('E' . $row, $mascota['peso']);
            $sheet->setCellValue('F' . $row, $mascota['total_dispositivos']);
            $row++;
        }

        // Estadísticas
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Estadísticas');
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Mascotas:');
        $sheet->setCellValue('B' . $row, $estadisticas['total']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Especies Diferentes:');
        $sheet->setCellValue('B' . $row, $estadisticas['especies']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Edad Promedio:');
        $sheet->setCellValue('B' . $row, round($estadisticas['edad_promedio'], 1) . ' años');

        // Generar archivo
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="reporte_mascotas.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        */
        echo "Funcionalidad de Excel temporalmente deshabilitada";
    }

    private function generarPDFDispositivos($dispositivos, $lecturas, $fechaInicio, $fechaFin) {
        // Comentado temporalmente - requiere librería Dompdf
        /*
        $dompdf = new Dompdf();
        
        $html = $this->render('reportes/dispositivos_pdf', [
            'dispositivos' => $dispositivos,
            'lecturas' => $lecturas,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin
        ], true);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $dompdf->stream('reporte_dispositivos.pdf', ['Attachment' => true]);
        */
        echo "Funcionalidad de PDF temporalmente deshabilitada";
    }

    private function generarExcelDispositivos($dispositivos, $lecturas, $fechaInicio, $fechaFin) {
        // Comentado temporalmente - requiere librería PhpSpreadsheet
        /*
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar encabezados
        $sheet->setCellValue('A1', 'Reporte de Dispositivos');
        $sheet->setCellValue('A2', 'Período: ' . $fechaInicio . ' al ' . $fechaFin);
        
        $sheet->setCellValue('A4', 'Nombre');
        $sheet->setCellValue('B4', 'Tipo');
        $sheet->setCellValue('C4', 'Estado');
        $sheet->setCellValue('D4', 'Última Lectura');
        $sheet->setCellValue('E4', 'Mascota Asociada');
        $sheet->setCellValue('F4', 'Total Lecturas');

        // Llenar datos
        $row = 5;
        foreach ($dispositivos as $dispositivo) {
            $sheet->setCellValue('A' . $row, $dispositivo['nombre']);
            $sheet->setCellValue('B' . $row, $dispositivo['tipo']);
            $sheet->setCellValue('C' . $row, $dispositivo['estado']);
            $sheet->setCellValue('D' . $row, $dispositivo['ultima_lectura']);
            $sheet->setCellValue('E' . $row, $dispositivo['mascota_nombre']);
            $sheet->setCellValue('F' . $row, $dispositivo['total_lecturas']);
            $row++;
        }

        // Generar archivo
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="reporte_dispositivos.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        */
        echo "Funcionalidad de Excel temporalmente deshabilitada";
    }

    // --- ENDPOINTS AJAX PARA REPORTE MODERNO ---
    public function getPropietariosAction() {
        header('Content-Type: application/json');
        $q = $_GET['q'] ?? '';
        $model = $this->loadModel('User');
        $propietarios = $model->buscar($q, ['rol_id' => 3]); // 3 = rol usuario normal
        $result = array_map(function($u) {
            return [
                'id' => $u['id_usuario'],
                'text' => $u['nombre'] . ' (' . $u['email'] . ')'
            ];
        }, $propietarios);
        echo json_encode(['results' => $result]);
        exit;
    }

    public function getMascotasPorPropietarioAction() {
        header('Content-Type: application/json');
        $usuario_id = $_GET['usuario_id'] ?? null;
        if (!$usuario_id) { echo json_encode(['results'=>[]]); exit; }
        $model = $this->loadModel('Mascota');
        $mascotas = $model->getMascotasByUser($usuario_id);
        $result = array_map(function($m) {
            return [
                'id' => $m['id_mascota'],
                'text' => $m['nombre'] . ' (' . $m['especie'] . ')'
            ];
        }, $mascotas);
        echo json_encode(['results' => $result]);
        exit;
    }

    public function getMacsAction() {
        header('Content-Type: application/json');
        $q = $_GET['q'] ?? '';
        $model = $this->loadModel('Dispositivo');
        $macs = $model->buscarMacs($q);
        $result = array_map(function($d) {
            return [
                'id' => $d['mac'],
                'text' => $d['mac']
            ];
        }, $macs);
        echo json_encode(['results' => $result]);
        exit;
    }

    public function getRegistrosAction() {
        header('Content-Type: application/json');
        $usuario_id = $_GET['usuario_id'] ?? null;
        $mascota_id = $_GET['mascota_id'] ?? null;
        $mac = $_GET['mac'] ?? null;
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = min(50, intval($_GET['perPage'] ?? 20));
        $fecha_inicio = $_GET['fecha_inicio'] ?? null;
        $fecha_fin = $_GET['fecha_fin'] ?? null;
        $model = $this->loadModel('DatosSensor');
        $result = $model->buscarRegistrosAvanzado($usuario_id, $mascota_id, $mac, $page, $perPage, $fecha_inicio, $fecha_fin);
        echo json_encode($result);
        exit;
    }

    public function exportarExcelAction() {
        $usuario_id = $_GET['usuario_id'] ?? null;
        $mascota_id = $_GET['mascota_id'] ?? null;
        $mac = $_GET['mac'] ?? null;
        $model = $this->loadModel('DatosSensor');
        $registros = $model->buscarRegistrosAvanzado($usuario_id, $mascota_id, $mac, 1, 10000)['data'];
        // Aquí se puede usar PhpSpreadsheet para exportar a Excel
        // Por simplicidad, exporto CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="reporte_mascotas_iot.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Fecha y hora','Temperatura','Ritmo cardíaco','Ubicación','Batería']);
        foreach($registros as $r) {
            fputcsv($out, [$r['fecha_hora'],$r['temperatura'],$r['ritmo_cardiaco'],$r['ubicacion'],$r['bateria']]);
        }
        fclose($out);
        exit;
    }

    public function getUltimasUbicacionesAction() {
        header('Content-Type: application/json');
        $model = $this->loadModel('DatosSensor');
        $result = $model->obtenerUltimasUbicacionesMascotas();
        echo json_encode($result);
        exit;
    }
} 