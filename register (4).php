<?php
require_once 'config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Premium Voice Generator</title>
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
            max-width: 450px;
            width: 100%;
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .header p {
            font-size: 14px;
            opacity: 0.95;
        }

        .icon-container {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            backdrop-filter: blur(10px);
        }

        .icon {
            width: 40px;
            height: 40px;
            fill: white;
        }

        .form-container {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
            background: #fafafa;
        }

        input:focus {
            outline: none;
            border-color: #ff4d6d;
            background: white;
            box-shadow: 0 0 0 4px rgba(255, 77, 109, 0.1);
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ff4d6d 0%, #ff758f 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 77, 109, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .footer {
            text-align: center;
            padding: 20px 30px 30px;
            color: #666;
            font-size: 14px;
        }

        .footer a {
            color: #ff4d6d;
            text-decoration: none;
            font-weight: 600;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c62828;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #2e7d32;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 10px;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #ff4d6d;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon-container">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <h1>Create Account</h1>
            <p>Join Premium Voice Generator</p>
        </div>

        <div class="form-container">
            <div id="message"></div>
            
            <form id="registerForm">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required placeholder="Enter your full name">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="your@email.com">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required placeholder="10-digit mobile number" maxlength="10" pattern="[0-9]{10}">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Minimum 6 characters" minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="Re-enter password">
                </div>

                <button type="submit" class="btn">Create Account</button>
                
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p style="margin-top: 10px; color: #666;">Creating account...</p>
                </div>
            </form>
        </div>

        <div class="footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Validation
            if (data.password !== data.confirmPassword) {
                showMessage('Passwords do not match!', 'error');
                return;
            }
            
            if (data.phone.length !== 10) {
                showMessage('Please enter a valid 10-digit phone number!', 'error');
                return;
            }
            
            // Show loading
            form.style.display = 'none';
            document.getElementById('loading').style.display = 'block';
            
            try {
                const response = await fetch('api/register_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    showMessage(result.message, 'error');
                    form.style.display = 'block';
                    document.getElementById('loading').style.display = 'none';
                }
            } catch (error) {
                showMessage('Registration failed. Please try again.', 'error');
                form.style.display = 'block';
                document.getElementById('loading').style.display = 'none';
            }
        });
        
        function showMessage(message, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.className = type;
            messageDiv.textContent = message;
            messageDiv.style.display = 'block';
        }
    </script>
</body>
</html>