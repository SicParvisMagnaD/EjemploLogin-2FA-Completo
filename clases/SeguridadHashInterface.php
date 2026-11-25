<?php
/**
 * Interfaz POO para generar y validar Hashes
 * Criterio 9 de la Rúbrica (5 pts)
 */
interface SeguridadHashInterface {
    public function generarHash($clavePlana);
    public function validarHash($clavePlana, $hashAlmacenado);
}
?>
