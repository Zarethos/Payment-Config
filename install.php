<?php
/**
 * Installation & System Checker
 * Run this file first to verify everything is configured correctly
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if config file exists
$configExists = file_exists('config.php');

if ($configExists) {
    require_once 'config.php';
}

$checks = [];

// 1. Check PHP Version
$checks['php_version'] = [
    'name' => 'PHP Version (>= 7.4)',
    'status' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'value' => PHP_VERSION,
    'required' => true
];

// 2. Check PDO Extension
$checks['pdo_mysql'] = [
    'name' => 'PDO MySQL Extension',
    'status' => extension_loaded('pdo_mysql'),
    'value' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled',
    'required' => true
];

// 3. Check cURL Extension
$checks['curl'] = [
    'name' => 'cURL Extension',
    'status' => extension_loaded('curl'),
    'value' => extension_loaded('curl') ? 'Enabled' : 'Disabled',
    'required' => true
];

// 4. Check OpenSSL
$checks['openssl'] = [
    'name' => 'OpenSSL Extension',
    'status' => extension_loaded('openssl'),
    'value' => extension_loaded('openssl') ? 'Enabled' : 'Disabled',
    'required' => true
];

// 5. Check Database Connection
if ($configExists) {
    try {
        $testPdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS
        );
        $checks['database'] = [
            'name' => 'Database Connection',
            'status' => true,
            'value' => 'Connected to ' . DB_NAME,
            'required' => true
        ];
        
        // Check if tables exist
        $tables = ['users', 'voice_history', 'payments', 'user_sessions', 'system_logs'];
        $existingTables = [];
        foreach ($tables as $table) {
            $result = $testPdo->query("SHOW TABLES LIKE '$table'");
            if ($result->rowCount() > 0) {
                $existingTables[] = $table;
            }
        }
        
        $checks['tables'] = [
            'name' => 'Database Tables',
            'status' => count($existingTables) === count($tables),
            'value' => count($existingTables) . ' of ' . count($tables) . ' tables created',
            'required' => true
        ];
        
    } catch(PDOException $e) {
        $checks['database'] = [
            'name' => 'Database Connection',
            'status' => false,
            'value' => 'Failed: ' . $e->getMessage(),
            'required' => true
        ];
    }
} else {
    $checks['database'] = [
        'name' => 'Database Configuration',
        'status' => false,
        'value' => 'config.php not found',
        'required' => true
    ];
}

// 6. Check Audio Directory
$audioDir = __DIR__ . '/audio';
$checks['audio_directory'] = [
    'name' => 'Audio Directory',
    'status' => file_exists($audioDir) && is_writable($audioDir),
    'value' => file_exists($audioDir) ? 
        (is_writable($audioDir) ? 'Writable' : 'Not Writable') : 
        'Not Found',
    'required' => true
];

// 7. Check API Configuration
if ($configExists) {
    $apiKeys = checkApiKeysConfigured();
    
    $checks['openai_api'] = [
        'name' => 'OpenAI API Key',
        'status' => $apiKeys['openai'],
        'value' => $apiKeys['openai'] ? 'Configured' : 'Not Configured',
        'required' => false
    ];
    
    $checks['google_api'] = [
        'name' => 'Google Cloud API Key',
        'status' => $apiKeys['google'],
        'value' => $apiKeys['google'] ? 'Configured' : 'Not Configured',
        'required' => false
    ];
    
    $checks['cashfree_api'] = [
        'name' => 'Cashfree Payment Gateway',
        'status' => $apiKeys['cashfree'],
        'value' => $apiKeys['cashfree'] ? 'Configured' : 'Not Configured',
        'required' => true
    ];
    
    $checks['tts_ready'] = [
        'name' => 'TTS Service Ready',
        'status' => $apiKeys['tts_ready'],
        'value' => $apiKeys['tts_ready'] ? 'At least one TTS configured' : 'No TTS configured',
        'required' => true
    ];
}

// 8. Check File Permissions
$checks['config_permissions'] = [
    'name' => 'Config File Permissions',
    'status' => $configExists && is_readable('config.php'),
    'value' => $configExists ? (is_readable('config.php') ? 'Readable' : 'Not Readable') : 'Not Found',
    'required' => true
];

// Calculate overall status
$totalChecks = count($checks);
$passedChecks = 0;
$failedRequired = 0;

foreach ($checks as $check) {
    if ($check['status']) {
        $passedChecks++;
    } elseif ($check['required']) {
        $failedRequired++;
    }
}

$allPassed = $failedRequired === 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Checker - Premium Voice Generator</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ffffff 0%, #ffebee 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #ff4d6d 0%, #ff758f 100%);
            padding: 40px;
            text-align: center;
            color: white;
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.95;
        }

        .status-banner {
            padding: 30px 40px;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
        }

        .status-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-error {
            background: #ffebee;
            color: #c62828;
        }

        .content {
            padding: 40px;
        }

        .check-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.3s;
        }

        .check-item:hover {
            background: #fafafa;
        }

        .check-item:last-child {
            border-bottom: none;
        }

        .check-name {
            font-weight: 600;
            color: #333;
            flex: 1;
        }

        .check-value {
            color: #666;
            font-size: 14px;
            margin: 0 20px;
        }

        .check-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-pass {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-fail {
            background: #ffebee;
            color: #c62828;
        }

        .status-optional {
            background: #fff3e0;
            color: #e65100;
        }

        .progress-bar {
            margin: 30px 0;
            height: 8px;
            background: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4caf50, #66bb6a);
            transition: width 1s ease;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            text-align: center;
            padding: 20px;
            background: #fafafa;
            border-radius: 12px;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #666;
            font-size: 13px;
        }

        .actions {
            margin-top: 40px;
            text-align: center;
        }

        .btn {
            display: inline-block;
            padding: 16px 32px;
            background: linear-gradient(135deg, #ff4d6d, #ff758f);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
            margin: 0 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 77, 109, 0.4);
        }

        .btn-secondary {
            background: #f5f5f5;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .setup-guide {
            background: #fff5f7;
            padding: 24px;
            border-radius: 12px;
            margin-top: 30px;
            border-left: 4px solid #ff4d6d;
        }

        .setup-guide h3 {
            color: #ff4d6d;
            margin-bottom: 16px;
        }

        .setup-guide ol {
            margin-left: 20px;
            line-height: 1.8;
            color: #333;
        }

        .setup-guide li {
            margin-bottom: 8px;
        }

        .code-block {
            background: #333;
            color: #00ff00;
            padding: 16px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            margin: 10px 0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎙️ Premium Voice Generator</h1>
            <p>Installation & System Checker</p>
        </div>

        <div class="status-banner <?php echo $allPassed ? 'status-success' : 'status-error'; ?>">
            <?php if ($allPassed): ?>
                ✅ All Required Checks Passed - System Ready!
            <?php else: ?>
                ❌ System Not Ready - <?php echo $failedRequired; ?> Required Check(s) Failed
            <?php endif; ?>
        </div>

        <div class="content">
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-value" style="color: #4caf50;"><?php echo $passedChecks; ?></div>
                    <div class="stat-label">Passed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #c62828;"><?php echo $failedRequired; ?></div>
                    <div class="stat-label">Failed (Required)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #666;"><?php echo $totalChecks; ?></div>
                    <div class="stat-label">Total Checks</div>
                </div>
            </div>

            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo ($passedChecks / $totalChecks) * 100; ?>%;"></div>
            </div>

            <h2 style="margin: 30px 0 20px; color: #333;">System Checks</h2>

            <?php foreach ($checks as $key => $check): ?>
            <div class="check-item">
                <div class="check-name">
                    <?php echo $check['name']; ?>
                    <?php if (!$check['required']): ?>
                        <span style="font-size: 11px; color: #999;">(Optional)</span>
                    <?php endif; ?>
                </div>
                <div class="check-value"><?php echo $check['value']; ?></div>
                <div class="check-status <?php 
                    if ($check['status']) {
                        echo 'status-pass';
                    } elseif ($check['required']) {
                        echo 'status-fail';
                    } else {
                        echo 'status-optional';
                    }
                ?>">
                    <?php if ($check['status']): ?>
                        ✓ Pass
                    <?php elseif ($check['required']): ?>
                        ✗ Fail
                    <?php else: ?>
                        ⚠ Warning
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (!$allPassed): ?>
            <div class="setup-guide">
                <h3>🔧 Setup Instructions</h3>
                <ol>
                    <li>Make sure <strong>config.php</strong> exists in the root directory</li>
                    <li>Update database credentials in config.php:
                        <div class="code-block">
define('DB_HOST', 'localhost');
define('DB_NAME', 'windersx_voicegen');
define('DB_USER', 'windersx_voicegen');
define('DB_PASS', 'DmjjHsXW2jDdGpG');
                        </div>
                    </li>
                    <li>Configure Cashfree Payment Gateway:
                        <div class="code-block">
define('CASHFREE_APP_ID', 'YOUR_APP_ID');
define('CASHFREE_SECRET_KEY', 'YOUR_SECRET_KEY');
                        </div>
                    </li>
                    <li>Configure at least one TTS API (OpenAI recommended):
                        <div class="code-block">
define('OPENAI_API_KEY', 'sk-...');
                        </div>
                    </li>
                    <li>Create <strong>audio/</strong> folder and set permissions:
                        <div class="code-block">
mkdir audio
chmod 755 audio
                        </div>
                    </li>
                    <li>Enable required PHP extensions: PDO, cURL, OpenSSL</li>
                    <li>Refresh this page to verify changes</li>
                </ol>
            </div>
            <?php endif; ?>

            <div class="actions">
                <?php if ($allPassed): ?>
                    <a href="register.php" class="btn">Start Registration</a>
                    <a href="login.php" class="btn btn-secondary">Go to Login</a>
                <?php else: ?>
                    <a href="install.php" class="btn">Refresh Checks</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Animate progress bar on load
        window.addEventListener('load', function() {
            const progress = document.querySelector('.progress-fill');
            progress.style.width = '0%';
            setTimeout(() => {
                progress.style.width = '<?php echo ($passedChecks / $totalChecks) * 100; ?>%';
            }, 100);
        });
    </script>
</body>
</html>