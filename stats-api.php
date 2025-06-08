<?php
header('Content-Type: application/json');
require_once 'track-visits.php';

$action = $_GET['action'] ?? '';
$pageId = $_GET['page'] ?? 'sitewide_stats';

if ($action === 'list_pages') {
    // List all available JSON files
    $files = glob('stats/*.json');
    $pages = array_map(function($file) {
        return basename($file);
    }, $files);
    
    echo json_encode(['pages' => $pages]);
    exit;
}

// Get stats for specific page
if (file_exists("stats/$pageId.json")) {
    $stats = json_decode(file_get_contents("stats/$pageId.json"), true);
    echo json_encode($stats);
} else {
    echo json_encode([
        'daily' => 0,
        'weekly' => 0,
        'monthly' => 0,
        'yearly' => 0,
        'total' => 0,
        'history' => []
    ]);
}
?>
