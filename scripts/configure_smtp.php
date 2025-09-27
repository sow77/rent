<?php
// scripts/configure_smtp.php

require_once __DIR__ . '/../config/EmailService.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/config.php';

// Cargar posibles credenciales base
$creds = [];
$prodFile = __DIR__ . '/../config/production_credentials.php';
if (file_exists($prodFile)) {
	$creds = require $prodFile;
}

$smtpUsername = $creds['smtp_username'] ?? getenv('SMTP_USERNAME') ?? '';
$smtpPassword = $creds['smtp_password'] ?? getenv('SMTP_PASSWORD') ?? '';

// Normalizar password (el app password de Gmail no lleva espacios)
$smtpPassword = preg_replace('/\s+/', '', $smtpPassword);

if (empty($smtpUsername) || empty($smtpPassword)) {
	echo "ERROR: Faltan SMTP_USERNAME o SMTP_PASSWORD.\n";
	exit(1);
}

$host = 'smtp.gmail.com';
$port = 587;
$encryption = 'tls';
$fromName = 'Dev Rent';
$fromEmail = $smtpUsername; // usar el mismo usuario de Gmail
$replyTo = $smtpUsername;   // mismo reply-to válido

$ok = EmailService::configureSMTP($host, $port, $smtpUsername, $smtpPassword, $encryption, $fromName, $fromEmail, $replyTo);

if ($ok) {
	echo "OK: SMTP configurado en system_config.\n";
} else {
	echo "ERROR: No se pudo configurar SMTP.\n";
	exit(1);
}


