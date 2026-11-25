<?php
session_start();
require_once __DIR__ . '/clases/SanitizarEntrada.php';
require_once __DIR__ . '/clases/Gestor2FA.php';
require_once __DIR__ . '/utilidades/CSRFProtection.php';
include("comunes/loginfunciones.php");

// Verificar que haya pasado la fase 1 del login
if (!isset($_SESSION['2fa_pendiente']) || $_SESSION['2fa_pendiente'] !== true) {
    redireccionar("login.php");
    exit();
}

$gestor2FA = new Gestor2FA('UTP_DesarrolloWeb_2FA');
$error = '';
$mostrarQR = isset($_GET['qr']) && $_GET['qr'] === '1';
$qrUrl = $gestor2FA->generarUrlQR($_SESSION['Correo_Temporal'], $_SESSION['Secret_2FA']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRFProtection::verificarFormulario();

    $codigoUsuario = SanitizarEntrada::limpiarCodigo2FA($_POST['codigo_2fa'] ?? '');

    if (strlen($codigoUsuario) !== 6) {
        $error = "Debe ingresar un código numérico de 6 dígitos.";
    } elseif ($gestor2FA->validarCodigo($_SESSION['Secret_2FA'], $codigoUsuario)) {
        // Verificación 2FA exitosa -> Conceder acceso completo al sistema
        session_regenerate_id(true);
        CSRFProtection::regenerarToken();

        $_SESSION['autenticado'] = "SI";
        $_SESSION['sesion_2fa_completada'] = true;
        $_SESSION['Usuario']     = $_SESSION['Usuario_Temporal'];
        unset($_SESSION['2fa_pendiente']);

        redireccionar("formularios/PanelControl.php");
        exit();
    } else {
        $error = "Código de verificación 2FA incorrecto o expirado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verificación 2FA - Google Authenticator</title>
    <link rel="stylesheet" href="Estilos/estilo_2fa.css" type="text/css" />
</head>
<body>
    <div class="tarjeta-sistema">
        <div class="titulo-utp">
            <h2>Verificación en Dos Pasos (2FA)</h2>
            <p>Usuario: <strong><?= htmlspecialchars($_SESSION['Usuario_Temporal']) ?></strong></p>
        </div>

        <?php if (isset($_SESSION['qr_solicitado_exitosamente']) && $_SESSION['qr_solicitado_exitosamente'] === true): ?>
            <div class="mensaje-exito" style="font-size:0.84rem;">
                ✓ Fase de solicitud de Código QR confirmada en sesión.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mensaje-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="verificar_2fa.php" autocomplete="off">
            <?= CSRFProtection::campoHidden(); ?>

            <div class="campo-grupo">
                <label for="codigo_2fa">Código de 6 dígitos (Google Authenticator):</label>
                <input type="text" id="codigo_2fa" name="codigo_2fa" class="input-codigo-2fa" maxlength="6" pattern="[0-9]{6}" required autofocus placeholder="000000" />
            </div>

            <button type="submit" class="btn-utp">Verificar Código y Entrar</button>
        </form>

        <?php if ($mostrarQR): ?>
            <div class="contenedor-qr">
                <p style="margin-top:0; font-size:0.85rem;">Escanea este QR con Google Authenticator:</p>
                <img src="<?= htmlspecialchars($qrUrl) ?>" alt="QR 2FA" />
            </div>
        <?php else: ?>
            <div class="pie-enlaces">
                <a href="verificar_2fa.php?qr=1">¿Necesitas volver a escanear tu Código QR?</a>
            </div>
        <?php endif; ?>

        <div class="pie-enlaces">
            <a href="salir.php" style="color:#dc2626;">Cancelar y volver al Login</a>
        </div>
    </div>
</body>
</html>
