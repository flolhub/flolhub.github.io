<?php
// Ensure stats directory exists
if (!file_exists('stats')) {
    mkdir('stats', 0755, true);
}

function updateVisitStats($pageId) {
    $statsFile = "stats/$pageId.json";
    $now = new DateTime();
    $today = $now->format('Y-m-d');
    
    // Initialize or load existing stats
    if (file_exists($statsFile)) {
        $stats = json_decode(file_get_contents($statsFile), true);
        // Backward compatibility for older versions
        if (!isset($stats['history'])) {
            $stats['history'] = [];
        }
    } else {
        $stats = [
            'total' => 0,
            'daily' => 0,
            'weekly' => 0,
            'monthly' => 0,
            'yearly' => 0,
            'last_visit' => $today,
            'history' => []
        ];
    }

    // Reset counters if needed
    $lastVisit = new DateTime($stats['last_visit']);
    
    // Check if we need to reset daily counter (new day)
    if ($today != $stats['last_visit']) {
        $stats['daily'] = 0;
        
        // Check if we need to reset weekly counter (new week)
        if ($now->format('W') != $lastVisit->format('W') || 
            $now->format('Y') != $lastVisit->format('Y')) {
            $stats['weekly'] = 0;
        }
        
        // Check if we need to reset monthly counter (new month)
        if ($now->format('m') != $lastVisit->format('m')) {
            $stats['monthly'] = 0;
        }
        
        // Check if we need to reset yearly counter (new year)
        if ($now->format('Y') != $lastVisit->format('Y')) {
            $stats['yearly'] = 0;
        }
    }

    // Update counters
    $stats['total']++;
    $stats['daily']++;
    $stats['weekly']++;
    $stats['monthly']++;
    $stats['yearly']++;
    $stats['last_visit'] = $today;
    
    // Record daily history (last 30 days)
    if (!isset($stats['history'][$today])) {
        $stats['history'][$today] = 0;
        
        // Keep only last 30 days
        if (count($stats['history']) > 30) {
            $oldestDate = min(array_keys($stats['history']));
            unset($stats['history'][$oldestDate]);
        }
    }
    $stats['history'][$today]++;
    
    // Save updated stats
    file_put_contents($statsFile, json_encode($stats));
    
    return $stats;
}

// Handle API requests
if (isset($_GET['page'])) {
    $pageId = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $_GET['page']);
    $stats = updateVisitStats($pageId);
    
    // If asking for sitewide stats, ensure we don't double-count
    if ($pageId === 'sitewide_stats') {
        $currentPage = basename($_SERVER['PHP_SELF'], '.php');
        if ($currentPage !== 'stats-api' && $currentPage !== 'track-visits') {
            updateVisitStats($currentPage);
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($stats);
    exit;
}

// Handle page list request
if (isset($_GET['action']) && $_GET['action'] === 'list_pages') {
    $files = glob('stats/*.json');
    $pages = array_map(function($file) {
        return basename($file);
    }, $files);
    
    header('Content-Type: application/json');
    echo json_encode(['pages' => $pages]);
    exit;
}

// Normal page visit tracking (when included on regular pages)
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
if (!in_array($currentPage, ['stats-api', 'track-visits'])) {
    // Track individual page
    $pageStats = updateVisitStats($currentPage);
    
    // Track sitewide stats
    $siteStats = updateVisitStats('sitewide_stats');
    
    // Make stats available to the page
    $GLOBALS['pageVisitStats'] = $pageStats;
    $GLOBALS['siteVisitStats'] = $siteStats;
}
?>
