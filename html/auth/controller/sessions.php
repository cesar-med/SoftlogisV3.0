<?php

require_once __DIR__ . "/authController.php";

session_start();

$auth = new authController();
$pathLogin = $auth->pathLogin;
$user = $_SESSION["user"];

if (!isset($user['jwt'])) {
    header("Location: $pathLogin");
    exit;
}

$valid = $auth->validateJWT($user['jwt']);

if (!$valid['result']) {
    session_destroy();
    header("Location: $pathLogin");
    exit;
}

$decoded = $valid['token'];

// Validar que la sesión aún esté activa en el sistema
$isActive = $auth->isSessionActive($user['user_id'], $user['jwt']);
if (!$isActive) {
    session_destroy();
    header("Location: $pathLogin");
    exit;
}
