<?php
/**
 * Clase SanitizarEntrada con métodos estáticos
 * Criterio 5 de la Rúbrica (10 pts): Mínimo 3 métodos estáticos para sanitizar datos.
 */
class SanitizarEntrada {

    // 1. Sanitiza una cadena general eliminando espacios, barras y etiquetas HTML
    public static function limpiarCadena($cadena) {
        if (!is_string($cadena)) {
            return '';
        }
        $cadena = trim($cadena);
        $cadena = stripslashes($cadena);
        return htmlspecialchars(strip_tags($cadena), ENT_QUOTES, 'UTF-8');
    }

    // 2. Sanitiza y valida el formato de un correo electrónico
    public static function limpiarCorreo($correo) {
        $correo = trim((string)$correo);
        $correoLimpio = filter_var($correo, FILTER_SANITIZE_EMAIL);
        return filter_var($correoLimpio, FILTER_VALIDATE_EMAIL) ? strtolower($correoLimpio) : false;
    }

    // 3. Sanitiza el nombre de usuario permitiendo solo alfanuméricos, punto, guion y guion bajo
    public static function limpiarUsuario($usuario) {
        $usuario = trim((string)$usuario);
        return preg_replace('/[^a-zA-Z0-9._-]/', '', $usuario);
    }

    // 4. Sanitiza el código TOTP de 6 dígitos de Google Authenticator
    public static function limpiarCodigo2FA($codigo) {
        return preg_replace('/[^0-9]/', '', trim((string)$codigo));
    }
}
?>
