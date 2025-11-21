<?php
require_once 'config.php';
requireLogin();

$orderId = $_GET['order_id'] ?? '';

if (empty($orderId)) {
    header('Location: dashboard.php');
    exit;
}

// Verify payment status with Cashfree
$paymentStatus = verifyCashfreePayment($orderId);

try {
    if ($paymentStatus === 'SUCCESS') {
        // Update payment record
        $stmt = $pdo->prepare("
            UPDATE payments 
            SET status = 'completed', payment_time = NOW() 
            WHERE order_id = ? AND user_id = ?
        ");
        $stmt->execute([$orderId, $_SESSION['user_id']]);
        
        // Update user subscription
        $subscriptionExpires = date('Y-m-d', strtotime('+3 months'));
        $stmt = $pdo->prepare("
            UPDATE users 
            SET credits = credits + 500000, 
                has_subscription = TRUE,
                subscription_expires = ?
            WHERE id = ?
        ");
        $stmt->execute([$subscriptionExpires, $_SESSION['user_id']]);
        
        $success = true;
        $message = 'Payment successful! 500,000 credits added to your account.';
    } else {
        $success = false;
        $message = 'Payment verification failed. Please contact support.';
    }
} catch (Exception $e) {
    $success = false;
    $message = 'An error occurred. Please contact support.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Payment Successful' : 'Payment Failed'; ?></title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            text-align: center;
            padding: 50px 40px;
        }

        .icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 50px;
        }

        .success-icon {
            background: linear-gradient(135deg, #4caf50, #66bb6a);
            animation: scaleIn 0.5s ease;
        }

        .error-icon {
            background: linear-gradient(135deg, #f44336, #ef5350);
            animation: shake 0.5s ease;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        h1 {
            font-size: 28px;
            margin-bottom: 16px;
            color: #333;
        }

        p {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .credits-box {
            background: linear-gradient(135deg, #fff5f7, #ffebee);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 30px;
        }

        .credits-amount {
            font-size: 48px;
            font-weight: 700;
            color: #ff4d6d;
            margin-bottom: 8px;
        }

        .credits-label {
            color: #666;
            font-size: 14px;
        }

        .btn {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #ff4d6d, #ff758f);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 77, 109, 0.4);
        }

        .order-id {
            background: #f5f5f5;
            padding: 12px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 13px;
            color: #666;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
        <div class="icon success-icon">✓</div>
        <h1>Payment Successful!</h1>
        <p><?php echo $message; ?></p>
        
        <div class="credits-box">
            <div class="credits-amount">500,000</div>
            <div class="credits-label">Credits Added</div>
        </div>

        <a href="dashboard.php" class="btn">Start Generating Voices</a>
        
        <?php else: ?>
        <div class="icon error-icon">✕</div>
        <h1>Payment Failed</h1>
        <p><?php echo $message; ?></p>
        
        <a href="payment.php" class="btn">Try Again</a>
        <?php endif; ?>

        <div class="order-id">
            Order ID: <?php echo htmlspecialchars($orderId); ?>
        </div>
    </div>
</body>
</html>

<?php
function verifyCashfreePayment($orderId) {
    $url = CASHFREE_ENV === 'PROD'
        ? "https://api.cashfree.com/pg/orders/{$orderId}"
        : "https://sandbox.cashfree.com/pg/orders/{$orderId}";
    
    $headers = [
        'Content-Type: application/json',
        'x-api-version: 2023-08-01',
        'x-client-id: ' . CASHFREE_APP_ID,
        'x-client-secret: ' . CASHFREE_SECRET_KEY
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        return $result['order_status'] ?? 'FAILED';
    }
    
    return 'FAILED';
}
?>