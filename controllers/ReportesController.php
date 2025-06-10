<?php

class ReportesController {

    public function index() {
        $title = 'Administrador de reportes';
        $content = $this->render('reportes/index', []);
        $GLOBALS['content'] = $content;
        $GLOBALS['title'] = $title;
        $GLOBALS['menuActivo'] = 'reportes';
        require_once 'views/layouts/main.php';
    }
} 