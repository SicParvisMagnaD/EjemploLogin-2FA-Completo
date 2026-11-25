# 🔐 Login con Autenticación de Dos Factores (2FA) en PHP

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-D22128?style=for-the-badge&logo=apache&logoColor=white)
![WampServer](https://img.shields.io/badge/WampServer-FF0040?style=for-the-badge&logo=windows&logoColor=white)
![Composer](https://img.shields.io/badge/Composer-885630?style=for-the-badge&logo=composer&logoColor=white)
![OWASP](https://img.shields.io/badge/OWASP-Authentication%20Failures-1EA362?style=for-the-badge)

Sistema de **registro e inicio de sesión con doble factor de autenticación (2FA)** usando contraseñas con hash Bcrypt y códigos temporales (TOTP) de **Google Authenticator**. Incluye protección Anti-CSRF, un usuario de base de datos con privilegios mínimos, auditoría de intentos de login y una bitácora de acciones. Proyecto de la materia *Desarrollo Web VII: PHP y MySQL* (Licenciatura en Ciberseguridad, Universidad Tecnológica de Panamá).

## 📌 ¿De qué trata?

Una contraseña sola no basta: si se filtra o se adivina, la cuenta queda expuesta. El 2FA agrega un segundo factor, algo que solo tiene el usuario (su celular), de modo que aunque alguien conozca la contraseña no puede entrar sin el código temporal. El flujo completo es:

1. **Registro** (`registro.php`): el usuario llena el formulario, se guarda con la contraseña hasheada y se genera un secreto 2FA único.
2. **Código QR:** al registrarse se muestra un QR que el usuario escanea con Google Authenticator.
3. **Login** (`login.php`): usuario o correo y contraseña, con token Anti-CSRF.
4. **Verificación 2FA** (`verificar_2fa.php`): el usuario escribe el código de 6 dígitos de Google Authenticator.
5. **Panel de control** (`formularios/PanelControl.php`): solo se accede si se superaron los tres controles (credenciales, token Anti-CSRF y TOTP).

## ✨ Características de seguridad

| Medida | Cómo se aplica |
|---|---|
| **Hash de contraseñas** | Bcrypt con costo 13 (`GestorHashBcrypt`). La contraseña nunca se guarda en texto plano. |
| **2FA con TOTP** | Secreto por usuario y validación del código con `sonata-project/google-authenticator` (`Gestor2FA`). |
| **Protección CSRF** | Token en cada formulario, verificado en el servidor y regenerado tras el registro (`CSRFProtection`). |
| **Sanitización de entradas** | Limpieza de nombre, apellido, usuario, correo y sexo antes de procesarlos (`SanitizarEntrada`). |
| **Validación en dos capas** | Frontend (JavaScript y atributos HTML) y backend (`FormularioRegistro`). |
| **Control de duplicados** | Se comprueba que el usuario y el correo no existan antes de guardar. |
| **Usuario de BD con privilegios mínimos** | La aplicación se conecta con el usuario `app_login_2fa`, que solo tiene los permisos necesarios (no usa `root`). |
| **Auditoría de intentos de login** | Cada intento queda registrado con usuario, IP remota, resultado y fecha en la tabla `intentos_login`. |
| **Bitácora de trazabilidad** | Las acciones sobre las tablas se registran en `trazabilidad_acciones` (tabla, acción, ID del registro, usuario y fecha). |
| **Sesiones separadas** | Sesión principal, sesión de verificación 2FA y sesión de solicitud de QR; el panel muestra el estado de cada una. |
| **Salida escapada** | Los datos que se muestran en pantalla pasan por `htmlspecialchars()` para evitar XSS. |

## 📁 Estructura del proyecto

```text
EjemploLogin-2FA-Completo/
├── capturas/                   Imágenes que se muestran en este README
├── clases/
│   ├── mysql.inc.php           Conexión a la base de datos (clase mod_db)
│   ├── SanitizarEntrada.php    Limpieza de datos de entrada
│   ├── GestorHashBcrypt.php    Hash y verificación de contraseñas
│   ├── Gestor2FA.php           Secreto TOTP, URL del QR y validación del código
│   └── FormularioRegistro.php  Validación, duplicados y guardado del usuario
├── formularios/
│   └── PanelControl.php        Panel protegido: sesiones, auditoría y bitácora
├── utilidades/
│   └── CSRFProtection.php      Generación y verificación del token anti-CSRF
├── Estilos/
│   └── estilo_2fa.css          Estilos de las pantallas
├── registro.php                Formulario de registro y muestra del código QR
├── login.php                   Inicio de sesión (usuario o correo y contraseña)
├── verificar_2fa.php           Verificación del código de 6 dígitos
├── composer.json               Dependencias (se instalan con Composer)
└── README.md