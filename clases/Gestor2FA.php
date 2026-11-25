<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

/**
 * Clase para generar secretos TOTP, códigos QR y validar códigos de Google Authenticator
 */
class Gestor2FA {
    private $ga;
    private $nombreSistema;

    public function __construct($nombreSistema = 'UTP_Sistema2FA') {
        $this->ga = new GoogleAuthenticator();
        $this->nombreSistema = $nombreSistema;
    }

    public function generarSecreto() {
        return $this->ga->generateSecret();
    }

    public function generarUrlQR($correo, $secreto) {
        // URI estándar que entiende Google Authenticator
        $urlOtp = 'otpauth://totp/'
                . rawurlencode($this->nombreSistema . ':' . $correo)
                . '?secret=' . $secreto
                . '&issuer=' . rawurlencode($this->nombreSistema);

        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($urlOtp);
    }

    public function validarCodigo($secreto, $codigoUsuario) {
        if (empty($secreto) || empty($codigoUsuario)) {
            return false;
        }
        return $this->ga->checkCode($secreto, $codigoUsuario);
    }
}
?>