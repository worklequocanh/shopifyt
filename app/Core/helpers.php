<?php

/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

/**
 * Format number to Vietnamese currency
 */
function format_currency(float $number): string
{
    return number_format($number, 0, ',', '.') . 'đ';
}

/**
 * Sanitize user input
 */
function sanitize_input(string $data): string
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Redirect to a URL
 */
function redirect(string $url): void
{
    // If URL starts with /, it's already absolute
    if (strpos($url, '/') === 0) {
        header("Location: {$url}");
    } else {
        header("Location: /{$url}");
    }
    exit();
}

/**
 * Set a flash message in session
 */
function setFlashMessage(string $type, string $message): void
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get flash message (without displaying)
 */
function getFlashMessage(): ?array
{
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['id']) && isset($_SESSION['role']);
}

/**
 * Get current user ID
 */
function getCurrentUserId(): ?int
{
    return $_SESSION['id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole(): ?string
{
    return $_SESSION['role'] ?? null;
}

/**
 * Check if current user has a specific role
 */
function hasRole(string $role): bool
{
    return getCurrentUserRole() === $role;
}

/**
 * Escape HTML output
 */
function e($value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get base URL
 */
function baseUrl($path = ''): string
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    
    if ($path && strpos($path, '/') !== 0) {
        $path = '/' . $path;
    }
    
    return $protocol . '://' . $host . $basePath . $path;
}

/**
 * Get asset URL
 */
function asset($path): string
{
    return baseUrl('/assets/' . ltrim($path, '/'));
}
