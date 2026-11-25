<?php
/**
 * Sistema de protección CSRF
 * Ubicación: utilidades/CSRFProtection.php
 */
class CSRFProtection {
    
    public static function generarToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function obtenerToken() {
        return isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
    }
    
    public static function validarToken($token_recibido) {
        if (!isset($_SESSION['csrf_token']) || empty($token_recibido)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token_recibido);
    }
    
    public static function campoHidden() {
        $token = self::generarToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    public static function verificarFormulario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token_recibido = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
            if (!self::validarToken($token_recibido)) {
                http_response_code(403);
                die('Error de seguridad: Token CSRF inválido o ausente.');
            }
        }
    }
    
    public static function regenerarToken() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
}
?>
