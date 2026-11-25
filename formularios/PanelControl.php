<?php
include("../comunes/bloque_Seguridad.php");
require_once("../clases/mysql.inc.php");

$db = new mod_db('login');
$pdo = $db->getConexion();
$Usuario = $_SESSION['Usuario'];

$ultimosIntentos = [];
$ultimasAcciones = [];
try {
    $stmt1 = $pdo->query("SELECT Usuario, ipRemoto, deteccion_anomalia, fecha_intento FROM intentos_login ORDER BY id DESC LIMIT 5");
    $ultimosIntentos = $stmt1->fetchAll();

    $stmt2 = $pdo->query("SELECT tabla_afectada, accion, codigo_registro, usuario_sistema, fecha FROM trazabilidad_acciones ORDER BY id DESC LIMIT 5");
    $ultimasAcciones = $stmt2->fetchAll();
} catch (Exception $e) {
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Sistema 2FA Verificado</title>
    <link rel="stylesheet" href="../Estilos/estilo_2fa.css" type="text/css" />
    <style>
        .panel-amplio { max-width: 850px; }
        .grid-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 15px; margin: 20px 0; }
        .card-dato { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 15px; }
        .card-dato h4 { margin: 0 0 8px; color: #1e3a8a; font-size: 0.95rem; }
        .card-dato p { margin: 4px 0; font-size: 0.88rem; color: #334155; }
        .tabla-auditoria { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.85rem; }
        .tabla-auditoria th, .tabla-auditoria td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        .tabla-auditoria th { background: #1e3a8a; color: #fff; }
        .badge-ok { background: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 12px; font-weight: bold; font-size: 0.8rem; }
        .barra-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #e2e8f0; }
        .btn-salir { background: #dc2626; color: #fff; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="tarjeta-sistema panel-amplio">
        <div class="barra-nav">
            <div>
                <h2 style="margin:0; color:#1e3a8a;">Panel de Control | UTP - FISC</h2>
                <small style="color:#64748b;">Bienvenido, <strong><?= htmlspecialchars(strtoupper($Usuario)) ?></strong></small>
            </div>
            <div style="display:flex; gap:12px; align-items:center;">
                <a href="../ver_privilegios.php" style="color:#1d4ed8; font-weight:600; font-size:0.9rem; text-decoration:none;">Ver Privilegios BD</a>
                <a href="../salir.php" class="btn-salir">Cerrar Sesión</a>
            </div>
        </div>

        <div class="mensaje-exito">
            <strong>Acceso Total Concedido:</strong> Has superado exitosamente la validación de credenciales, el token Anti-CSRF y el segundo factor de autenticación (TOTP).
        </div>

        <div class="grid-info">
            <div class="card-dato">
                <h4>Estado de Sesiones Activas</h4>
                <p><strong>Sesión Principal:</strong> <span class="badge-ok"><?= htmlspecialchars($_SESSION['autenticado']) ?></span></p>
                <p><strong>Sesión Verificación 2FA:</strong> <span class="badge-ok"><?= isset($_SESSION['sesion_2fa_completada']) ? 'VERIFICADA' : 'ACTIVA' ?></span></p>
                <p><strong>Sesión Solicitud QR:</strong> <span class="badge-ok"><?= isset($_SESSION['qr_solicitado_exitosamente']) ? 'CONFIRMADA' : 'VINCULADA' ?></span></p>
            </div>
            <div class="card-dato">
                <h4>Seguridad Aplicada</h4>
                <p><strong>Usuario BD Activo:</strong> <code>app_login_2fa</code> (Privilegios mínimos)</p>
                <p><strong>Algoritmo Hash:</strong> <code>PASSWORD_BCRYPT (Cost 13)</code></p>
                <p><strong>Protección CSRF:</strong> Token sincronizado activo</p>
            </div>
        </div>

        <h3 style="color:#1e3a8a; font-size:1.05rem; margin-top:20px;">Auditoría: Intentos de Login (<code>intentos_login</code>)</h3>
        <table class="tabla-auditoria">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>IP Remota</th>
                    <th>Resultado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($ultimosIntentos)): ?>
                    <?php foreach ($ultimosIntentos as $int): ?>
                        <tr>
                            <td><?= htmlspecialchars($int['Usuario']) ?></td>
                            <td><?= htmlspecialchars($int['ipRemoto']) ?></td>
                            <td><?= $int['deteccion_anomalia'] == 1 ? '<span class="badge-ok">Exitoso (1)</span>' : 'Fallido (0)' ?></td>
                            <td><?= htmlspecialchars($int['fecha_intento']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">Sin registros recientes.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h3 style="color:#1e3a8a; font-size:1.05rem; margin-top:20px;">Bitácora: Trazabilidad de Acciones (<code>trazabilidad_acciones</code>)</h3>
        <table class="tabla-auditoria">
            <thead>
                <tr>
                    <th>Tabla</th>
                    <th>Acción</th>
                    <th>ID Registro</th>
                    <th>Usuario</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($ultimasAcciones)): ?>
                    <?php foreach ($ultimasAcciones as $acc): ?>
                        <tr>
                            <td><?= htmlspecialchars($acc['tabla_afectada']) ?></td>
                            <td><?= htmlspecialchars($acc['accion']) ?></td>
                            <td><?= htmlspecialchars($acc['codigo_registro']) ?></td>
                            <td><?= htmlspecialchars($acc['usuario_sistema']) ?></td>
                            <td><?= htmlspecialchars($acc['fecha']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">Sin acciones registradas aún.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
