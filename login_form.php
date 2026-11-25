<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/utilidades/CSRFProtection.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="Author" content="Irina Fong - UTP" />
    <title>Inicio de Sesión - Sistema 2FA</title>
    <link rel="stylesheet" href="Estilos/estilo_2fa.css" type="text/css" />
    <script src="jquery/jquery-latest.js" type="text/javascript"></script>
    <script src="jquery/jquery.validate.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $("#deteccionUser").validate({
                rules: {
                    usuario: { required: true, minlength: 4 },
                    contrasena: { required: true }
                },
                messages: {
                    usuario: "Ingrese un usuario válido (mínimo 4 caracteres)",
                    contrasena: "Ingrese su contraseña"
                }
            });
        });
    </script>
</head>
<body>
    <div class="tarjeta-sistema">
        <div class="titulo-utp">
            <h2>Desarrollo Web | UTP</h2>
            <p>Sistema de Autenticación con Doble Factor (2FA)</p>
        </div>

        <?php if (isset($_SESSION["emsg"]) && $_SESSION["emsg"] == 1): ?>
            <div class="mensaje-error">
                Usuario o contraseña incorrectos, vuelva a digitar la información.
            </div>
            <?php unset($_SESSION["emsg"]); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['qr_solicitado_exitosamente']) && $_SESSION['qr_solicitado_exitosamente'] === true): ?>
            <div class="mensaje-exito">
                Registro y generación de QR confirmados para <strong><?= htmlspecialchars($_SESSION['qr_usuario_registrado']) ?></strong>. Ahora inicia sesión.
            </div>
        <?php endif; ?>

        <form id="deteccionUser" name="deteccionUser" method="post" action="index.php">
            <?= CSRFProtection::campoHidden(); ?>
            <input type="hidden" name="tolog" id="tolog" value="true" />

            <div class="campo-grupo">
                <label for="usuario">Usuario o Correo Electrónico:</label>
                <input id="usuario" name="usuario" type="text" minlength="4" required placeholder="Ingrese su usuario o correo" />
            </div>

            <div class="campo-grupo">
                <label for="contrasena">Contraseña:</label>
                <input id="contrasena" name="contrasena" type="password" required placeholder="••••••••" />
            </div>

            <button type="submit" class="btn-utp">Iniciar Sesión</button>
        </form>

        <div class="pie-enlaces">
            ¿No tienes cuenta? <a href="registro.php">Regístrate aquí y genera tu Código QR</a><br><br><a href="ver_privilegios.php" style="font-size:0.84rem; color:#0f766e;">Ver Usuarios y Privilegios de BD en Pantalla</a>
        </div>

        <div style="text-align:center; margin-top:20px; font-size:0.8rem; color:#64748b;">
            &copy; Universidad Tecnológica de Panamá | <?php echo date('Y'); ?>
        </div>
    </div>
</body>
</html>
