<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions/auth_functions.php';

echo "Welcome to the admin dashboard, " . htmlspecialchars($_SESSION['name']) . "!" . htmlspecialchars($_SESSION['role']);