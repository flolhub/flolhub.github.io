<?php
// Get list of all tracked pages
$pages = array_map(
    fn($f) => pathinfo($f, PATHINFO_FILENAME),
    glob('stats/*.json')
);
$currentPage = $_GET['page'] ?? 'all_pages';
$stats = json_decode(file_get_contents("stats/$currentPage.json"), true);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Visit Statistics</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .card { 
            background: #f8f9fa; 
            border-radius: 8px; 
            padding: 20px; 
            margin: 10px 0; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: inline-block;
            width: 200px;
        }
        .card h3 { margin-top: 0; color: #333; }
        .card .value { 
            font-size: 24px; 
            font-weight: bold; 
            color: #007bff; 
            margin: 10px 0;
        }
        select { padding: 8px; margin-bottom: 20px; }
        #chart { margin-top: 30px; max-width: 800px; height: 400px; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Visit Statistics</h1>
    
    <select id="pageSelect" onchange="location.href='?page='+this.value">
        <?php foreach ($pages as $page): ?>
            <option value="<?= $page ?>" <?= $page === $currentPage ? 'selected' : '' ?>>
                <?= $page === 'all_pages' ? 'All Pages' : ucfirst($page) ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <div class="card">
        <h3>Today</h3>
        <div class="value"><?= $stats['daily'] ?></div>
    </div>
    
    <div class="card">
        <h3>This Week</h3>
        <div class="value"><?= $stats['weekly'] ?></div>
    </div>
    
    <div class="card">
        <h3>This Month</h3>
        <div class="value"><?= $stats['monthly'] ?></div>
    </div>
    
    <div class="card">
        <h3>This Year</h3>
        <div class="value"><?= $stats['yearly'] ?></div>
    </div>
    
    <div class="card">
        <h3>All Time</h3>
        <div class="value"><?= $stats['total'] ?></div>
    </div>
    
    <div>
        <canvas id="chart"></canvas>
    </div>
    
    <script>
        const history = <?= json_encode($stats['daily_history']) ?>;
        const labels = Object.keys(history).sort();
        const data = labels.map(date => history[date]);
        
        new Chart(document.getElementById('chart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Visits',
                    data: data,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
