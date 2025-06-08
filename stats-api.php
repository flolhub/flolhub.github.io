<?php
header('Content-Type: application/json');
require_once 'track-visits.php';
echo json_encode($stats);
?>

<script>
fetch('stats-api.php')
  .then(response => response.json())
  .then(data => {
    document.getElementById('daily-visits').textContent = data.daily;
    document.getElementById('weekly-visits').textContent = data.weekly;
    document.getElementById('monthly-visits').textContent = data.monthly;
    document.getElementById('yearly-visits').textContent = data.yearly;
    document.getElementById('total-visits').textContent = data.total;
  });
</script>
