<?php
require_once __DIR__ . '/SanitizarEntrada.php';
require_once __DIR__ . '/SeguridadHashInterface.php';
require_once __DIR__ . '/Gestor2FA.php';

/**
 * Clase FormularioRegistro con responsabilidad única por método
 * Criterio 3 (Validación de duplicados) y Criterio 4 (10 pts)
 */
class FormularioRegistro {
    private $db;
    private $gestorHash;
    private $gestor2FA;
    private $errores = [];

    public function __construct(mod_db $db, SeguridadHashInterface $gestorHash, Gestor2FA $gestor2FA) {
        $this->db = $db;
        $this->gestorHash = $gestorHash;
        $this->gestor2FA = $gestor2FA;
    }

    /**
     * Responsabilidad 1: Validar reglas de formato y coincidencia de contraseñas
     */
    public function validarCampos(array $datos) {
        $this->errores = [];

        if (empty($datos['nombre']) || empty($datos['apellido'])) {
            $this->errores[] = "El nombre y el apellido son obligatorios.";
        }
        if (empty($datos['usuario']) || strlen($datos['usuario']) < 4) {
            $this->errores[] = "El usuario debe tener al menos 4 caracteres.";
        }
        if ($datos['correo'] === false) {
            $this->errores[] = "Debe ingresar un correo electrónico válido.";
        }
        if (!in_array($datos['sexo'], ['M', 'F'], true)) {
            $this->errores[] = "Debe seleccionar un sexo válido (M o F).";
        }
        if (strlen($datos['contrasena']) < 8) {
            $this->errores[] = "La contraseña debe tener al menos 8 caracteres.";
        }
        if ($datos['contrasena'] !== $datos['confirmar_contrasena']) {
            $this->errores[] = "Las contraseñas no coinciden.";
        }

        return empty($this->errores);
    }

    /**
     * Responsabilidad 2: Validar que el correo y el usuario no estén duplicados en la BD
     */
    public function existeDuplicado($usuario, $correo) {
        $pdo = $this->db->getConexion();
        $stmt = $pdo->prepare("SELECT Usuario, correo FROM usuarios WHERE Usuario = :usuario OR correo = :correo LIMIT 1");
        $stmt->execute([':usuario' => $usuario, ':correo' => $correo]);
        $fila = $stmt->fetch();

        if ($fila) {
            if (strcasecmp($fila['Usuario'], $usuario) === 0) {
                $this->errores[] = "El usuario '{$usuario}' ya está registrado.";
            }
            if (strcasecmp($fila['correo'], $correo) === 0) {
                $this->errores[] = "El correo '{$correo}' ya está registrado.";
            }
            return true;
        }
        return false;
    }

    /**
     * Responsabilidad 3: Guardar el nuevo usuario con HashBcrypt y Secreto 2FA
     */
    public function guardarUsuario(array $datos) {
        $hash = $this->gestorHash->generarHash($datos['contrasena']);
        $secreto2FA = $this->gestor2FA->generarSecreto();

        $insertado = $this->db->insertSeguro('usuarios', [
            'nombre'     => $datos['nombre'],
            'apellido'   => $datos['apellido'],
            'Usuario'    => $datos['usuario'],
            'correo'     => $datos['correo'],
            'sexo'       => $datos['sexo'],
            'HashMagic'  => $hash,
            'secret_2fa' => $secreto2FA
        ]);

        if (!$insertado) {
            $this->errores[] = "No se pudo guardar el usuario en la base de datos.";
            return false;
        }

        $nuevoId = $this->db->insert_id();
        $this->db->registrarTrazabilidad('usuarios', 'INSERT', $nuevoId, $datos['usuario']);

        return [
            'id'         => $nuevoId,
            'usuario'    => $datos['usuario'],
            'correo'     => $datos['correo'],
            'secret_2fa' => $secreto2FA,
            'qr_url'     => $this->gestor2FA->generarUrlQR($datos['correo'], $secreto2FA)
        ];
    }

    public function getErrores() {
        return $this->errores;
    }
}
?>
