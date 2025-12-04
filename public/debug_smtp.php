<?php
/**
 * Debug SMTP Credentials
 * Check what values are actually being loaded from .env
 */

// Load .env
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    die("❌ .env file not found!");
}

$env = parse_ini_file($envPath, false, INI_SCANNER_RAW);

echo "<h2>🔍 SMTP Credentials Debug</h2>";
echo "<pre>";

echo "\n=== RAW .env VALUES ===\n";
echo "MAIL_HOST: '" . ($env['MAIL_HOST'] ?? 'NOT SET') . "'\n";
echo "MAIL_PORT: '" . ($env['MAIL_PORT'] ?? 'NOT SET') . "'\n";
echo "MAIL_USERNAME: '" . ($env['MAIL_USERNAME'] ?? 'NOT SET') . "'\n";
echo "MAIL_PASSWORD length: " . (isset($env['MAIL_PASSWORD']) ? strlen($env['MAIL_PASSWORD']) : 'NOT SET') . " chars\n";
echo "MAIL_PASSWORD starts with: " . (isset($env['MAIL_PASSWORD']) ? substr($env['MAIL_PASSWORD'], 0, 20) . '...' : 'NOT SET') . "\n";
echo "MAIL_FROM_ADDRESS: '" . ($env['MAIL_FROM_ADDRESS'] ?? 'NOT SET') . "'\n";
echo "MAIL_ENCRYPTION: '" . ($env['MAIL_ENCRYPTION'] ?? 'NOT SET') . "'\n";

echo "\n=== WHITESPACE CHECK ===\n";
if (isset($env['MAIL_USERNAME'])) {
    $trimmed = trim($env['MAIL_USERNAME']);
    if ($trimmed !== $env['MAIL_USERNAME']) {
        echo "⚠️  WARNING: MAIL_USERNAME has leading/trailing whitespace!\n";
        echo "   Original length: " . strlen($env['MAIL_USERNAME']) . "\n";
        echo "   Trimmed length: " . strlen($trimmed) . "\n";
    } else {
        echo "✅ MAIL_USERNAME: No extra whitespace\n";
    }
}

if (isset($env['MAIL_PASSWORD'])) {
    $trimmed = trim($env['MAIL_PASSWORD']);
    if ($trimmed !== $env['MAIL_PASSWORD']) {
        echo "⚠️  WARNING: MAIL_PASSWORD has leading/trailing whitespace!\n";
        echo "   Original length: " . strlen($env['MAIL_PASSWORD']) . "\n";
        echo "   Trimmed length: " . strlen($trimmed) . "\n";
    } else {
        echo "✅ MAIL_PASSWORD: No extra whitespace\n";
    }
    
    // Check if starts with xsmtpsib
    if (strpos($trimmed, 'xsmtpsib-') === 0) {
        echo "✅ MAIL_PASSWORD: Starts with 'xsmtpsib-' (Brevo SMTP key format)\n";
    } else {
        echo "⚠️  WARNING: MAIL_PASSWORD doesn't start with 'xsmtpsib-'\n";
        echo "   This should be a Brevo SMTP key, not account password!\n";
    }
}

echo "\n=== EXPECTED VALUES ===\n";
echo "MAIL_HOST should be: smtp-relay.brevo.com\n";
echo "MAIL_PORT should be: 587\n";
echo "MAIL_USERNAME should be: your-brevo-email@domain.com\n";
echo "MAIL_PASSWORD should be: xsmtpsib-... (90+ characters)\n";
echo "MAIL_ENCRYPTION should be: tls\n";

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. MAIL_USERNAME must be the email address registered in Brevo\n";
echo "2. MAIL_PASSWORD must be the SMTP key from Brevo (Settings → SMTP & API)\n";
echo "3. MAIL_FROM_ADDRESS should match MAIL_USERNAME (or verified sender)\n";
echo "4. No quotes needed in .env file (e.g., MAIL_HOST=smtp-relay.brevo.com not MAIL_HOST=\"smtp-relay.brevo.com\")\n";

echo "</pre>";

echo "<hr>";
echo "<h3>🧪 Test SMTP Connection (without sending)</h3>";

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = trim($env['MAIL_HOST'] ?? '');
    $mail->SMTPAuth = true;
    $mail->Username = trim($env['MAIL_USERNAME'] ?? '');
    $mail->Password = trim($env['MAIL_PASSWORD'] ?? '');
    $mail->SMTPSecure = ($env['MAIL_ENCRYPTION'] ?? 'tls') === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $env['MAIL_PORT'] ?? 587;
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->AuthType = 'LOGIN';
    
    // Capture debug output
    ob_start();
    
    // Try to connect
    if ($mail->smtpConnect()) {
        $debugOutput = ob_get_clean();
        echo "<pre style='background:#d4edda; padding:10px; border-radius:5px;'>";
        echo "✅ SMTP CONNECTION SUCCESSFUL!\n\n";
        echo "Authentication worked! Your credentials are correct.\n";
        echo "</pre>";
        
        echo "<details><summary>Show SMTP debug log</summary><pre>" . htmlspecialchars($debugOutput) . "</pre></details>";
        
        $mail->smtpClose();
    } else {
        $debugOutput = ob_get_clean();
        echo "<pre style='background:#f8d7da; padding:10px; border-radius:5px;'>";
        echo "❌ SMTP CONNECTION FAILED\n\n";
        echo htmlspecialchars($debugOutput);
        echo "</pre>";
    }
} catch (Exception $e) {
    $debugOutput = ob_get_clean();
    echo "<pre style='background:#f8d7da; padding:10px; border-radius:5px;'>";
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
    echo htmlspecialchars($debugOutput);
    echo "</pre>";
}
?>
