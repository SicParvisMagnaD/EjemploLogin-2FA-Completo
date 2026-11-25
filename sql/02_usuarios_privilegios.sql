-- ============================================================================
-- CREACIÓN DE USUARIOS MYSQL CON PRIVILEGIOS MÍNIMOS (NO USAR ROOT)
-- Criterio 1 de la Rúbrica (15 pts)
-- ============================================================================

DROP USER IF EXISTS 'app_login_2fa'@'localhost';
DROP USER IF EXISTS 'app_registro_2fa'@'localhost';

-- Usuario con privilegios mínimos para iniciar sesión y registrar auditoría
CREATE USER 'app_login_2fa'@'localhost' IDENTIFIED BY 'Login2FA_UTP#2026';
GRANT SELECT, UPDATE ON company_info.usuarios TO 'app_login_2fa'@'localhost';
GRANT INSERT ON company_info.intentos_login TO 'app_login_2fa'@'localhost';
GRANT INSERT ON company_info.trazabilidad_acciones TO 'app_login_2fa'@'localhost';

-- Usuario con privilegios mínimos para registrar nuevos usuarios y generar 2FA
CREATE USER 'app_registro_2fa'@'localhost' IDENTIFIED BY 'Reg2FA_UTP#2026';
GRANT SELECT, INSERT, UPDATE ON company_info.usuarios TO 'app_registro_2fa'@'localhost';
GRANT INSERT ON company_info.trazabilidad_acciones TO 'app_registro_2fa'@'localhost';

FLUSH PRIVILEGES;

-- ============================================================================
-- COMANDOS PARA MOSTRAR USUARIOS CREADOS Y PRIVILEGIOS EN PANTALLA (RÚBRICA)
-- ============================================================================
SELECT User, Host FROM mysql.user WHERE User LIKE 'app_%';
SHOW GRANTS FOR 'app_login_2fa'@'localhost';
SHOW GRANTS FOR 'app_registro_2fa'@'localhost';
