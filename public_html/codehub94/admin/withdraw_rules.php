<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit();
}
include "conn.php";
include "header.php";

// === AUTO FIX COLUMNS ===
$conn->query("ALTER TABLE withdrawal_rules 
    ADD COLUMN IF NOT EXISTS `bet_multiplier` DECIMAL(5,2) NOT NULL DEFAULT 1.00 AFTER `maxPrice`,
    ADD COLUMN IF NOT EXISTS `need_to_bet_enabled` TINYINT(1) NOT NULL DEFAULT 1 AFTER `bet_multiplier`,
    ADD COLUMN IF NOT EXISTS `daily_withdraw_limit` TINYINT(2) NOT NULL DEFAULT 3 AFTER `need_to_bet_enabled`");

// === FETCH RULES ===
$rules = [];
$result = $conn->query("SELECT * FROM withdrawal_rules ORDER BY FIELD(withdraw_type, 4, 3, 0)");
while ($row = $result->fetch_assoc()) {
    $rules[$row['withdraw_type']] = $row;
}

// === DEFAULTS IF MISSING ===
$defaults = [
    0 => ['startTime'=>'00:00:00','endTime'=>'23:59:59','fee'=>0,'minPrice'=>110,'maxPrice'=>50000,'bet_multiplier'=>1.00,'need_to_bet_enabled'=>1,'daily_withdraw_limit'=>3],
    3 => ['startTime'=>'00:00:00','endTime'=>'23:59:59','fee'=>0,'minPrice'=>110,'maxPrice'=>10000,'bet_multiplier'=>1.00,'need_to_bet_enabled'=>1,'daily_withdraw_limit'=>5],
    4 => ['startTime'=>'00:00:00','endTime'=>'23:59:59','fee'=>0,'minPrice'=>110,'maxPrice'=>50000,'bet_multiplier'=>1.00,'need_to_bet_enabled'=>1,'daily_withdraw_limit'=>3]
];

foreach ($defaults as $type => $vals) {
    if (!isset($rules[$type])) {
        $stmt = $conn->prepare("INSERT INTO withdrawal_rules 
            (withdraw_type, startTime, endTime, fee, minPrice, maxPrice, bet_multiplier, need_to_bet_enabled, daily_withdraw_limit) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issddddii", $type, ...array_values($vals));
        $stmt->execute(); $stmt->close();
        $rules[$type] = array_merge(['id'=>0, 'withdraw_type'=>$type], $vals);
    }
}

// === UPDATE LOGIC ===
$msg = $errors = [];
if ($_POST['action'] ?? '' === 'update') {
    $id = intval($_POST['id']);
    $type = intval($_POST['withdraw_type']);
    $startTime = $_POST['startTime'] ?? '';
    $endTime = $_POST['endTime'] ?? '';
    $fee_percent = floatval($_POST['fee_percent'] ?? 0);
    $fee = $fee_percent / 100;
    $minPrice = floatval($_POST['minPrice'] ?? 110);
    $maxPrice = floatval($_POST['maxPrice'] ?? 50000);
    $bet_multiplier = floatval($_POST['bet_multiplier'] ?? 1);
    $need_to_bet = isset($_POST['need_to_bet_enabled']) ? 1 : 0;
    $daily_limit = intval($_POST['daily_withdraw_limit'] ?? 3);

    if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $startTime)) $errors[] = "Invalid Start Time";
    if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $endTime)) $errors[] = "Invalid End Time";
    if ($fee_percent < 0 || $fee_percent > 10) $errors[] = "Fee must be 0-10%";
    if ($minPrice < 110) $errors[] = "Minimum Amount cannot be less than 110";
    if ($maxPrice < $minPrice) $errors[] = "Max Price must be >= Min Price";
    if ($bet_multiplier <= 0) $errors[] = "Bet Multiplier must be > 0";
    if ($daily_limit < 0) $errors[] = "Daily limit cannot be negative";

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE withdrawal_rules SET 
            startTime=?, endTime=?, fee=?, minPrice=?, maxPrice=?, 
            bet_multiplier=?, need_to_bet_enabled=?, daily_withdraw_limit=? 
            WHERE id=?");
        $stmt->bind_param("ssddddiii", $startTime, $endTime, $fee, $minPrice, $maxPrice, $bet_multiplier, $need_to_bet, $daily_limit, $id);
        if ($stmt->execute()) {
            $msg[] = "Settings saved!";
            $rules[$type] = array_merge($rules[$type], [
                'startTime'=>$startTime,'endTime'=>$endTime,'fee'=>$fee,'minPrice'=>$minPrice,'maxPrice'=>$maxPrice,
                'bet_multiplier'=>$bet_multiplier,'need_to_bet_enabled'=>$need_to_bet,'daily_withdraw_limit'=>$daily_limit
            ]);
        } else {
            $errors[] = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}

// === GET ACTIVE TAB FROM URL ===
$active_tab = 'wallet'; // default
$hash = $_SERVER['REQUEST_URI'] ?? '';
if (preg_match('/#(\w+)/', $hash, $m)) {
    $tab = strtolower($m[1]);
    if (in_array($tab, ['wallet', 'trc', 'other'])) {
        $active_tab = $tab;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Withdrawal Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --success: #06d6a0;
            --danger: #ef476f;
            --dark: #2d3436;
            --light: #f8f9fa;
            --gray: #e9ecef;
            --radius: 16px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e9fd 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            padding: 24px 16px;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
        }

        /* MESSAGES */
        .msg {
            padding: 14px 20px;
            margin-bottom: 24px;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            animation: slideIn 0.4s ease;
        }
        .msg.success { background: #d1f2eb; color: #0b6b5a; border-left: 5px solid var(--success); }
        .msg.error { background: #fadbd8; color: #6e1c1a; border-left: 5px solid var(--danger); }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-15px); } to { opacity: 1; transform: translateY(0); } }

        /* TABS */
        .tabs {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            background: #fff;
            border-radius: var(--radius);
            padding: 10px;
            margin-bottom: 28px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            gap: 16px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .tabs::-webkit-scrollbar { display: none; }
        .tab {
            flex: 1;
            min-width: 160px;
            padding: 16px 20px;
            text-align: center;
            border-radius: 14px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
            color: #555;
            background: #f1f3f5;
        }
        .tab.active {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 15px rgba(67,97,238,0.3);
            transform: translateY(-2px);
        }
        @media (max-width: 768px) {
            .tab { min-width: 140px; padding: 14px 16px; font-size: 15px; }
        }

        .tab-content { display: none; animation: fadeIn 0.5s ease; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .card {
            background: #fff;
            border-radius: var(--radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        .card:hover { transform: translateY(-4px); }
        .card h3 {
            margin: 0 0 20px;
            font-size: 19px;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 22px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14.5px;
            color: #495057;
        }
        input, select {
            width: 100%;
            padding: 13px;
            border: 1.8px solid #dee2e6;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #fff;
        }
        input:focus, select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(67,97,238,0.15);
            outline: none;
        }

        /* FEE PERCENTAGE */
        .fee-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .fee-group select, .fee-group input {
            flex: 1;
        }
        .fee-group .percent-sign {
            font-weight: 700;
            color: #495057;
            font-size: 16px;
        }

        .switch { position: relative; display: inline-block; width: 52px; height: 28px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #ccc; transition: .4s; border-radius: 28px; }
        .slider:before { position: absolute; content: ""; height: 22px; width: 22px; left: 3px; bottom: 3px; background: white; transition: .4s; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.2); }
        input:checked + .slider { background: var(--success); }
        input:checked + .slider:before { transform: translateX(24px); }

        .btn {
            padding: 14px 32px;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            font-size: 15.5px;
            font-weight: 700;
            transition: all 0.3s ease;
            margin-top: 18px;
        }
        .btn-save { background: var(--success); color: #fff; }
        .btn-save:hover { background: #05b589; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(6,214,160,0.3); }

        table { width: 100%; border-collapse: collapse; margin-top: 18px; font-size: 14.5px; }
        th, td { padding: 14px 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #555; font-size: 13px; text-transform: uppercase; }
        .badge { padding: 8px 16px; border-radius: 24px; font-size: 12px; font-weight: bold; }
        .badge-wallet { background: #d4edda; color: #155724; }
        .badge-trc { background: #fff3cd; color: #856404; }
        .badge-other { background: #e7f3ff; color: #0c5db8; }

        @media (max-width: 768px) {
            .container { padding: 16px; }
            .form-grid { grid-template-columns: 1fr; gap: 18px; }
            .card { padding: 24px; }
            .tabs { padding: 8px; gap: 12px; }
            .tab { min-width: 130px; padding: 12px 16px; font-size: 14.5px; }
            .fee-group { flex-direction: column; }
            .fee-group .percent-sign { margin-left: 0; margin-top: 8px; }
            table { font-size: 13.5px; }
            th, td { padding: 10px 8px; }
        }
    </style>
</head>
<body>
<div class="container">
    <?php if ($msg): ?>
        <div class="msg success"><?php echo implode('<br>', array_map('htmlspecialchars', $msg)); ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="msg error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
    <?php endif; ?>

    <!-- TABS -->
    <div class="tabs">
        <div class="tab <?php echo $active_tab==='wallet'?'active':''; ?>" data-tab="wallet">E-Wallet</div>
        <div class="tab <?php echo $active_tab==='trc'?'active':''; ?>" data-tab="trc">TRC</div>
        <div class="tab <?php echo $active_tab==='other'?'active':''; ?>" data-tab="other">Other</div>
    </div>

    <!-- E-WALLET -->
    <div id="tab-wallet" class="tab-content <?php echo $active_tab==='wallet'?'active':''; ?>">
        <div class="card">
            <h3>E-Wallet Settings</h3>
            <?php $r = $rules[4]; $fee_percent = $r['fee'] * 100; ?>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                <input type="hidden" name="withdraw_type" value="4">
                <div class="form-grid">
                    <div><label>Start Time</label><input type="text" name="startTime" class="flatpickr" value="<?php echo htmlspecialchars($r['startTime']); ?>" required></div>
                    <div><label>End Time</label><input type="text" name="endTime" class="flatpickr" value="<?php echo htmlspecialchars($r['endTime']); ?>" required></div>

                    <div>
                        <label>Fee (%)</label>
                        <div class="fee-group">
                            <select name="fee_percent" id="fee-select-wallet" onchange="syncFeeInput(this, 'fee-input-wallet')">
                                <option value="">Custom</option>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $fee_percent == $i ? 'selected' : ''; ?>><?php echo $i; ?>%</option>
                                <?php endfor; ?>
                            </select>
                            <input type="number" step="0.01" min="0" max="10" name="fee_percent" id="fee-input-wallet" 
                                   value="<?php echo $fee_percent > 10 || $fee_percent == 0 ? '' : $fee_percent; ?>" 
                                   placeholder="0-10" oninput="syncFeeSelect(this, 'fee-select-wallet')">
                            <span class="percent-sign">%</span>
                        </div>
                    </div>

                    <div><label>Min Amount (≥110)</label><input type="number" step="0.01" min="110" name="minPrice" value="<?php echo number_format($r['minPrice'], 2, '.', ''); ?>" required></div>
                    <div><label>Max Amount</label><input type="number" step="0.01" name="maxPrice" value="<?php echo number_format($r['maxPrice'], 2, '.', ''); ?>" required></div>
                    <div><label>Bet ×</label><input type="number" step="0.01" name="bet_multiplier" value="<?php echo number_format($r['bet_multiplier'], 2, '.', ''); ?>" required></div>
                    <div><label>Need Bet</label><label class="switch"><input type="checkbox" name="need_to_bet_enabled" <?php echo $r['need_to_bet_enabled'] ? 'checked' : ''; ?>><span class="slider"></span></label></div>
                    <div><label>Daily Limit</label><input type="number" min="0" name="daily_withdraw_limit" value="<?php echo $r['daily_withdraw_limit']; ?>" required></div>
                </div>
                <button type="submit" class="btn btn-save">Save E-Wallet</button>
            </form>
        </div>
    </div>

    <!-- TRC -->
    <div id="tab-trc" class="tab-content <?php echo $active_tab==='trc'?'active':''; ?>">
        <div class="card">
            <h3>TRC Settings</h3>
            <?php $r = $rules[3]; $fee_percent = $r['fee'] * 100; ?>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                <input type="hidden" name="withdraw_type" value="3">
                <div class="form-grid">
                    <div><label>Start Time</label><input type="text" name="startTime" class="flatpickr" value="<?php echo htmlspecialchars($r['startTime']); ?>" required></div>
                    <div><label>End Time</label><input type="text" name="endTime" class="flatpickr" value="<?php echo htmlspecialchars($r['endTime']); ?>" required></div>

                    <div>
                        <label>Fee (%)</label>
                        <div class="fee-group">
                            <select name="fee_percent" id="fee-select-trc" onchange="syncFeeInput(this, 'fee-input-trc')">
                                <option value="">Custom</option>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $fee_percent == $i ? 'selected' : ''; ?>><?php echo $i; ?>%</option>
                                <?php endfor; ?>
                            </select>
                            <input type="number" step="0.01" min="0" max="10" name="fee_percent" id="fee-input-trc" 
                                   value="<?php echo $fee_percent > 10 || $fee_percent == 0 ? '' : $fee_percent; ?>" 
                                   placeholder="0-10" oninput="syncFeeSelect(this, 'fee-select-trc')">
                            <span class="percent-sign">%</span>
                        </div>
                    </div>

                    <div><label>Min Amount (≥110)</label><input type="number" step="0.01" min="110" name="minPrice" value="<?php echo number_format($r['minPrice'], 2, '.', ''); ?>" required></div>
                    <div><label>Max Amount</label><input type="number" step="0.01" name="maxPrice" value="<?php echo number_format($r['maxPrice'], 2, '.', ''); ?>" required></div>
                    <div><label>Bet ×</label><input type="number" step="0.01" name="bet_multiplier" value="<?php echo number_format($r['bet_multiplier'], 2, '.', ''); ?>" required></div>
                    <div><label>Need Bet</label><label class="switch"><input type="checkbox" name="need_to_bet_enabled" <?php echo $r['need_to_bet_enabled'] ? 'checked' : ''; ?>><span class="slider"></span></label></div>
                    <div><label>Daily Limit</label><input type="number" min="0" name="daily_withdraw_limit" value="<?php echo $r['daily_withdraw_limit']; ?>" required></div>
                </div>
                <button type="submit" class="btn btn-save">Save TRC</button>
            </form>
        </div>
    </div>

    <!-- OTHER -->
    <div id="tab-other" class="tab-content <?php echo $active_tab==='other'?'active':''; ?>">
        <div class="card">
            <h3>Other Settings (Global)</h3>
            <?php $r = $rules[0]; $fee_percent = $r['fee'] * 100; ?>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                <input type="hidden" name="withdraw_type" value="0">
                <div class="form-grid">
                    <div><label>Start Time</label><input type="text" name="startTime" class="flatpickr" value="<?php echo htmlspecialchars($r['startTime']); ?>" required></div>
                    <div><label>End Time</label><input type="text" name="endTime" class="flatpickr" value="<?php echo htmlspecialchars($r['endTime']); ?>" required></div>

                    <div>
                        <label>Fee (%)</label>
                        <div class="fee-group">
                            <select name="fee_percent" id="fee-select-other" onchange="syncFeeInput(this, 'fee-input-other')">
                                <option value="">Custom</option>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $fee_percent == $i ? 'selected' : ''; ?>><?php echo $i; ?>%</option>
                                <?php endfor; ?>
                            </select>
                            <input type="number" step="0.01" min="0" max="10" name="fee_percent" id="fee-input-other" 
                                   value="<?php echo $fee_percent > 10 || $fee_percent == 0 ? '' : $fee_percent; ?>" 
                                   placeholder="0-10" oninput="syncFeeSelect(this, 'fee-select-other')">
                            <span class="percent-sign">%</span>
                        </div>
                    </div>

                    <div><label>Min Amount (≥110)</label><input type="number" step="0.01" min="110" name="minPrice" value="<?php echo number_format($r['minPrice'], 2, '.', ''); ?>" required></div>
                    <div><label>Max Amount</label><input type="number" step="0.01" name="maxPrice" value="<?php echo number_format($r['maxPrice'], 2, '.', ''); ?>" required></div>
                    <div><label>Bet ×</label><input type="number" step="0.01" name="bet_multiplier" value="<?php echo number_format($r['bet_multiplier'], 2, '.', ''); ?>" required></div>
                    <div><label>Need Bet</label><label class="switch"><input type="checkbox" name="need_to_bet_enabled" <?php echo $r['need_to_bet_enabled'] ? 'checked' : ''; ?>><span class="slider"></span></label></div>
                    <div><label>Daily Limit</label><input type="number" min="0" name="daily_withdraw_limit" value="<?php echo $r['daily_withdraw_limit']; ?>" required></div>
                </div>
                <button type="submit" class="btn btn-save">Save Other</button>
            </form>
        </div>
    </div>

    <!-- SUMMARY -->
    <div class="card">
        <h3>Summary</h3>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Time</th>
                        <th>Fee</th>
                        <th>Min</th>
                        <th>Max</th>
                        <th>Bet ×</th>
                        <th>Need</th>
                        <th>Daily</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ([4,3,0] as $type): $r = $rules[$type]; ?>
                    <tr>
                        <td><span class="badge <?php echo $type==4?'badge-wallet':($type==3?'badge-trc':'badge-other'); ?>">
                            <?php echo $type==4?'E-Wallet':($type==3?'TRC':'Other'); ?>
                        </span></td>
                        <td><?php echo substr($r['startTime'],0,5); ?>-<?php echo substr($r['endTime'],0,5); ?></td>
                        <td><?php echo number_format($r['fee'] * 100, 2); ?>%</td>
                        <td><?php echo number_format($r['minPrice'], 2); ?></td>
                        <td><?php echo number_format($r['maxPrice'], 2); ?></td>
                        <td><?php echo number_format($r['bet_multiplier'], 2); ?>x</td>
                        <td><?php echo $r['need_to_bet_enabled'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $r['daily_withdraw_limit']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.js"></script>
<script>
    // Tabs with URL hash
    const tabs = document.querySelectorAll('.tab');
    const contents = document.querySelectorAll('.tab-content');

    function openTab(tabName) {
        tabs.forEach(t => t.classList.remove('active'));
        contents.forEach(c => c.classList.remove('active'));
        document.querySelector(`.tab[data-tab="${tabName}"]`).classList.add('active');
        document.getElementById(`tab-${tabName}`).classList.add('active');
        history.replaceState(null, null, `#${tabName}`);
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => openTab(tab.dataset.tab));
    });

    // On load: open correct tab
    const hash = window.location.hash.substring(1) || 'wallet';
    if (['wallet', 'trc', 'other'].includes(hash)) {
        openTab(hash);
    }

    // Sync dropdown and input
    function syncFeeInput(select, inputId) {
        const input = document.getElementById(inputId);
        if (select.value) input.value = select.value;
    }
    function syncFeeSelect(input, selectId) {
        const select = document.getElementById(selectId);
        if (input.value === '' || parseFloat(input.value) > 10 || parseFloat(input.value) < 0) {
            select.value = '';
        } else if ([1,2,3,4,5,6,7,8,9,10].includes(parseInt(input.value))) {
            select.value = input.value;
        }
    }

    // Flatpickr
    flatpickr(".flatpickr", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i:S",
        time_24hr: true
    });

    // Auto hide messages
    setTimeout(() => {
        document.querySelectorAll('.msg').forEach(m => {
            m.style.transition = 'opacity 0.5s';
            m.style.opacity = '0';
            setTimeout(() => m.remove(), 500);
        });
    }, 4000);
</script>
</body>
</html>