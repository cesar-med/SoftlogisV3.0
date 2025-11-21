<?php

require_once __DIR__ . "/vendor/autoload.php";

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dashboard = $_ENV['DASHBOARD'];
header("Location: $dashboard");
exit();
