<?php
$configFile = 'api_config.json';
$logFile = 'api_logs.json';

$responseData = null;
$curlError = null;
$httpcode = 0;

// Save Config
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $configData = [
        'api_url'  => $_POST['api_url'],
        'typeId'   => (int)$_POST['typeId'],
        'language' => (int)$_POST['language'],
        'pageNo'   => (int)$_POST['pageNo'],
        'pageSize' => (int)$_POST['pageSize']
    ];
    file_put_contents($configFile, json_encode($configData, JSON_PRETTY_PRINT));
    $_SESSION['message'] = "✅ Configuration saved!";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Load Config
$config = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [
    'api_url'  => '',
    'typeId'   => 3,
    'language' => 0,
    'pageNo'   => 1,
    'pageSize' => 10
];

// Call API
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['call_api'])) {
    $random = bin2hex(random_bytes(16));
    $signatureString = '{"language":'.$config['language'].',"pageNo":'.$config['pageNo'].',"pageSize":'.$config['pageSize'].',"random":"'.$random.'","typeId":'.$config['typeId'].'}';
    $signature = strtoupper(md5($signatureString));
    $timestamp = time();

    $postDataArray = [
        'language'  => $config['language'],
        'pageNo'    => $config['pageNo'],
        'pageSize'  => $config['pageSize'],
        'random'    => $random,
        'typeId'    => $config['typeId'],
        'signature' => $signature,
        'timestamp' => $timestamp
    ];
    $postData = json_encode($postDataArray);

    $ch = curl_init($config['api_url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json;charset=UTF-8"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $curlError = curl_error($ch);
    }

    curl_close($ch);
    $responseData = json_decode($response, true);

    $logEntry = [
        'timestamp' => date("Y-m-d H:i:s"),
        'status'    => $httpcode,
        'typeId'    => $config['typeId'],
        'payload'   => $postData,
        'response'  => $responseData ?? ['error' => $curlError ?? 'No response'],
        'raw'       => $response ?: 'Empty'
    ];

    $existingLogs = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
    array_unshift($existingLogs, $logEntry);
    file_put_contents($logFile, json_encode(array_slice($existingLogs, 0, 50), JSON_PRETTY_PRINT));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>API Config Manager + Logs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { background: #f4f6f8; }
        .card { margin-top: 30px; }
        .status-success { color: green; font-weight: bold; }
        .status-fail { color: red; font-weight: bold; }
        pre { background: #1e1e1e; color: #0f0; padding: 10px; border-radius: 6px; }
        .table-response td, .table-response th { font-size: 14px; }
    </style>
</head>
<body>
<div class="container">
    <div class="card p-4">
        <h3 class="mb-3">🚀 API Configuration</h3>
        <?php session_start(); if (!empty($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-3">
            <div class="col-md-12">
                <label class="form-label">API Endpoint URL</label>
                <input type="text" class="form-control" name="api_url" value="<?= htmlspecialchars($config['api_url']) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Game Type ID</label>
                <input type="number" class="form-control" name="typeId" value="<?= $config['typeId'] ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Language Code</label>
                <input type="number" class="form-control" name="language" value="<?= $config['language'] ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Page Number</label>
                <input type="number" class="form-control" name="pageNo" value="<?= $config['pageNo'] ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Page Size</label>
                <input type="number" class="form-control" name="pageSize" value="<?= $config['pageSize'] ?>" required>
            </div>
            <div class="col-md-6 d-grid">
                <button type="submit" name="save" class="btn btn-primary">💾 Save Config</button>
            </div>
            <div class="col-md-6 d-grid">
                <button type="submit" name="call_api" class="btn btn-success">📡 Call API Now</button>
            </div>
        </form>
    </div>

    <?php if ($responseData || $curlError): ?>
    <div class="card p-4 mt-4">
        <h4>📬 Latest Result (1 Record Only)</h4>
        <p>Status: 
            <span class="badge <?= ($httpcode === 200) ? 'bg-success' : 'bg-danger' ?>">
                HTTP <?= $httpcode ?>
            </span>
        </p>

        <?php if ($curlError): ?>
            <div class="alert alert-danger">❌ cURL Error: <?= htmlspecialchars($curlError) ?></div>
        <?php endif; ?>

        <?php
        $list = $responseData['data']['list'] ?? [];
        $latest = $list[0] ?? null;
        ?>

        <?php if ($latest): ?>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>🆔 Issue Number</th>
                        <th>🎯 Number</th>
                        <th>🎨 Colour</th>
                        <th>💰 Premium</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= htmlspecialchars($latest['issueNumber'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($latest['number'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($latest['colour'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($latest['premium'] ?? '-') ?></td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning">⚠️ No valid result data available.</div>
        <?php endif; ?>
    </div>
<?php endif; ?>


    <div class="card p-4 mt-4">
        <h4>📜 API Call History Logs</h4>
        <?php $logs = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : []; ?>
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-striped">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Time</th>
                        <th>Type ID</th>
                        <th>Status</th>
                        <th>Payload</th>
                        <th>Response</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($logs as $index => $log): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $log['timestamp'] ?></td>
                        <td><?= $log['typeId'] ?></td>
                        <td class="<?= ($log['status'] === 200) ? 'status-success' : 'status-fail' ?>">HTTP <?= $log['status'] ?></td>
                        <td><pre><?= json_encode(json_decode($log['payload']), JSON_PRETTY_PRINT) ?></pre></td>
                        <td><pre><?= is_array($log['response']) ? json_encode($log['response'], JSON_PRETTY_PRINT) : htmlspecialchars($log['response']) ?></pre></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
