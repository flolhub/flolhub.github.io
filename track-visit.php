<?php
// Create stats directory if it doesn't exist
if (!is_dir('stats')) {
    mkdir('stats', 0755, true);
}

function updateStats($pageName) {
    $file = "stats/$pageName.json";
    $now = date('Y-m-d');
    
    // Initialize or load existing stats
    $stats = file_exists($file) ? json_decode(file_get_contents($file), true) : [
        'total' => 0,
        'daily' => 0,
        'weekly' => 0,
        'monthly' => 0,
        'yearly' => 0,
        'last_updated' => $now,
        'daily_history' => []
    ];
    
    // Reset counters if it's a new period
    if ($stats['last_updated'] !== $now) {
        $stats['daily'] = 0;
        if (date('W') !== date('W', strtotime($stats['last_updated']))) {
            $stats['weekly'] = 0;
        }
        if (date('m') !== date('m', strtotime($stats['last_updated']))) {
            $stats['monthly'] = 0;
        }
        if (date('Y') !== date('Y', strtotime($stats['last_updated']))) {
            $stats['yearly'] = 0;
        }
    }
    
    // Update counters
    $stats['total']++;
    $stats['daily']++;
    $stats['weekly']++;
    $stats['monthly']++;
    $stats['yearly']++;
    $stats['last_updated'] = $now;
    
    // Update daily history (keep last 30 days)
    $stats['daily_history'][$now] = ($stats['daily_history'][$now] ?? 0) + 1;
    $stats['daily_history'] = array_slice($stats['daily_history'], -30, 30, true);
    
    // Save data
    file_put_contents($file, json_encode($stats));
    return $stats;
}

// Get current page name (or 'index' for home page)
$pageName = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_FILENAME) ?: 'index';
updateStats($pageName);
updateStats('all_pages'); // Combined stats for all pages
?>
