<?php
/**
 * Email System Test Page
 * URL: http://localhost:8080/test_email.php
 * 
 * This page helps test email functionality without going through registration/order flows
 */

// Load Composer autoloader (for PHPMailer)
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Services/EmailService.php';
require_once __DIR__ . '/../app/Helpers/email_helpers.php';

// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load .env configuration
$envPath = __DIR__ . '/../.env';
$env = [];
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath, false, INI_SCANNER_RAW);
}

$message = '';
$messageType = '';

// Handle test email sending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailType = $_POST['email_type'] ?? '';
    $toEmail = $_POST['to_email'] ?? '';
    
    if (empty($toEmail)) {
        $message = 'Please enter an email address';
        $messageType = 'error';
    } else {
        try {
            $emailService = new App\Services\EmailService();
            $result = false;
            $errorDetails = '';
            
            switch ($emailType) {
                case 'verification':
                    $token = generateToken(32);
                    $user = [
                        'id' => 999,
                        'name' => 'Test User',
                        'email' => $toEmail
                    ];
                    $result = $emailService->sendVerification($user, $token);
                    break;
                    
                case 'password_reset':
                    $token = generateToken(32);
                    $user = [
                        'id' => 999,
                        'name' => 'Test User',
                        'email' => $toEmail
                    ];
                    $result = $emailService->sendPasswordReset($user, $token);
                    break;
                    
                case 'order_confirmation':
                    $order = [
                        'id' => 999,
                        'order_date' => date('Y-m-d H:i:s'),
                        'customer_name' => 'Test Customer',
                        'customer_email' => $toEmail,
                        'shipping_phone' => '0123456789',
                        'shipping_address' => '123 Test Street, Test City',
                        'total_amount' => 500000,
                        'discount_amount' => 50000,
                        'voucher_id' => 1,
                        'voucher_code' => '10PT',
                        'items' => [
                            [
                                'product_name' => 'Test Product 1',
                                'quantity' => 2,
                                'unit_price' => 150000
                            ],
                            [
                                'product_name' => 'Test Product 2',
                                'quantity' => 1,
                                'unit_price' => 250000
                            ]
                        ]
                    ];
                    $result = $emailService->sendOrderConfirmation($order);
                    break;
                    
                case 'order_accepted':
                    $order = [
                        'id' => 999,
                        'order_date' => date('Y-m-d H:i:s'),
                        'customer_name' => 'Test Customer',
                        'customer_email' => $toEmail,
                        'total_amount' => 500000
                    ];
                    $result = $emailService->sendOrderStatusUpdate($order, 'pending', 'accepted');
                    break;
                    
                case 'order_cancelled':
                    $order = [
                        'id' => 999,
                        'order_date' => date('Y-m-d H:i:s'),
                        'customer_name' => 'Test Customer',
                        'customer_email' => $toEmail,
                        'total_amount' => 500000
                    ];
                    $result = $emailService->sendOrderStatusUpdate($order, 'pending', 'cancelled');
                    break;
            }
            
            if ($result) {
                $message = "✅ Email sent successfully to $toEmail";
                $messageType = 'success';
            } else {
                // Get error details from Docker logs
                $dockerLogs = shell_exec('docker logs shopifyt-php-1 2>&1 | grep -i "email\|smtp\|phpmailer" | tail -10');
                
                if (!empty($dockerLogs)) {
                    $errorDetails = "Recent Docker logs:\n" . $dockerLogs;
                } else {
                    $errorDetails = "No detailed error logs found.\n\nCommon issues:\n";
                    $errorDetails .= "1. SMTP authentication failed - check MAIL_USERNAME and MAIL_PASSWORD\n";
                    $errorDetails .= "2. Invalid SMTP credentials - verify Brevo SMTP key\n";
                    $errorDetails .= "3. Network issue - check internet connection\n";
                    $errorDetails .= "4. Port blocked - try port 465 with SSL instead of 587 with TLS\n";
                }
                
                // Add configuration info for debugging
                $errorDetails .= "\n\nCurrent SMTP Config:\n";
                $errorDetails .= "Host: " . ($env['MAIL_HOST'] ?? 'Not set') . "\n";
                $errorDetails .= "Port: " . ($env['MAIL_PORT'] ?? 'Not set') . "\n";
                $errorDetails .= "Username: " . (!empty($env['MAIL_USERNAME']) ? 'Set (***' . substr($env['MAIL_USERNAME'], -5) . ')' : 'Not set') . "\n";
                $errorDetails .= "Password: " . (!empty($env['MAIL_PASSWORD']) ? 'Set (' . strlen($env['MAIL_PASSWORD']) . ' chars)' : 'Not set') . "\n";
                $errorDetails .= "Encryption: " . ($env['MAIL_ENCRYPTION'] ?? 'Not set') . "\n";
                
                $message = "❌ Failed to send email. Check error details below.";
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = "❌ Error: " . $e->getMessage();
            $errorDetails = "Exception Details:\n" . $e->getMessage() . "\n\n";
            $errorDetails .= "Stack trace:\n" . $e->getTraceAsString();
            $messageType = 'error';
        }
    }
}

// Check SMTP configuration status
$smtpConfigured = !empty($env['MAIL_USERNAME']) && !empty($env['MAIL_PASSWORD']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email System Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        button { background: #007bff; color: white; padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #0056b3; }
        .config-status { padding: 15px; background: #f8f9fa; border-radius: 5px; margin-bottom: 20px; }
        .config-item { margin: 5px 0; }
        .status-ok { color: #28a745; }
        .status-missing { color: #dc3545; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email System Test Tool</h1>
        
        <?php if (!$smtpConfigured): ?>
            <div class="alert alert-warning">
                <strong>⚠️ SMTP Not Configured!</strong><br>
                Please add email credentials to <code>.env</code> file.
            </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
            
            <?php if (!empty($errorDetails)): ?>
                <div class="alert alert-warning">
                    <strong>📋 Error Details:</strong>
                    <pre style="background: #fff; padding: 10px; border-radius: 4px; margin-top: 10px; overflow: auto; max-height: 200px; font-size: 12px;"><?php echo htmlspecialchars($errorDetails); ?></pre>
                    <p style="margin-top: 10px; font-size: 13px;">💡 <strong>Tip:</strong> Check SMTP credentials and Brevo account status.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="config-status">
            <h3>SMTP Configuration Status</h3>
            <div class="config-item">
                <strong>Host:</strong> 
                <span class="<?php echo !empty($env['MAIL_HOST']) ? 'status-ok' : 'status-missing'; ?>">
                    <?php echo $env['MAIL_HOST'] ?? '❌ Not set'; ?>
                </span>
            </div>
            <div class="config-item">
                <strong>Port:</strong> 
                <span class="<?php echo !empty($env['MAIL_PORT']) ? 'status-ok' : 'status-missing'; ?>">
                    <?php echo $env['MAIL_PORT'] ?? '❌ Not set'; ?>
                </span>
            </div>
            <div class="config-item">
                <strong>Username:</strong> 
                <span class="<?php echo !empty($env['MAIL_USERNAME']) ? 'status-ok' : 'status-missing'; ?>">
                    <?php echo !empty($env['MAIL_USERNAME']) ? '✓ Configured' : '❌ Not set'; ?>
                </span>
            </div>
            <div class="config-item">
                <strong>Password:</strong> 
                <span class="<?php echo !empty($env['MAIL_PASSWORD']) ? 'status-ok' : 'status-missing'; ?>">
                    <?php echo !empty($env['MAIL_PASSWORD']) ? '✓ Configured' : '❌ Not set'; ?>
                </span>
            </div>
            <div class="config-item">
                <strong>From Address:</strong> 
                <span class="<?php echo !empty($env['MAIL_FROM_ADDRESS']) ? 'status-ok' : 'status-missing'; ?>">
                    <?php echo $env['MAIL_FROM_ADDRESS'] ?? '❌ Not set'; ?>
                </span>
            </div>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="email_type">Email Type:</label>
                <select name="email_type" id="email_type" required>
                    <option value="">-- Select Email Type --</option>
                    <option value="verification">Account Verification</option>
                    <option value="password_reset">Password Reset</option>
                    <option value="order_confirmation">Order Confirmation</option>
                    <option value="order_accepted">Order Accepted</option>
                    <option value="order_cancelled">Order Cancelled</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="to_email">Recipient Email:</label>
                <input type="email" name="to_email" id="to_email" placeholder="test@example.com" required>
            </div>
            
            <button type="submit">Send Test Email</button>
        </form>
        
        <table>
            <thead>
                <tr>
                    <th>Email Type</th>
                    <th>Description</th>
                    <th>Token Expiry</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Verification</td>
                    <td>Sent after user registration</td>
                    <td>24 hours</td>
                </tr>
                <tr>
                    <td>Password Reset</td>
                    <td>Sent when user requests password reset</td>
                    <td>1 hour</td>
                </tr>
                <tr>
                    <td>Order Confirmation</td>
                    <td>Sent after successful order placement</td>
                    <td>N/A</td>
                </tr>
                <tr>
                    <td>Order Status</td>
                    <td>Sent when admin changes order status</td>
                    <td>N/A</td>
                </tr>
            </tbody>
        </table>
        
        <div style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-radius: 5px;">
            <strong>💡 Tips:</strong>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Use your own email to test</li>
                <li>Check spam folder if email doesn't arrive</li>
                <li>Verify SMTP credentials are correct in .env</li>
                <li>Check server logs for detailed error messages</li>
            </ul>
        </div>
    </div>
</body>
</html>
