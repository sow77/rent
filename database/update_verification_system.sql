-- Actualizar tabla users para sistema de verificación
ALTER TABLE users 
ADD COLUMN email_verified_at TIMESTAMP NULL,
ADD COLUMN phone_verified_at TIMESTAMP NULL,
ADD COLUMN verification_token VARCHAR(255) NULL,
ADD COLUMN verification_token_expires_at TIMESTAMP NULL,
ADD COLUMN phone_verification_code VARCHAR(6) NULL,
ADD COLUMN phone_verification_expires_at TIMESTAMP NULL,
ADD COLUMN registration_ip VARCHAR(45) NULL,
ADD COLUMN registration_user_agent TEXT NULL,
ADD COLUMN recaptcha_score DECIMAL(3,2) NULL,
ADD COLUMN account_status ENUM('pending_email', 'pending_phone', 'active', 'suspended', 'banned') DEFAULT 'pending_email',
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Crear tabla para logs de verificación
CREATE TABLE IF NOT EXISTS verification_logs (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    type ENUM('email', 'phone', 'recaptcha') NOT NULL,
    status ENUM('sent', 'verified', 'failed', 'expired') NOT NULL,
    details JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Crear tabla para configuraciones del sistema
CREATE TABLE IF NOT EXISTS system_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar configuraciones por defecto
INSERT INTO system_config (config_key, config_value, description) VALUES
('email_verification_enabled', '1', 'Habilitar verificación de email'),
('phone_verification_enabled', '1', 'Habilitar verificación de teléfono'),
('recaptcha_enabled', '1', 'Habilitar reCAPTCHA'),
('verification_token_expiry', '3600', 'Tiempo de expiración del token de verificación en segundos'),
('phone_code_expiry', '300', 'Tiempo de expiración del código OTP en segundos'),
('max_verification_attempts', '3', 'Máximo número de intentos de verificación'),
('kickbox_api_key', '', 'API Key de Kickbox para validación de emails'),
('twilio_account_sid', '', 'Account SID de Twilio'),
('twilio_auth_token', '', 'Auth Token de Twilio'),
('twilio_phone_number', '', 'Número de teléfono de Twilio'),
('recaptcha_site_key', '', 'Site Key de reCAPTCHA'),
('recaptcha_secret_key', '', 'Secret Key de reCAPTCHA')
ON DUPLICATE KEY UPDATE config_value = VALUES(config_value);
