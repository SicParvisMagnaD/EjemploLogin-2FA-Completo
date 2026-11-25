<?php
require_once __DIR__ . '/SeguridadHashInterface.php';

/**
 * Implementación concreta con PASSWORD_BCRYPT y costo 13 (según repo de la profesora)
 */
class GestorHashBcrypt implements SeguridadHashInterface {
    private $costo;

    public function __construct($costo = 13) {
        $this->costo = $costo;
    }

    public function generarHash($clavePlana) {
        return password_hash($clavePlana, PASSWORD_BCRYPT, ['cost' => $this->costo]);
    }

    public function validarHash($clavePlana, $hashAlmacenado) {
        return password_verify($clavePlana, $hashAlmacenado);
    }
}
?>
