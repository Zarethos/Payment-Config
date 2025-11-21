<?php
require_once 'config.php';
requireLogin();

$user = getUserData($pdo, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Premium Voice Generator</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ffffff 0%, #ffebee 50%, #ffe0e5 100%);
            min-height: 100vh;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #ff4d6d, #ff758f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: #fff5f7;
            border-radius: 12px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff4d6d, #ff758f);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .logout-btn {
            padding: 10px 20px;
            background: #ff4d6d;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: #ff758f;
            transform: translateY(-2px);
        }

        .container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff4d6d, #ff758f);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(255, 77, 109, 0.2);
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
        }

        .premium-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #333;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
        }

        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        @media (max-width: 968px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .card-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #333;
        }

        .voice-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .voice-card {
            padding: 20px;
            border: 2px solid #f0f0f0;
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #fafafa;
        }

        .voice-card:hover {
            border-color: #ff4d6d;
            background: #fff5f7;
            transform: translateY(-3px);
        }

        .voice-card.selected {
            border-color: #ff4d6d;
            background: linear-gradient(135deg, #ff4d6d10, #ff758f10);
            box-shadow: 0 4px 15px rgba(255, 77, 109, 0.2);
        }

        .voice-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #ff4d6d, #ff758f);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            color: white;
            font-size: 24px;
        }

        .voice-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .voice-desc {
            font-size: 11px;
            color: #666;
        }

        textarea {
            width: 100%;
            min-height: 200px;
            padding: 18px;
            border: 2px solid #f0f0f0;
            border-radius: 16px;
            font-size: 15px;
            font-family: inherit;
            resize: vertical;
            background: #fafafa;
            transition: all 0.3s;
        }

        textarea:focus {
            outline: none;
            border-color: #ff4d6d;
            background: white;
            box-shadow: 0 0 0 4px rgba(255, 77, 109, 0.1);
        }

        .char-count {
            text-align: right;
            margin-top: 8px;
            font-size: 13px;
            color: #666;
        }

        .generate-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #ff4d6d, #ff758f);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .generate-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 77, 109, 0.4);
        }

        .generate-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .payment-card {
            background: linear-gradient(135deg, #ff4d6d, #ff758f);
            color: white;
            padding: 32px;
            border-radius: 20px;
            text-align: center;
        }

        .payment-card h3 {
            font-size: 24px;
            margin-bottom: 16px;
        }

        .payment-card p {
            opacity: 0.95;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .price {
            font-size: 48px;
            font-weight: 700;
            margin: 20px 0;
        }

        .price-desc {
            font-size: 14px;
            opacity: 0.9;
        }

        .subscribe-btn {
            width: 100%;
            padding: 16px;
            background: white;
            color: #ff4d6d;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
        }

        .subscribe-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .history-item {
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s;
        }

        .history-item:hover {
            background: #fff5f7;
        }

        .history-item:last-child {
            border-bottom: none;
        }

        .history-voice {
            font-weight: 600;
            color: #ff4d6d;
            margin-bottom: 6px;
        }

        .history-text {
            font-size: 13px;
            color: #666;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .history-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .play-btn, .download-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .play-btn {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .download-btn {
            background: #e3f2fd;
            color: #1565c0;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #ff4d6d;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: none;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                🎙️ VoiceGen Premium
            </div>
            <div class="nav-right">
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
                    <div>
                        <div style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($user['name']); ?></div>
                        <div style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                </div>
                <button class="logout-btn" onclick="logout()">Logout</button>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Available Credits</div>
                <div class="stat-value" id="creditDisplay"><?php echo number_format($user['credits']); ?></div>
                <?php if ($user['has_subscription']): ?>
                <div class="premium-badge">
                    ⭐ Premium Active
                </div>
                <?php endif; ?>
            </div>

            <div class="stat-card">
                <div class="stat-label">Subscription Status</div>
                <div class="stat-value" style="font-size: 20px;">
                    <?php echo $user['has_subscription'] ? '✅ Active' : '❌ Inactive'; ?>
                </div>
                <?php if ($user['subscription_expires']): ?>
                <div style="font-size: 12px; color: #666; margin-top: 8px;">
                    Expires: <?php echo date('d M Y', strtotime($user['subscription_expires'])); ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="stat-card">
                <div class="stat-label">Generation Limit</div>
                <div class="stat-value" style="font-size: 24px;">20,000</div>
                <div style="font-size: 12px; color: #666; margin-top: 8px;">Characters per generation</div>
            </div>
        </div>

        <?php if (!$user['has_subscription']): ?>
        <div class="payment-card" style="margin-bottom: 40px;">
            <h3>🚀 Unlock Premium Features</h3>
            <p>Get unlimited access to all premium voices and generate up to 20,000 characters at once!</p>
            <div class="price">₹49</div>
            <div class="price-desc">3 Months | 500,000 Credits</div>
            <button class="subscribe-btn" onclick="initiatePayment()">Subscribe Now</button>
        </div>
        <?php endif; ?>

        <div class="main-grid">
            <div>
                <div class="card">
                    <h2 class="card-title">🎤 Generate Voice</h2>
                    
                    <div id="alert" class="alert"></div>

                    <h3 style="margin-bottom: 16px; font-size: 16px; color: #333;">Select Voice</h3>
                    <div class="voice-grid" id="voiceGrid">
                        <div class="voice-card" data-voice="alloy">
                            <div class="voice-icon">🎙️</div>
                            <div class="voice-name">Alloy</div>
                            <div class="voice-desc">Professional</div>
                        </div>
                        <div class="voice-card" data-voice="echo">
                            <div class="voice-icon">🎵</div>
                            <div class="voice-name">Echo</div>
                            <div class="voice-desc">Warm</div>
                        </div>
                        <div class="voice-card" data-voice="fable">
                            <div class="voice-icon">📖</div>
                            <div class="voice-name">Fable</div>
                            <div class="voice-desc">Story</div>
                        </div>
                        <div class="voice-card" data-voice="onyx">
                            <div class="voice-icon">🎭</div>
                            <div class="voice-name">Onyx</div>
                            <div class="voice-desc">Deep</div>
                        </div>
                        <div class="voice-card" data-voice="nova">
                            <div class="voice-icon">⭐</div>
                            <div class="voice-name">Nova</div>
                            <div class="voice-desc">Energetic</div>
                        </div>
                        <div class="voice-card" data-voice="shimmer">
                            <div class="voice-icon">✨</div>
                            <div class="voice-name">Shimmer</div>
                            <div class="voice-desc">Elegant</div>
                        </div>
                        <div class="voice-card" data-voice="sage">
                            <div class="voice-icon">🧙</div>
                            <div class="voice-name">Sage</div>
                            <div class="voice-desc">Wise</div>
                        </div>
                        <div class="voice-card" data-voice="aria">
                            <div class="voice-icon">🎶</div>
                            <div class="voice-name">Aria</div>
                            <div class="voice-desc">Sweet</div>
                        </div>
                    </div>

                    <h3 style="margin: 24px 0 16px; font-size: 16px; color: #333;">Enter Script</h3>
                    <textarea id="scriptInput" placeholder="Enter your script here (max 20,000 characters)..." maxlength="20000"></textarea>
                    <div class="char-count"><span id="charCount">0</span> / 20,000 characters</div>

                    <button class="generate-btn" id="generateBtn" onclick="generateVoice()" disabled>
                        <span>🎙️</span>
                        <span>Generate Voice</span>
                    </button>

                    <div class="loading-spinner" id="loadingSpinner">
                        <div class="spinner"></div>
                        <p style="margin-top: 16px; color: #666;">Generating your voice...</p>
                    </div>
                </div>
            </div>

            <div>
                <div class="card">
                    <h2 class="card-title">📜 History</h2>
                    <div id="historyContainer">
                        <p style="text-align: center; color: #999; padding: 40px 0;">No history yet</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedVoice = '';
        const hasSubscription = <?php echo $user['has_subscription'] ? 'true' : 'false'; ?>;
        let currentCredits = <?php echo $user['credits']; ?>;

        // Voice selection
        document.querySelectorAll('.voice-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.voice-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                selectedVoice = this.dataset.voice;
                checkGenerateButton();
            });
        });

        // Script input
        const scriptInput = document.getElementById('scriptInput');
        const charCount = document.getElementById('charCount');

        scriptInput.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count.toLocaleString();
            checkGenerateButton();
        });

        function checkGenerateButton() {
            const btn = document.getElementById('generateBtn');
            const hasText = scriptInput.value.trim().length > 0;
            const hasVoice = selectedVoice !== '';
            btn.disabled = !(hasText && hasVoice && hasSubscription);
        }

        async function generateVoice() {
            if (!hasSubscription) {
                showAlert('Please subscribe to generate voice!', 'error');
                return;
            }

            const script = scriptInput.value.trim();
            const charLength = script.length;

            if (charLength === 0) {
                showAlert('Please enter a script!', 'error');
                return;
            }

            if (!selectedVoice) {
                showAlert('Please select a voice!', 'error');
                return;
            }

            if (currentCredits < charLength) {
                showAlert('Insufficient credits! You need ' + charLength.toLocaleString() + ' credits.', 'error');
                return;
            }

            // Show loading
            document.getElementById('generateBtn').style.display = 'none';
            document.getElementById('loadingSpinner').style.display = 'block';

            try {
                const response = await fetch('api/generate_voice.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        voice: selectedVoice,
                        script: script
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Voice generated successfully!', 'success');
                    currentCredits = result.remaining_credits;
                    document.getElementById('creditDisplay').textContent = currentCredits.toLocaleString();
                    scriptInput.value = '';
                    charCount.textContent = '0';
                    loadHistory();
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Generation failed! Please try again.', 'error');
            }

            // Hide loading
            document.getElementById('generateBtn').style.display = 'flex';
            document.getElementById('loadingSpinner').style.display = 'none';
        }

        async function loadHistory() {
            try {
                const response = await fetch('api/get_history.php');
                const result = await response.json();

                if (result.success && result.history.length > 0) {
                    displayHistory(result.history);
                }
            } catch (error) {
                console.error('Failed to load history');
            }
        }

        function displayHistory(history) {
            const container = document.getElementById('historyContainer');
            container.innerHTML = history.map(item => `
                <div class="history-item">
                    <div class="history-voice">${item.voice_name}</div>
                    <div class="history-text">${item.script_text}</div>
                    <div style="font-size: 11px; color: #999;">${item.character_count} characters • ${new Date(item.created_at).toLocaleDateString()}</div>
                    <div class="history-actions">
                        <button class="play-btn" onclick="playAudio('${item.audio_url}')">▶️ Play</button>
                        <button class="download-btn" onclick="downloadAudio('${item.audio_url}')">⬇️ Download</button>
                    </div>
                </div>
            `).join('');
        }

        function playAudio(url) {
            const audio = new Audio(url);
            audio.play();
        }

        function downloadAudio(url) {
            window.open(url, '_blank');
        }

        function showAlert(message, type) {
            const alert = document.getElementById('alert');
            alert.className = 'alert alert-' + type;
            alert.textContent = message;
            alert.style.display = 'block';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        }

        async function initiatePayment() {
            window.location.href = 'payment.php';
        }

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }

        // Load history on page load
        loadHistory();
    </script>
</body>
</html>