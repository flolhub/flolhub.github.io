<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name'], $data['score'], $data['date'], $data['mode'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid data']));
}

// Load existing scores
$scores = [];
if (file_exists('scores.json')) {
    $scores = json_decode(file_get_contents('scores.json'), true);
}

// Add new score
$scores[] = [
    'name' => htmlspecialchars($data['name']),
    'score' => (int)$data['score'],
    'date' => $data['date'],
    'mode' => htmlspecialchars($data['mode'])
];

// Sort by score (descending)
usort($scores, function($a, $b) {
    return $b['score'] - $a['score'];
});

// Keep only top 100 scores
$scores = array_slice($scores, 0, 100);

// Save back to file
file_put_contents('scores.json', json_encode($scores));

echo json_encode(['success' => true]);
?>
