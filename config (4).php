<?php
/**
 * Premium Voice Generator - Configuration File
 * Auto Table Creation Enabled
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'windersx_voicegen');
define('DB_USER', 'windersx_voicegen');
define('DB_PASS', 'DmjjHsXW2jDdGpG');

// Cashfree Payment Gateway Configuration
define('CASHFREE_APP_ID', '805651d1a917471429d9005d30156508');
define('CASHFREE_SECRET_KEY', 'cfsk_ma_prod_1d334e9021909f422d8b64991296c24a_a2c2c4ca');
define('CASHFREE_ENV', 'PROD'); // Change to 'PROD' for production

// API Keys Configuration (Choose one TTS provider)
// Option 1: OpenAI TTS API (Recommended)
define('OPENAI_API_KEY', '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');

// Option 2: Google Cloud Text-to-Speech API
define('GOOGLE_CLOUD_API_KEY', 'YOUR_GOOGLE_CLOUD_API_KEY');

// Subscription Configuration
define('SUBSCRIPTION_PRICE', 49.00);
define('SUBSCRIPTION_CREDITS', 500000);
define('SUBSCRIPTION_DURATION_MONTHS', 3);

// Generation Limits
define('MAX_CHARACTERS_PER_GENERATION', 20000);
define('CHARACTER_TO_CREDIT_RATIO', 1); // 1 character = 1 credit

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Auto Create Tables if not exists
createTablesIfNotExists($pdo);

/**
 * Create all required tables automatically
 */
function createTablesIfNotExists($pdo) {
    try {
        // Create Users Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                phone VARCHAR(15) NOT NULL,
                password VARCHAR(255) NOT NULL,
                credits BIGINT DEFAULT 0,
                has_subscription BOOLEAN DEFAULT FALSE,
                subscription_expires DATE NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_subscription (has_subscription)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create Voice History Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS voice_history (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                voice_name VARCHAR(50) NOT NULL,
                script_text TEXT NOT NULL,
                character_count INT NOT NULL,
                audio_url VARCHAR(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create Payments Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS payments (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                order_id VARCHAR(100) UNIQUE NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                status VARCHAR(50) DEFAULT 'pending',
                payment_method VARCHAR(50),
                cashfree_order_id VARCHAR(100),
                payment_time TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_order_id (order_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create Sessions Table (for better session management)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_sessions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                session_token VARCHAR(255) UNIQUE NOT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_session_token (session_token)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create System Logs Table (for debugging)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS system_logs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NULL,
                action VARCHAR(100) NOT NULL,
                details TEXT,
                ip_address VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_action (action),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

    } catch(PDOException $e) {
        error_log("Table Creation Error: " . $e->getMessage());
    }
}

/**
 * Helper Functions
 */

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Require login (redirect if not logged in)
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Get current user data
function getUserData($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return null;
    }
}

// Get current user (shorthand)
function getCurrentUser($pdo) {
    if (!isLoggedIn()) {
        return null;
    }
    return getUserData($pdo, $_SESSION['user_id']);
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone number
function validatePhone($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    // Check if it's exactly 10 digits
    return strlen($phone) === 10 && ctype_digit($phone);
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Log system action
function logAction($pdo, $action, $details = '', $user_id = null) {
    try {
        if ($user_id === null && isLoggedIn()) {
            $user_id = $_SESSION['user_id'];
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO system_logs (user_id, action, details, ip_address) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch(PDOException $e) {
        error_log("Log Action Error: " . $e->getMessage());
    }
}

// Check if user has sufficient credits
function hasCredits($pdo, $user_id, $required_credits) {
    $user = getUserData($pdo, $user_id);
    return $user && $user['credits'] >= $required_credits;
}

// Deduct credits from user
function deductCredits($pdo, $user_id, $credits_to_deduct) {
    try {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET credits = credits - ? 
            WHERE id = ? AND credits >= ?
        ");
        $stmt->execute([$credits_to_deduct, $user_id, $credits_to_deduct]);
        return $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        error_log("Deduct Credits Error: " . $e->getMessage());
        return false;
    }
}

// Add credits to user
function addCredits($pdo, $user_id, $credits_to_add) {
    try {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET credits = credits + ? 
            WHERE id = ?
        ");
        $stmt->execute([$credits_to_add, $user_id]);
        return $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        error_log("Add Credits Error: " . $e->getMessage());
        return false;
    }
}

// Check if subscription is active
function hasActiveSubscription($pdo, $user_id) {
    $user = getUserData($pdo, $user_id);
    
    if (!$user || !$user['has_subscription']) {
        return false;
    }
    
    // Check if subscription has expired
    if ($user['subscription_expires']) {
        $expiry_date = strtotime($user['subscription_expires']);
        $current_date = time();
        
        if ($current_date > $expiry_date) {
            // Subscription expired - deactivate it
            try {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET has_subscription = FALSE 
                    WHERE id = ?
                ");
                $stmt->execute([$user_id]);
            } catch(PDOException $e) {
                error_log("Deactivate Subscription Error: " . $e->getMessage());
            }
            return false;
        }
    }
    
    return true;
}

// Format number with Indian style
function formatIndianNumber($number) {
    return number_format($number, 0, '.', ',');
}

// Generate unique order ID
function generateOrderId($user_id) {
    return 'ORDER_' . time() . '_' . $user_id . '_' . rand(1000, 9999);
}

// Create audio directory if not exists
function ensureAudioDirectory() {
    $audioDir = __DIR__ . '/audio';
    if (!file_exists($audioDir)) {
        mkdir($audioDir, 0755, true);
    }
    return $audioDir;
}

// Get base URL
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    return $protocol . $domainName;
}

// JSON Response Helper
function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

// Security: Prevent SQL Injection
function sanitizeForDB($pdo, $value) {
    return $pdo->quote($value);
}

// Check if API keys are configured
function checkApiKeysConfigured() {
    $openai_configured = defined('OPENAI_API_KEY') && OPENAI_API_KEY !== 'YOUR_OPENAI_API_KEY';
    $google_configured = defined('GOOGLE_CLOUD_API_KEY') && GOOGLE_CLOUD_API_KEY !== 'YOUR_GOOGLE_CLOUD_API_KEY';
    $cashfree_configured = defined('CASHFREE_APP_ID') && CASHFREE_APP_ID !== 'YOUR_CASHFREE_APP_ID';
    
    return [
        'openai' => $openai_configured,
        'google' => $google_configured,
        'cashfree' => $cashfree_configured,
        'tts_ready' => $openai_configured || $google_configured,
        'payment_ready' => $cashfree_configured
    ];
}

// Create .htaccess for security
function createHtaccess() {
    $htaccess_content = "
# Prevent directory browsing
Options -Indexes

# Deny access to config file
<Files config.php>
    Order allow,deny
    Deny from all
</Files>

# Enable mod_rewrite
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Remove .php extension
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME}\.php -f
    RewriteRule ^(.*)$ \$1.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options \"nosniff\"
    Header set X-Frame-Options \"SAMEORIGIN\"
    Header set X-XSS-Protection \"1; mode=block\"
</IfModule>

# PHP Settings
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
php_value max_input_time 300
";

    $htaccess_file = __DIR__ . '/.htaccess';
    if (!file_exists($htaccess_file)) {
        file_put_contents($htaccess_file, $htaccess_content);
    }
}

// Initialize audio directory and .htaccess
ensureAudioDirectory();
createHtaccess();

// Log successful configuration load
if (isLoggedIn()) {
    logAction($pdo, 'PAGE_VIEW', 'User accessed: ' . basename($_SERVER['PHP_SELF']));
}

/**
 * Timezone Configuration
 */
date_default_timezone_set('Asia/Kolkata');

/**
 * Constants for Response Messages
 */
define('MSG_SUCCESS_REGISTER', 'Registration successful! Please login.');
define('MSG_SUCCESS_LOGIN', 'Login successful!');
define('MSG_SUCCESS_LOGOUT', 'Logged out successfully!');
define('MSG_SUCCESS_PAYMENT', 'Payment successful! Credits added to your account.');
define('MSG_SUCCESS_GENERATION', 'Voice generated successfully!');

define('MSG_ERROR_INVALID_CREDENTIALS', 'Invalid email or password');
define('MSG_ERROR_EMAIL_EXISTS', 'Email already registered');
define('MSG_ERROR_PHONE_EXISTS', 'Phone number already registered');
define('MSG_ERROR_INSUFFICIENT_CREDITS', 'Insufficient credits');
define('MSG_ERROR_NO_SUBSCRIPTION', 'Please subscribe to generate voice');
define('MSG_ERROR_GENERATION_FAILED', 'Voice generation failed. Please try again.');
define('MSG_ERROR_PAYMENT_FAILED', 'Payment failed. Please try again.');

/**
 * Debug Mode (Set to false in production)
 */
define('DEBUG_MODE', true);

if (!DEBUG_MODE) {
    error_reporting(0);
    ini_set('display_errors', 0);
}

/**
 * Application Version
 */
define('APP_VERSION', '1.0.0');
define('APP_NAME', 'Premium Voice Generator');

/**
 * System Ready Message
 */
if (DEBUG_MODE) {
    error_log("============================================");
    error_log("✅ " . APP_NAME . " v" . APP_VERSION);
    error_log("✅ Database Connected: " . DB_NAME);
    error_log("✅ Tables Created Successfully");
    error_log("✅ Session Started");
    error_log("✅ Audio Directory Ready");
    error_log("============================================");
}
?>