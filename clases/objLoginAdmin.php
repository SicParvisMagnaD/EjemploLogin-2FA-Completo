<?php
require_once __DIR__ . '/GestorHashBcrypt.php';

final class ValidacionLogin {
    private $id;
    private $usuario;
    private $correo;
    private $contrasena;
    private $hastGenerado;
    private $secret2FA;
    private $loginExitoso;
    private $ip;
    private $pdo;
    private $gestorHash;

    public function __construct($usuario, $contrasena, $ipRemoto, $pdo) {
        $this->usuario    = SanitizarEntrada::limpiarUsuario($usuario);
        $this->contrasena = $contrasena;
        $this->ip         = $ipRemoto;
        $this->pdo        = $pdo;
        $this->gestorHash = new GestorHashBcrypt(13);
    }

    public function logger() {
        $usuariologueado = $this->pdo->log($this->usuario);

        if ($usuariologueado) {
            $this->id           = $usuariologueado->id;
            $this->usuario      = $usuariologueado->Usuario;
            $this->correo       = $usuariologueado->correo;
            $this->hastGenerado = $usuariologueado->HashMagic;
            $this->secret2FA    = $usuariologueado->secret_2fa;
            return true;
        } else {
            return false;
        }
    }

    public function autenticar() {
        if ($this->gestorHash->validarHash($this->contrasena, $this->hastGenerado)) {
            $this->loginExitoso = 1;
        } else {
            $this->loginExitoso = 0;
        }
    }

    public function getIntentoLogin() {
        return $this->loginExitoso;
    }

    public function getId() {
        return $this->id;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function getSecret2FA() {
        return $this->secret2FA;
    }

    public function registrarIntentos() {
        $data = array(
            "Usuario"            => $this->usuario,
            "ipRemoto"           => $this->ip,
            "deteccion_anomalia" => $this->loginExitoso
        );
        $this->pdo->insertSeguro("intentos_login", $data);
    }
}
?>
