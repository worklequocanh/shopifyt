<?php
session_start();
include_once("includes/config.php");
include_once("includes/auth.php");
include_once("includes/functions.php");

restrictToRoles($pdo, ['admin', 'employee'], redirectIfNotLoggedIn: '/login.php');
