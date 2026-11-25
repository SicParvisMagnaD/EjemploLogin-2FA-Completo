<?php
session_start();
include("clases/mysql.inc.php");
$db = new mod_db('login');

include("clases/SanitizarEntrada.php");
include("comunes/loginfunciones.php");
include("clases/objLoginAdmin.php");
require_once("utilidades/CSRFProtection.php");

$tolog = isset($_POST["tolog"]) ? $_POST["tolog"] : false;

if (isset($tolog) && ($tolog == "true") && ($_SERVER['REQUEST_METHOD'] === 'POST')) {
    // 1. Verificar token Anti-CSRF
    CSRFProtection::verificarFormulario();

    $Usuario  = $_POST['usuario'] ?? '';
    $ClaveKey = $_POST['contrasena'] ?? '';
    $ipRemoto = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $Logearme = new ValidacionLogin($Usuario, $ClaveKey, $ipRemoto, $db);

    if ($Logearme->logger()) {
        $Logearme->autenticar();
        if ($Logearme->getIntentoLogin()) {
            // Fase 1 correcta: Guardar sesión intermedia pendiente de 2FA
            session_regenerate_id(true);
            CSRFProtection::regenerarToken();

            $_SESSION['2fa_pendiente']    = true;
            $_SESSION['2fa_usuario_id']   = $Logearme->getId();
            $_SESSION['Usuario_Temporal'] = $Logearme->getUsuario();
            $_SESSION['Correo_Temporal']  = $Logearme->getCorreo();
            $_SESSION['Secret_2FA']       = $Logearme->getSecret2FA();

            $Logearme->registrarIntentos();
            $tolog = false;
            redireccionar("verificar_2fa.php");
            exit();
        } else {
            $Logearme->registrarIntentos();
            $_SESSION["emsg"] = 1;
            redireccionar("login.php");
            exit();
        }
    } else {
        $_SESSION["emsg"] = 1;
        redireccionar("login.php");
        exit();
    }
} else {
    redireccionar("login.php");
    exit();
}
?>
