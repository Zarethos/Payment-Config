<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$voice = $input['voice'] ?? '';
$script = trim($input['script'] ?? '');

// Validation
if (empty($voice) || empty($script)) {
    echo json_encode(['success' => false, 'message' => 'Voice and script are required']);
    exit;
}

$charCount = strlen($script);

if ($charCount > 20000) {
    echo json_encode(['success' => false, 'message' => 'Script exceeds 20,000 characters limit']);
    exit;
}

try {
    $user = getUserData($pdo, $_SESSION['user_id']);
    
    // Check subscription
    if (!$user['has_subscription']) {
        echo json_encode(['success' => false, 'message' => 'Please subscribe to generate voice']);
        exit;
    }
    
    // Check credits
    if ($user['credits'] < $charCount) {
        echo json_encode(['success' => false, 'message' => 'Insufficient credits']);
        exit;
    }
    
    // Generate voice using Google Text-to-Speech API or any TTS service
    // For demo, we'll create a dummy audio URL
    $audioUrl = generateVoiceAudio($voice, $script);
    
    if (!$audioUrl) {
        echo json_encode(['success' => false, 'message' => 'Voice generation failed']);
        exit;
    }
    
    // Deduct credits
    $newCredits = $user['credits'] - $charCount;
    $stmt = $pdo->prepare("UPDATE users SET credits = ? WHERE id = ?");
    $stmt->execute([$newCredits, $user['id']]);
    
    // Save to history
    $stmt = $pdo->prepare("
        INSERT INTO voice_history (user_id, voice_name, script_text, character_count, audio_url) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user['id'], ucfirst($voice), $script, $charCount, $audioUrl]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Voice generated successfully',
        'audio_url' => $audioUrl,
        'remaining_credits' => $newCredits,
        'characters_used' => $charCount
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Generation failed: ' . $e->getMessage()]);
}

function generateVoiceAudio($voice, $script) {
    /*
     * IMPORTANT: Integrate your actual TTS API here
     * 
     * Options:
     * 1. Google Cloud Text-to-Speech API
     * 2. Amazon Polly
     * 3. Microsoft Azure Speech
     * 4. ElevenLabs API
     * 5. OpenAI TTS API
     * 
     * Example with Google Cloud TTS:
     */
    
    // Uncomment and configure for actual TTS
    /*
    $apiKey = GEMINI_API_KEY; // or use appropriate TTS API key
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://texttospeech.googleapis.com/v1/text:synthesize?key=' . $apiKey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'input' => ['text' => $script],
        'voice' => [
            'languageCode' => 'en-US',
            'name' => 'en-US-' . $voice
        ],
        'audioConfig' => ['audioEncoding' => 'MP3']
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);
    
    if (isset($result['audioContent'])) {
        // Save audio file
        $filename = uniqid('voice_') . '.mp3';
        $filepath = '../audio/' . $filename;
        file_put_contents($filepath, base64_decode($result['audioContent']));
        return 'audio/' . $filename;
    }
    */
    
    // For demo purposes - return a sample audio URL
    // Replace this with actual TTS generation
    return 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3';
}

// Alternative: OpenAI TTS API Implementation
function generateVoiceWithOpenAI($voice, $script) {
    $apiKey = 'YOUR_OPENAI_API_KEY';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/audio/speech');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => 'tts-1',
        'input' => $script,
        'voice' => $voice
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    
    $audioData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        // Create audio directory if not exists
        if (!file_exists('../audio')) {
            mkdir('../audio', 0755, true);
        }
        
        $filename = uniqid('voice_') . '.mp3';
        $filepath = '../audio/' . $filename;
        file_put_contents($filepath, $audioData);
        
        return 'audio/' . $filename;
    }
    
    return false;
}
?>