<?php
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteController extends Controller {
    private $mascotaModel;
    private $dispositivoModel;
    private $alertaModel;
    private $logModel;

    public function __construct() {
        parent::__construct();
        $this->mascotaModel = $this->loadModel('Mascota');
        $this->dispositivoModel = $this->loadModel('Dispositivo');
        $this->alertaModel = $this->loadModel('Alerta');
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

    public function alertasAction() {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $formato = $_GET['formato'] ?? 'pdf';

        $alertas = $this->alertaModel->getAlertasByPeriodo($fechaInicio, $fechaFin);
        $estadisticas = $this->alertaModel->getEstadisticas($fechaInicio, $fechaFin);

        if ($formato === 'pdf') {
            $this->generarPDFAlertas($alertas, $estadisticas, $fechaInicio, $fechaFin);
        } else {
            $this->generarExcelAlertas($alertas, $estadisticas, $fechaInicio, $fechaFin);
        }
    }

    private function generarPDFMascotas($mascotas, $estadisticas, $fechaInicio, $fechaFin) {
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
    }

    private function generarExcelMascotas($mascotas, $estadisticas, $fechaInicio, $fechaFin) {
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
    }

    private function generarPDFDispositivos($dispositivos, $lecturas, $fechaInicio, $fechaFin) {
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
    }

    private function generarExcelDispositivos($dispositivos, $lecturas, $fechaInicio, $fechaFin) {
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
    }

    private function generarPDFAlertas($alertas, $estadisticas, $fechaInicio, $fechaFin) {
        $dompdf = new Dompdf();
        
        $html = $this->render('reportes/alertas_pdf', [
            'alertas' => $alertas,
            'estadisticas' => $estadisticas,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin
        ], true);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('reporte_alertas.pdf', ['Attachment' => true]);
    }

    private function generarExcelAlertas($alertas, $estadisticas, $fechaInicio, $fechaFin) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar encabezados
        $sheet->setCellValue('A1', 'Reporte de Alertas');
        $sheet->setCellValue('A2', 'Período: ' . $fechaInicio . ' al ' . $fechaFin);
        
        $sheet->setCellValue('A4', 'Fecha');
        $sheet->setCellValue('B4', 'Tipo');
        $sheet->setCellValue('C4', 'Mensaje');
        $sheet->setCellValue('D4', 'Dispositivo');
        $sheet->setCellValue('E4', 'Mascota');
        $sheet->setCellValue('F4', 'Estado');

        // Llenar datos
        $row = 5;
        foreach ($alertas as $alerta) {
            $sheet->setCellValue('A' . $row, $alerta['fecha']);
            $sheet->setCellValue('B' . $row, $alerta['tipo']);
            $sheet->setCellValue('C' . $row, $alerta['mensaje']);
            $sheet->setCellValue('D' . $row, $alerta['dispositivo_nombre']);
            $sheet->setCellValue('E' . $row, $alerta['mascota_nombre']);
            $sheet->setCellValue('F' . $row, $alerta['estado']);
            $row++;
        }

        // Estadísticas
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Estadísticas');
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Alertas:');
        $sheet->setCellValue('B' . $row, $estadisticas['total']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Alertas Pendientes:');
        $sheet->setCellValue('B' . $row, $estadisticas['pendientes']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Alertas Resueltas:');
        $sheet->setCellValue('B' . $row, $estadisticas['resueltas']);

        // Generar archivo
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="reporte_alertas.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }
} 