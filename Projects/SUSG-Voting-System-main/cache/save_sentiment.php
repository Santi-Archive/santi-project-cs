<?php
session_start();
require_once 'SentimentCache.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['key']) || !isset($data['value']) || !isset($data['election_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required data']);
    exit;
}

try {
    $cache = new SentimentCache();
    $cache->set($data['key'], $data['value'], $data['election_id']);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
