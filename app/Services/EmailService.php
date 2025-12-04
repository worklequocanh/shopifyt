<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Email Service
 * Centralized email sending service using PHPMailer and Brevo SMTP
 */
class EmailService
{
    private PHPMailer $mailer;
    private string $fromAddress;
    private string $fromName;
    private string $appUrl;
    
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        
        // Load .env configuration (same as database.php)
        $envPath = __DIR__ . '/../../.env';
        $env = [];
        if (file_exists($envPath)) {
            $env = parse_ini_file($envPath, false, INI_SCANNER_RAW);
        }
        
        // Set configuration from .env (with trim to avoid whitespace issues)
        $this->fromAddress = trim($env['MAIL_FROM_ADDRESS'] ?? 'noreply@shopifyt.com');
        $this->fromName = trim($env['MAIL_FROM_NAME'] ?? 'Shopifyt');
        $this->appUrl = trim($env['APP_URL'] ?? 'http://localhost:8080');
        
        // Configure SMTP with .env values
        $this->configureSMTP($env);
    }
    
    /**
     * Configure SMTP settings
     */
    private function configureSMTP(array $env): void
    {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = trim($env['MAIL_HOST'] ?? 'smtp-relay.brevo.com');
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = trim($env['MAIL_USERNAME'] ?? '');
            $this->mailer->Password = trim($env['MAIL_PASSWORD'] ?? '');
            $this->mailer->SMTPSecure = trim($env['MAIL_ENCRYPTION'] ?? 'tls') === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = $env['MAIL_PORT'] ?? 587;
            $this->mailer->CharSet = 'UTF-8';
            
            // Force LOGIN authentication method for Brevo
            $this->mailer->AuthType = 'LOGIN';
            
            // Debugging - set to 0 for production, 2 for troubleshooting
            $this->mailer->SMTPDebug = 0; // Changed back to 0 now that connection works
            $this->mailer->Debugoutput = function($str, $level) {
                error_log("PHPMailer [$level]: $str");
            };
        } catch (Exception $e) {
            error_log("SMTP Configuration Error: " . $e->getMessage());
        }
    }
    
    /**
     * Send email with template
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $template Template file name (without .php)
     * @param array $data Data to pass to template
     * @return bool Success status
     */
    public function send(string $to, string $subject, string $template, array $data = []): bool
    {
        try {
            // Reset mailer for new email
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Set sender and recipient
            $this->mailer->setFrom($this->fromAddress, $this->fromName);
            $this->mailer->addAddress($to);
            
            // Set subject
            $this->mailer->Subject = $subject;
            
            // Render template
            $html = $this->renderTemplate($template, $data);
            
            // Set HTML body
            $this->mailer->isHTML(true);
            $this->mailer->Body = $html;
            
            // Send
            $result = $this->mailer->send();
            
            error_log("Email sent successfully to: $to - Subject: $subject");
            return $result;
            
        } catch (Exception $e) {
            $errorMsg = "Email sending failed to $to: " . $e->getMessage();
            error_log($errorMsg);
            
            // Also log SMTP errors if available
            if ($this->mailer->ErrorInfo) {
                error_log("PHPMailer Error Info: " . $this->mailer->ErrorInfo);
            }
            
            return false;
        }
    }
    
    /**
     * Render email template
     * 
     * @param string $template Template name
     * @param array $data Template data
     * @return string Rendered HTML
     */
    private function renderTemplate(string $template, array $data): string
    {
        // Extract data to variables
        extract($data);
        
        // Add common variables
        $appUrl = $this->appUrl;
        $appName = $this->fromName;
        
        // Start output buffering
        ob_start();
        
        // Template path
        $templatePath = __DIR__ . '/../Views/emails/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            error_log("Email template not found: $templatePath");
            return "<p>Email template not found.</p>";
        }
        
        // Include template
        include $templatePath;
        
        // Get content
        $content = ob_get_clean();
        
        // Wrap in layout
        return $this->wrapInLayout($content);
    }
    
    /**
     * Wrap content in email layout
     */
    private function wrapInLayout(string $content): string
    {
        $appName = $this->fromName;
        $appUrl = $this->appUrl;
        $year = date('Y');
        
        return <<<HTML
                <!DOCTYPE html>
                <html lang="vi">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Email from {$appName}</title>
                    <style>
                        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
                        .email-container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                        .email-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px 20px; text-align: center; color: white; }
                        .email-header h1 { margin: 0; font-size: 24px; }
                        .email-body { padding: 30px 20px; color: #333333; line-height: 1.6; }
                        .email-footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
                        .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 10px 0; }
                        .btn:hover { background: #5568d3; }
                        a { color: #667eea; }
                    </style>
                </head>
                <body>
                    <div class="email-container">
                        <div class="email-header">
                            <h1>{$appName}</h1>
                        </div>
                        <div class="email-body">
                            {$content}
                        </div>
                        <div class="email-footer">
                            <p>&copy; {$year} {$appName}. All rights reserved.</p>
                            <p><a href="{$appUrl}">Visit our website</a></p>
                        </div>
                    </div>
                </body>
                </html>
                HTML;
    }
    
    /**
     * Send verification email
     */
    public function sendVerification(array $user, string $token): bool
    {
        $verifyUrl = $this->appUrl . '/auth/verify/' . $token;
        
        return $this->send(
            $user['email'],
            'Xác nhận tài khoản của bạn',
            'verification',
            [
                'name' => $user['name'],
                'verifyUrl' => $verifyUrl
            ]
        );
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordReset(array $user, string $token): bool
    {
        $resetUrl = $this->appUrl . '/auth/reset-password/' . $token;
        
        return $this->send(
            $user['email'],
            'Đặt lại mật khẩu',
            'password_reset',
            [
                'name' => $user['name'],
                'resetUrl' => $resetUrl
            ]
        );
    }
    
    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmation(array $order): bool
    {
        return $this->send(
            $order['customer_email'] ?? $order['email'],
            'Xác nhận đơn hàng #' . str_pad($order['id'], 6, '0', STR_PAD_LEFT),
            'order_confirmation',
            [
                'order' => $order
            ]
        );
    }
    
    /**
     * Send order status update email
     */
    public function sendOrderStatusUpdate(array $order, string $oldStatus, string $newStatus): bool
    {
        $subject = $newStatus === 'accepted' ? 
            'Đơn hàng đã được xác nhận' : 
            'Đơn hàng đã bị hủy';
        
        return $this->send(
            $order['customer_email'] ?? $order['email'],
            $subject . ' #' . str_pad($order['id'], 6, '0', STR_PAD_LEFT),
            'order_status',
            [
                'order' => $order,
                'oldStatus' => $oldStatus,
                'newStatus' => $newStatus
            ]
        );
    }
}
