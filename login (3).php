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
    <title>Login - Premium Voice Generator</title>
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
            padding: 50px 30px;
            text-align: center;
            color: white;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 15px;
            opacity: 0.95;
        }

        .icon-container {
            width: 90px;
            height: 90px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            backdrop-filter: blur(10px);
        }

        .icon {
            width: 45px;
            height: 45px;
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
            padding: 15px 18px;
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
            display: none;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #2e7d32;
            display: none;
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

        .forgot-link {
            text-align: right;
            margin-top: 8px;
        }

        .forgot-link a {
            color: #ff4d6d;
            text-decoration: none;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon-container">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                    <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                </svg>
            </div>
            <h1>Welcome Back</h1>
            <p>Login to your account</p>
        </div>

        <div class="form-container">
            <div id="message" class="error"></div>
            
            <form id="loginForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="your@email.com" autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password" autocomplete="current-password">
                    <div class="forgot-link">
                        <a href="#">Forgot Password?</a>
                    </div>
                </div>

                <button type="submit" class="btn">Login Now</button>
                
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p style="margin-top: 10px; color: #666;">Logging in...</p>
                </div>
            </form>
        </div>

        <div class="footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Show loading
            form.style.display = 'none';
            document.getElementById('loading').style.display = 'block';
            
            try {
                const response = await fetch('api/login_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    showMessage(result.message, 'error');
                    form.style.display = 'block';
                    document.getElementById('loading').style.display = 'none';
                }
            } catch (error) {
                showMessage('Login failed. Please try again.', 'error');
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