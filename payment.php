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

// Save payment record only if order creation was successful
if ($cashfreeOrder && isset($cashfreeOrder['success']) && $cashfreeOrder['success']) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO payments (user_id, order_id, amount, cashfree_order_id) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user['id'], $orderId, $amount, $cashfreeOrder['cf_order_id'] ?? $orderId]);
    } catch (Exception $e) {
        error_log("Payment record insertion error: " . $e->getMessage());
    }
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
                // Check if order creation was successful
                const orderSuccess = <?php echo isset($cashfreeOrder['success']) ? ($cashfreeOrder['success'] ? 'true' : 'false') : 'false'; ?>;
                const errorMessage = '<?php echo isset($cashfreeOrder['error']) ? addslashes($cashfreeOrder['error']) : ''; ?>';
                
                if (!orderSuccess) {
                    const message = errorMessage || 'Payment initialization failed. Please try again.';
                    alert('Error: ' + message);
                    console.error('Order creation failed:', errorMessage);
                    return;
                }

                const paymentSessionId = '<?php echo isset($cashfreeOrder['payment_session_id']) ? $cashfreeOrder['payment_session_id'] : ''; ?>';
                
                if (!paymentSessionId) {
                    alert('Error: Payment session ID is missing. Please contact support.');
                    console.error('Payment session ID is missing from the response');
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
                        console.error('Cashfree checkout error:', result.error);
                    }
                    if (result.redirect) {
                        console.log('Payment will be redirected');
                    }
                });
            } catch (error) {
                alert('Payment initialization failed: ' + (error.message || 'Unknown error'));
                console.error('Payment initialization error:', error);
            }
        }
    </script>
</body>
</html>

<?php
function createCashfreeOrder($orderId, $amount, $user) {
    // CRITICAL: Validate API credentials before making request
    if (!defined('CASHFREE_APP_ID') || empty(CASHFREE_APP_ID) || CASHFREE_APP_ID === 'YOUR_CASHFREE_APP_ID') {
        $error = 'Cashfree App ID is not configured';
        error_log("Cashfree Error: " . $error);
        return [
            'success' => false,
            'error' => $error
        ];
    }
    
    if (!defined('CASHFREE_SECRET_KEY') || empty(CASHFREE_SECRET_KEY) || CASHFREE_SECRET_KEY === 'YOUR_CASHFREE_SECRET_KEY') {
        $error = 'Cashfree Secret Key is not configured';
        error_log("Cashfree Error: " . $error);
        return [
            'success' => false,
            'error' => $error
        ];
    }
    
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
    
    // Initialize cURL
    $ch = curl_init($url);
    if ($ch === false) {
        $error = 'Failed to initialize cURL';
        error_log("Cashfree Error: " . $error);
        return [
            'success' => false,
            'error' => $error
        ];
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // Optional: Skip SSL verification for testing (NOT recommended for production)
    if (defined('CASHFREE_SKIP_SSL_VERIFY') && CASHFREE_SKIP_SSL_VERIFY === true) {
        error_log("WARNING: SSL verification is disabled for Cashfree API. This should only be used for testing!");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    
    // Execute request
    $response = curl_exec($ch);
    
    // CRITICAL: Check for cURL errors
    if ($response === false) {
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);
        
        $error = "cURL Error ($curlErrno): $curlError";
        error_log("Cashfree API Error: " . $error);
        
        return [
            'success' => false,
            'error' => 'Network error occurred. Please try again later.'
        ];
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Handle empty response
    if (empty($response)) {
        error_log("Cashfree API Error: Empty response received (HTTP $httpCode)");
        return [
            'success' => false,
            'error' => 'Empty response from payment gateway'
        ];
    }
    
    // Add try-catch for JSON decode to handle invalid JSON
    try {
        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonError = json_last_error_msg();
            error_log("Cashfree API Error: JSON decode failed - $jsonError. Response: $response");
            return [
                'success' => false,
                'error' => 'Invalid response format from payment gateway'
            ];
        }
    } catch (Exception $e) {
        error_log("Cashfree API Error: Exception during JSON decode - " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Failed to parse payment gateway response'
        ];
    }
    
    // Validate HTTP status code and response structure
    if ($httpCode === 200) {
        // CRITICAL: Check if payment_session_id exists in response
        if (!isset($decodedResponse['payment_session_id']) || empty($decodedResponse['payment_session_id'])) {
            error_log("Cashfree API Error: payment_session_id missing in response. HTTP $httpCode. Response: " . json_encode($decodedResponse));
            return [
                'success' => false,
                'error' => 'Payment session ID not received from gateway'
            ];
        }
        
        // Success - return structured response
        return array_merge(
            ['success' => true],
            $decodedResponse
        );
    } else {
        // API returned error
        $errorMessage = isset($decodedResponse['message']) ? $decodedResponse['message'] : 'Unknown error';
        $errorType = isset($decodedResponse['type']) ? $decodedResponse['type'] : 'API_ERROR';
        
        error_log("Cashfree API Error: HTTP $httpCode - Type: $errorType, Message: $errorMessage. Full response: " . json_encode($decodedResponse));
        
        return [
            'success' => false,
            'error' => $errorMessage,
            'error_type' => $errorType,
            'http_code' => $httpCode
        ];
    }
}
?>