<?php
require_once 'config.php';
requireLogin();

$user = getUserData($pdo, $_SESSION['user_id']);

if ($user['has_subscription']) {
    header('Location: dashboard.php');
    exit;
}

// Generate unique order ID
$orderId = 'ORDER_' . time() . '_' . $user['id'];
$amount = 49.00;

// Create Cashfree order
$cashfreeOrder = createCashfreeOrder($orderId, $amount, $user);

// Save payment record
try {
    $stmt = $pdo->prepare("
        INSERT INTO payments (user_id, order_id, amount, cashfree_order_id) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$user['id'], $orderId, $amount, $cashfreeOrder['cf_order_id'] ?? $orderId]);
} catch (Exception $e) {
    // Handle error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Premium Voice Generator</title>
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
        }

        .header {
            background: linear-gradient(135deg, #ff4d6d 0%, #ff758f 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .content {
            padding: 40px 30px;
        }

        .plan-card {
            background: linear-gradient(135deg, #fff5f7, #ffebee);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 30px;
        }

        .plan-title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 16px;
        }

        .plan-price {
            font-size: 48px;
            font-weight: 700;
            color: #ff4d6d;
            margin-bottom: 8px;
        }

        .plan-duration {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .feature-list {
            list-style: none;
        }

        .feature-list li {
            padding: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #333;
        }

        .feature-icon {
            color: #4caf50;
            font-size: 20px;
        }

        .pay-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #ff4d6d, #ff758f);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 77, 109, 0.4);
        }

        .secure-badge {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #ff4d6d;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
    <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Subscribe to Premium</h1>
            <p>Unlock unlimited voice generation</p>
        </div>

        <div class="content">
            <div class="plan-card">
                <div class="plan-title">Premium Plan</div>
                <div class="plan-price">₹49</div>
                <div class="plan-duration">Valid for 3 months</div>

                <ul class="feature-list">
                    <li>
                        <span class="feature-icon">✓</span>
                        <span>500,000 Credits (1 char = 1 credit)</span>
                    </li>
                    <li>
                        <span class="feature-icon">✓</span>
                        <span>8 Premium Voice Models</span>
                    </li>
                    <li>
                        <span class="feature-icon">✓</span>
                        <span>20,000 Characters Per Generation</span>
                    </li>
                    <li>
                        <span class="feature-icon">✓</span>
                        <span>Audio History & Downloads</span>
                    </li>
                    <li>
                        <span class="feature-icon">✓</span>
                        <span>Priority Support</span>
                    </li>
                </ul>
            </div>

            <button class="pay-btn" onclick="initiatePayment()">
                🔒 Proceed to Secure Payment
            </button>

            <div class="secure-badge">
                <span>🔐</span>
                <span>Secured by Cashfree Payments</span>
            </div>

            <div class="back-link">
                <a href="dashboard.php">← Back to Dashboard</a>
            </div>
        </div>
    </div>

    <script>
        const cashfree = Cashfree({
            mode: '<?php echo CASHFREE_ENV === 'PROD' ? 'production' : 'sandbox'; ?>'
        });

        async function initiatePayment() {
            try {
                const paymentSessionId = '<?php echo $cashfreeOrder['payment_session_id'] ?? ''; ?>';
                
                if (!paymentSessionId) {
                    alert('Payment initialization failed. Please try again.');
                    return;
                }

                const checkoutOptions = {
                    paymentSessionId: paymentSessionId,
                    returnUrl: '<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/payment_callback.php?order_id=' . $orderId; ?>',
                    redirectTarget: '_self'
                };

                cashfree.checkout(checkoutOptions).then(function(result) {
                    if (result.error) {
                        alert('Payment failed: ' + result.error.message);
                    }
                    if (result.redirect) {
                        console.log('Payment will be redirected');
                    }
                });
            } catch (error) {
                alert('Payment initialization failed. Please try again.');
                console.error(error);
            }
        }
    </script>
</body>
</html>

<?php
function createCashfreeOrder($orderId, $amount, $user) {
    $url = CASHFREE_ENV === 'PROD' 
        ? 'https://api.cashfree.com/pg/orders' 
        : 'https://sandbox.cashfree.com/pg/orders';
    
    $orderData = [
        'order_id' => $orderId,
        'order_amount' => $amount,
        'order_currency' => 'INR',
        'customer_details' => [
            'customer_id' => 'CUST_' . $user['id'],
            'customer_name' => $user['name'],
            'customer_email' => $user['email'],
            'customer_phone' => $user['phone']
        ],
        'order_meta' => [
            'return_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/payment_callback.php?order_id=' . $orderId,
            'notify_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/payment_webhook.php'
        ]
    ];
    
    $headers = [
        'Content-Type: application/json',
        'x-api-version: 2023-08-01',
        'x-client-id: ' . CASHFREE_APP_ID,
        'x-client-secret: ' . CASHFREE_SECRET_KEY
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return null;
}
?>