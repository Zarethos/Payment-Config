<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("
        SELECT id, voice_name, script_text, character_count, audio_url, created_at 
        FROM voice_history 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 50
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'history' => $history
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load history'
    ]);
}
?>