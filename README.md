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
```

## 🛠️ Requisitos

- Windows con **WampServer** (Apache + PHP 8.x + MySQL) o XAMPP.
- **Composer** instalado.
- La aplicación **Google Authenticator** en el celular.
- Conexión a internet (el QR se genera con el servicio `api.qrserver.com`).

## 🚀 Instalación

1. Copia o clona el proyecto dentro de la carpeta `www` de WampServer (en mi caso, dentro de `www\practicas`):

   ```bash
   cd C:\wamp64\www\practicas
   git clone <URL-DEL-REPOSITORIO> EjemploLogin-2FA-Completo
   ```

2. Instala las dependencias con Composer (esto crea la carpeta `vendor/`):

   ```bash
   cd EjemploLogin-2FA-Completo
   composer install
   ```

   Si no hay `composer.json`, instala la librería directamente:

   ```bash
   composer require sonata-project/google-authenticator
   ```

3. **Base de datos:** en phpMyAdmin (`http://localhost/phpmyadmin`) crea la base de datos `registro` e importa el script SQL del proyecto (tabla de usuarios, `intentos_login` y `trazabilidad_acciones`).

4. **Usuario con privilegios mínimos:** crea el usuario `app_login_2fa` y dale solo lo que la aplicación necesita, en lugar de usar `root`. Ejemplo (ajusta las tablas y permisos a tu proyecto):

   ```sql
   CREATE USER 'app_login_2fa'@'localhost' IDENTIFIED BY 'TU_CONTRASENA_SEGURA';
   GRANT SELECT, INSERT, UPDATE ON registro.* TO 'app_login_2fa'@'localhost';
   FLUSH PRIVILEGES;
   ```

   Después escribe ese usuario y su contraseña en `clases/mysql.inc.php`.

5. Inicia Apache y MySQL desde WampServer.

6. Abre el sistema en el navegador:

   ```text
   http://localhost/practicas/EjemploLogin-2FA-Completo/registro.php
   ```

> **Importante:** la carpeta `vendor/` no se sube al repositorio. Quien clone el proyecto debe ejecutar `composer install`.

## 🧪 Cómo se usa

### 1. Registro de usuario

Abre `registro.php` y llena el formulario (nombre, apellido, usuario, correo, sexo y contraseña con confirmación). La contraseña debe tener al menos 8 caracteres y las dos contraseñas deben coincidir; esto se valida primero en el navegador y luego en el servidor.

<!-- 📸 CAPTURA 1: formulario de registro. -->
![Formulario de registro](capturas/01_registro_formulario.png)

### 2. Código QR

Si el registro es correcto, la página muestra el código QR y la clave manual. Para activarlo:

1. Abre **Google Authenticator** en el celular.
2. Toca **+ → Escanear código QR** y escanea la imagen desde dentro de la aplicación.
3. Si no puedes escanear, toca **+ → Ingresar clave de configuración** y escribe el "Secreto manual" que aparece en la página.

<!-- 📸 CAPTURA 2: página con el QR (tapa el secreto antes de publicar). -->
![Código QR para Google Authenticator](capturas/02_registro_qr.png)

<!-- 📸 CAPTURA 3: Google Authenticator mostrando el código de 6 dígitos. -->
![Google Authenticator con el código](capturas/03_google_authenticator.png)

### 3. Inicio de sesión

En `login.php` escribe tu usuario o correo y tu contraseña. La pantalla también ofrece el enlace para registrarse y generar el código QR, y otro para ver los usuarios y privilegios de la base de datos.

<!-- 📸 CAPTURA 4: pantalla de login vacía. -->
![Pantalla de login](capturas/04_login.png)

<!-- 📸 CAPTURA 5: login con las credenciales escritas. -->
![Login con credenciales](capturas/05_login_credenciales.png)

### 4. Verificación en dos pasos (2FA)

Si las credenciales son correctas, `verificar_2fa.php` pide el código de 6 dígitos de Google Authenticator, que cambia cada 30 segundos. Desde aquí también se puede volver a escanear el QR o cancelar y regresar al login.

<!-- 📸 CAPTURA 6: pantalla de verificación 2FA. -->
![Verificación en dos pasos](capturas/06_verificar_2fa.png)

### 5. Panel de control

Con el código correcto se entra al panel. Muestra el estado de las tres sesiones (principal, verificación 2FA y solicitud de QR), la seguridad aplicada (usuario de BD con privilegios mínimos, Bcrypt costo 13 y token CSRF sincronizado), la tabla de **auditoría de intentos de login** y la **bitácora de trazabilidad de acciones**.

<!-- 📸 CAPTURA 7: panel de control. -->
![Panel de control](capturas/07_panel_control.png)

## 🧰 Solución de problemas

| Problema | Causa y solución |
|---|---|
| `Failed to open stream: ... vendor/autoload.php` | Falta la carpeta `vendor/`. Ejecuta `composer install` en la carpeta del proyecto. |
| `Class "..." not found` | Falta un `require_once` de la clase en el archivo, o el nombre del archivo o de la clase no coincide. |
| El QR abre una página web en vez de agregar la cuenta | El QR debe contener un enlace `otpauth://`. Escanea desde dentro de Google Authenticator y verifica que `Gestor2FA::generarUrlQR()` arme el `otpauth://totp/...` correctamente. |
| El código 2FA siempre es inválido | El reloj del celular o del servidor está desfasado. Activa la hora automática en el celular. |
| No se genera la imagen del QR | `api.qrserver.com` necesita internet. Si falla, usa la clave manual. |
| Error de conexión a la base de datos | Revisa el usuario `app_login_2fa`, su contraseña y sus privilegios en `clases/mysql.inc.php` y en MySQL. |

## 🔒 Notas de seguridad

- **No publiques secretos 2FA.** Con el secreto cualquiera puede generar los códigos de esa cuenta. En las capturas usa usuarios de prueba o tapa el QR y la clave.
- **No subas credenciales.** `mysql.inc.php` contiene la contraseña de la base de datos: agrégalo al `.gitignore` o súbelo solo con datos de prueba.
- **Agrega `vendor/` al `.gitignore`** para no subir las dependencias.
- **Enlace "Ver Usuarios y Privilegios de BD".** Muestra información interna de la base de datos; es útil para la práctica, pero en un sistema real no debe estar expuesto en la pantalla de login.
- **QR externo.** El servicio `api.qrserver.com` recibe el secreto dentro de la URL del QR. Para un sistema real conviene generar el QR localmente (por ejemplo con `endroid/qr-code`).
- **Producción.** Este proyecto es académico: en un sistema real usa HTTPS, guarda el secreto 2FA cifrado y limita los intentos de inicio de sesión.

## 📚 Referencias

- [OWASP Top 10: A07:2021 Identification and Authentication Failures](https://owasp.org/Top10/A07_2021-Identification_and_Authentication_Failures/)
- [OWASP Password Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)
- [OWASP Cross-Site Request Forgery Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)
- [Manual de PHP: password_hash](https://www.php.net/manual/es/function.password-hash.php)
- [sonata-project/google-authenticator](https://github.com/sonata-project/GoogleAuthenticator)

## 👨‍💻 Autor

**Javier Vallarino** (8-1040-1488), grupo 1S3122
Licenciatura en Ciberseguridad, Facultad de Ingeniería de Sistemas Computacionales
Universidad Tecnológica de Panamá
📧 javier.vallarino@utp.ac.pa

**Facilitadora:** Ing. Irina Fong       
