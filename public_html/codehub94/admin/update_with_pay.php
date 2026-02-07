<?php
session_start();
if (!isset($_SESSION['unohs']) || $_SESSION['unohs'] == null) {
    header("Location: index.php?msg=unauthorized");
    exit;
}

include 'conn.php'; // DB connection (mysqli $conn)

// Handle AJAX form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payid']) && isset($_POST['mode'])) {
    header('Content-Type: application/json');
    $payid = intval($_POST['payid']);
    $payName = $_POST['payName'] ?? '';
    $mode = $_POST['mode'] === 'AUTOMATIC' ? 1 : 0; // 1 for Automatic, 0 for Manual

    // Replace spaces with underscores for cleaner URLs
    $urlSafePayName = str_replace(' ', '_', $payName);

    // Set paySendUrl based on mode and payName
    if ($mode) {
        $paySendUrl = '/pay/' . $urlSafePayName; // Automatic mode: /pay/<payName>
    } else {
        $paySendUrl = '/pay/' . $urlSafePayName . '_Manual'; // Manual mode: /pay/<payName>_Manual
    }

    // Update database
    $stmt = $conn->prepare("UPDATE tbl_recharge_types SET use_pg_link = ?, paySendUrl = ? WHERE payid = ? LIMIT 1");
    $stmt->bind_param("isi", $mode, $paySendUrl, $payid);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Settings updated successfully.', 'paySendUrl' => $paySendUrl, 'mode' => $mode ? 'AUTOMATIC' : 'MANUAL']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $conn->error]);
    }
    $stmt->close();
    exit;
}

// Fetch all payment methods
$sel_sql = "SELECT payid, payName, paySysName, paySendUrl, use_pg_link FROM tbl_recharge_types ORDER BY payName ASC";
$result = mysqli_query($conn, $sel_sql);
$payment_methods = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $payment_methods[] = $row;
    }
}
?>
<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payment Methods</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-panel {
            padding: 20px;
        }
        .payment-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .payment-card:hover {
            transform: translateY(-5px);
        }
        .radio-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #f9fbff;
        }
        .radio-card input {
            accent-color: #007bff;
            transform: scale(1.2);
        }
        .radio-label {
            font-weight: 500;
            font-size: 16px;
            color: #333;
        }
        .radio-sub {
            font-size: 12px;
            color: #666;
        }
        .current-settings {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .success-popup {
            position: fixed;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            z-index: 1000;
            display: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            white-space: nowrap;
        }
        @media (max-width: 576px) {
            .payment-card {
                padding: 15px;
            }
            .radio-label {
                font-size: 14px;
            }
            .radio-sub {
                font-size: 11px;
            }
            .current-settings {
                margin-top: 20px;
            }
            .success-popup {
                font-size: 12px;
                padding: 6px 12px;
                width: auto;
                max-width: 90%;
            }
        }
    </style>
</head>
<body>
<div class="main-panel">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="text-center"><i class="fas fa-cog me-2"></i>Manage Payment Methods</h3>
            </div>
        </div>

        <div id="successPopup" class="success-popup">Success: Settings updated</div>

        <div class="row">
            <div class="col-lg-8 col-md-12 mb-4">
                <?php if (!empty($payment_methods)) : ?>
                    <?php foreach ($payment_methods as $index => $method) : ?>
                        <div class="payment-card">
                            <h5><i class="fas fa-credit-card me-2"></i><?php echo htmlspecialchars($method['payName']); ?></h5>
                            <form id="form-<?php echo $index; ?>">
                                <input type="hidden" name="payid" value="<?php echo intval($method['payid']); ?>">
                                <input type="hidden" name="payName" value="<?php echo htmlspecialchars($method['payName']); ?>">
                                <input type="hidden" name="paySysName" value="<?php echo htmlspecialchars($method['paySysName']); ?>">
                                <div class="form-group">
                                    <label class="radio-card">
                                        <input type="radio" name="mode-<?php echo $index; ?>" value="AUTOMATIC" <?php echo $method['use_pg_link'] ? 'checked' : ''; ?> onchange="updateMode(<?php echo $index; ?>, '<?php echo htmlspecialchars($method['payName']); ?>')">
                                        <div>
                                            <div class="radio-label"><i class="fas fa-sync-alt me-2"></i>Automatic</div>
                                            <div class="radio-sub" id="url-<?php echo $index; ?>">Path: <?php echo htmlspecialchars($method['paySendUrl']); ?></div>
                                        </div>
                                    </label>
                                    <label class="radio-card">
                                        <input type="radio" name="mode-<?php echo $index; ?>" value="MANUAL" <?php echo !$method['use_pg_link'] ? 'checked' : ''; ?> onchange="updateMode(<?php echo $index; ?>, '<?php echo htmlspecialchars($method['payName']); ?>')">
                                        <div>
                                            <div class="radio-label"><i class="fas fa-edit me-2"></i>Manual</div>
                                            <div class="radio-sub" id="manual-url-<?php echo $index; ?>">Path: <?php echo htmlspecialchars($method['paySendUrl']); ?></div>
                                        </div>
                                    </label>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No payment methods found.</div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="current-settings">
                    <h5><i class="fas fa-list me-2"></i>Current Settings</h5>
                    <?php if (!empty($payment_methods)) : ?>
                        <?php foreach ($payment_methods as $method) : ?>
                            <div class="radio-card mb-2" style="border-color:#28a745;">
                                <div>
                                    <div class="radio-label text-success"><i class="fas fa-credit-card me-2"></i><?php echo htmlspecialchars($method['payName']); ?> (<?php echo $method['use_pg_link'] ? 'AUTOMATIC' : 'MANUAL'; ?>)</div>
                                    <div class="radio-sub">Path: <?php echo htmlspecialchars($method['paySendUrl']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p><em><i class="fas fa-info-circle me-2"></i>No payment methods set.</em></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer mt-4">
        <div class="container text-center">
            <span class="text-muted">
                Copyright © Sol-0203.io
            </span>
        </div>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateMode(index, payName) {
    const form = document.getElementById(`form-${index}`);
    const mode = form.querySelector(`input[name="mode-${index}"]:checked`).value;
    const payid = form.querySelector('input[name="payid"]').value;
    const paySysName = form.querySelector('input[name="paySysName"]').value;
    const urlDisplay = document.getElementById(`url-${index}`);
    const manualUrlDisplay = document.getElementById(`manual-url-${index}`);

    // Replace spaces with underscores for cleaner URLs
    const urlSafePayName = payName.replace(/\s+/g, '_');

    // Determine paySendUrl based on mode and payName
    let paySendUrl = '';
    if (mode === 'AUTOMATIC') {
        paySendUrl = `/pay/${urlSafePayName}`;
    } else {
        paySendUrl = `/pay/${urlSafePayName}_Manual`;
    }

    // Update UI immediately
    urlDisplay.textContent = `Path: ${paySendUrl}`;
    manualUrlDisplay.textContent = `Path: ${paySendUrl}`;

    // Send AJAX request to save changes
    fetch('update_with_pay.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `payid=${payid}&mode=${mode}&payName=${encodeURIComponent(payName)}&paySysName=${encodeURIComponent(paySysName)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Show success popup
            const popup = document.getElementById('successPopup');
            popup.style.display = 'block';
            setTimeout(() => {
                popup.style.display = 'none';
            }, 2000);

            // Update current settings panel
            const settingsCards = document.querySelectorAll('.current-settings .radio-card');
            settingsCards.forEach(card => {
                if (card.querySelector('.radio-label').textContent.includes(payName)) {
                    card.querySelector('.radio-label').textContent = `${payName} (${data.mode})`;
                    card.querySelector('.radio-sub').textContent = `Path: ${data.paySendUrl}`;
                }
            });
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>
</body>
</html>