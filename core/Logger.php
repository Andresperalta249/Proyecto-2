<?php

class Logger {
    private static $instance = null;
    private $logFile;
    
    private function __construct() {
        $this->logFile = ROOT_PATH . '/logs/error.log';
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function log($message, $type = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp][$type] $message" . PHP_EOL;
        
        if (is_array($message) || is_object($message)) {
            $logMessage = "[$timestamp][$type] " . print_r($message, true) . PHP_EOL;
        }
        
        error_log($logMessage, 3, $this->logFile);
    }
    
    public function info($message) {
        $this->log($message, 'INFO');
    }
    
    public function error($message) {
        $this->log($message, 'ERROR');
    }
    
    public function debug($message) {
        $this->log($message, 'DEBUG');
    }
    
    public function warning($message) {
        $this->log($message, 'WARNING');
    }
} 