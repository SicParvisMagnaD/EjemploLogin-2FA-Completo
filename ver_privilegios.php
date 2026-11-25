<?php
require_once __DIR__ . '/clases/mysql.inc.php';

$dbLogin = new mod_db('login');
$dbRegistro = new mod_db('registro');

$grantsLogin = $dbLogin->getConexion()->query("SHOW GRANTS FOR CURRENT_USER()")->fetchAll(PDO::FETCH_COLUMN);
$grantsRegistro = $dbRegistro->getConexion()->query("SHOW GRANTS FOR CURRENT_USER()")->fetchAll(PDO::FETCH_COLUMN);
$usuarioActualLogin = $dbLogin->getConexion()->query("SELECT CURRENT_USER()")->fetchColumn();
$usuarioActualReg = $dbRegistro->getConexion()->query("SELECT CURRENT_USER()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Privilegios de Usuarios BD - Auditoría Rúbrica</title>
    <link rel="stylesheet" href="Estilos/estilo_2fa.css" type="text/css" />
    <style>
        .box-sql { background: #0f172a; color: #38bdf8; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 0.88rem; overflow-x: auto; margin: 10px 0; }
        .grant-item { background: #f1f5f9; border-left: 4px solid #1e3a8a; padding: 8px 12px; margin: 6px 0; font-family: monospace; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="tarjeta-sistema" style="max-width: 720px;">
        <div class="titulo-utp">
            <h2>Usuarios y Privilegios Mínimos en MySQL</h2>
            <p>Verificación de seguridad sin uso del superusuario <code>root</code></p>
        </div>

        <h3 style="color:#1e3a8a; font-size:1rem;">Comando SQL que permite ver los privilegios concedidos:</h3>
        <div class="box-sql">
            SHOW GRANTS FOR 'app_login_2fa'@'localhost';<br>
            SHOW GRANTS FOR 'app_registro_2fa'@'localhost';
        </div>

        <h3 style="color:#1e3a8a; font-size:1rem; margin-top:18px;">1. Usuario de Inicio de Sesión: <code><?= htmlspecialchars($usuarioActualLogin) ?></code></h3>
        <?php foreach ($grantsLogin as $g): ?>
            <div class="grant-item"><?= htmlspecialchars($g) ?></div>
        <?php endforeach; ?>

        <h3 style="color:#1e3a8a; font-size:1rem; margin-top:18px;">2. Usuario de Registro y 2FA: <code><?= htmlspecialchars($usuarioActualReg) ?></code></h3>
        <?php foreach ($grantsRegistro as $g): ?>
            <div class="grant-item"><?= htmlspecialchars($g) ?></div>
        <?php endforeach; ?>

        <div class="pie-enlaces" style="margin-top:22px;">
            <a href="login.php">Volver al Inicio de Sesión</a> | <a href="registro.php">Ir al Registro</a>
        </div>
    </div>
</body>
</html>
