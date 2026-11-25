<?php
session_start();
require_once __DIR__ . '/clases/mysql.inc.php';
require_once __DIR__ . '/clases/SanitizarEntrada.php';
require_once __DIR__ . '/clases/GestorHashBcrypt.php';
require_once __DIR__ . '/clases/Gestor2FA.php';
require_once __DIR__ . '/clases/FormularioRegistro.php';
require_once __DIR__ . '/utilidades/CSRFProtection.php';

$errores = [];
$datosRegistro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verificar token Anti-CSRF
    CSRFProtection::verificarFormulario();

    // 2. Sanitizar entradas usando métodos estáticos
    $entrada = [
        'nombre'               => SanitizarEntrada::limpiarCadena($_POST['nombre'] ?? ''),
        'apellido'             => SanitizarEntrada::limpiarCadena($_POST['apellido'] ?? ''),
        'usuario'              => SanitizarEntrada::limpiarUsuario($_POST['usuario'] ?? ''),
        'correo'               => SanitizarEntrada::limpiarCorreo($_POST['correo'] ?? ''),
        'sexo'                 => SanitizarEntrada::limpiarCadena($_POST['sexo'] ?? ''),
        'contrasena'           => $_POST['contrasena'] ?? '',
        'confirmar_contrasena' => $_POST['confirmar_contrasena'] ?? ''
    ];

    $db = new mod_db('registro');
    $gestorHash = new GestorHashBcrypt(13);
    $gestor2FA = new Gestor2FA('UTP_DesarrolloWeb_2FA');
    $registro = new FormularioRegistro($db, $gestorHash, $gestor2FA);

    // 3. Validar campos y verificar duplicados antes de guardar
    if ($registro->validarCampos($entrada)) {
        if (!$registro->existeDuplicado($entrada['usuario'], $entrada['correo'])) {
            $datosRegistro = $registro->guardarUsuario($entrada);

            if ($datosRegistro) {
                CSRFProtection::regenerarToken();
                // Criterio 8 de la Rúbrica: Crear otra sesión para confirmar que la fase de solicitud del código QR fue exitosa
                $_SESSION['qr_solicitado_exitosamente'] = true;
                $_SESSION['qr_usuario_registrado'] = $datosRegistro['usuario'];
                $_SESSION['qr_fecha_solicitud'] = date('Y-m-d H:i:s');
            }
        }
    }

    $errores = $registro->getErrores();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro de Usuario - 2FA</title>
    <link rel="stylesheet" href="Estilos/estilo_2fa.css" type="text/css" />
    <script>
        // Validación en el lado del Frontend (Criterio 2 de la rúbrica)
        function validarRegistroFrontend() {
            const pass = document.getElementById('contrasena').value;
            const confirm = document.getElementById('confirmar_contrasena').value;
            if (pass.length < 8) {
                alert("La contraseña debe tener al menos 8 caracteres.");
                return false;
            }
            if (pass !== confirm) {
                alert("Las contraseñas no coinciden.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="tarjeta-sistema">
        <div class="titulo-utp">
            <h2>Registro de Usuario</h2>
            <p>Completa tus datos para activar Google Authenticator (2FA)</p>
        </div>

        <?php if (!empty($errores)): ?>
            <div class="mensaje-error">
                <ul style="margin:0; padding-left:18px;">
                    <?php foreach ($errores as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($datosRegistro): ?>
            <div class="mensaje-exito">
                <strong>¡Usuario registrado correctamente!</strong><br>
                Sesión de solicitud de Código QR confirmada exitosamente.
            </div>

            <div class="contenedor-qr">
                <p style="margin-top:0; font-size:0.9rem;">
                    Escanea este Código QR con <strong>Google Authenticator</strong> antes de iniciar sesión:
                </p>
                <img src="<?= htmlspecialchars($datosRegistro['qr_url']) ?>" alt="Código QR Google Authenticator" />
                <div>
                    <span class="clave-manual">Secreto manual: <?= htmlspecialchars($datosRegistro['secret_2fa']) ?></span>
                </div>
            </div>

            <a href="login.php" class="btn-utp">Continuar al Inicio de Sesión</a>
        <?php else: ?>
            <form method="post" action="registro.php" onsubmit="return validarRegistroFrontend();">
                <?= CSRFProtection::campoHidden(); ?>

                <div class="campo-grupo">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej. Javier" />
                </div>

                <div class="campo-grupo">
                    <label for="apellido">Apellido *</label>
                    <input type="text" id="apellido" name="apellido" required placeholder="Ej. Vallarino" />
                </div>

                <div class="campo-grupo">
                    <label for="usuario">Nombre de Usuario *</label>
                    <input type="text" id="usuario" name="usuario" required minlength="4" placeholder="Ej. jvallarino" />
                </div>

                <div class="campo-grupo">
                    <label for="correo">Correo Electrónico *</label>
                    <input type="email" id="correo" name="correo" required placeholder="usuario@utp.ac.pa" />
                </div>

                <div class="campo-grupo">
                    <label for="sexo">Sexo *</label>
                    <select id="sexo" name="sexo" required>
                        <option value="">Seleccione...</option>
                        <option value="M">Masculino (M)</option>
                        <option value="F">Femenino (F)</option>
                    </select>
                </div>

                <div class="campo-grupo">
                    <label for="contrasena">Contraseña *</label>
                    <input type="password" id="contrasena" name="contrasena" required minlength="8" placeholder="Mínimo 8 caracteres" />
                </div>

                <div class="campo-grupo">
                    <label for="confirmar_contrasena">Confirmar Contraseña *</label>
                    <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required minlength="8" placeholder="Repita la contraseña" />
                </div>

                <button type="submit" class="btn-utp">Registrar y Generar Código QR</button>
            </form>

            <div class="pie-enlaces">
                ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>