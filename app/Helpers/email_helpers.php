<?php
/**
 * Email Helper Functions
 * Auto-require EmailService when using these functions
 */

/**
 * Get EmailService instance
 */
function getEmailService(): \App\Services\EmailService
{
    static $instance = null;
    
    if ($instance === null) {
        // Load Composer autoloader for PHPMailer
        require_once __DIR__ . '/../../vendor/autoload.php';
        // Load EmailService (correct path from Helpers/)
        require_once __DIR__ . '/../Services/EmailService.php';
        $instance = new \App\Services\EmailService();
    }
    
    return $instance;
}

/**
 * Send email helper
 */
function sendEmail(string $to, string $subject, string $template, array $data = []): bool
{
    return getEmailService()->send($to, $subject, $template, $data);
}

/**
 * Generate secure token
 */
function generateToken(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

/**
 * Hash token for database storage
 */
function hashToken(string $token): string
{
    return hash('sha256', $token);
}
