
<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (empty($_SESSION['unohs'])) {
    header("Location: index.php?msg=unauthorized");
    exit();
}
include("../conn.php");
// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Fetch default statistics
$today_recharge = 0;
$referrer_uid = null;
$today = date('Y-m-d');
$like_pattern = "%$today%";
$query = "SELECT SUM(motta) as total FROM thevani WHERE dinankavannuracisi LIKE ?";
$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param("s", $like_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $row = $result->fetch_assoc();
        $today_recharge = $row['total'] ?? 0;
    }
    $stmt->close();
} else {
    $error_message = "Error preparing today's recharge query: " . $conn->error;
}
$user_result = null;
$downline_data = [];
$error_message = '';
$display_results = false;
if (isset($_POST['submit'])) {
    $user_id = $_POST['user_id'];
    $display_results = true;
    $query = "SELECT * FROM shonu_subjects WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        $error_message = "Error preparing user query: " . $conn->error;
    } else {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($user_result && $user_result['owncode']) {
            $query = "SELECT id FROM shonu_subjects WHERE code1 = ? OR code2 = ? OR code3 = ? OR code4 = ? OR code5 = ?";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param("sssss",
                    $user_result['owncode'],
                    $user_result['owncode'],
                    $user_result['owncode'],
                    $user_result['owncode'],
                    $user_result['owncode']
                );
                $stmt->execute();
                $referrer_result = $stmt->get_result()->fetch_assoc();
                $referrer_uid = $referrer_result['id'] ?? 'N/A';
                $stmt->close();
            } else {
                $error_message = "Error preparing referrer query: " . $conn->error;
            }
        }
        if ($user_result) {
            $query = "SELECT * FROM thevani WHERE balakedara = ? ORDER BY dinankavannuracisi ASC";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                $error_message = "Error preparing deposit query: " . $conn->error;
            } else {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $deposit_result = $stmt->get_result();
                $first_deposit = $second_deposit = $third_deposit = null;
                $total_deposit = 0;
                $today_deposit = 0;
                while ($row = $deposit_result->fetch_assoc()) {
                    $total_deposit += $row['motta'];
                    if (!$first_deposit) $first_deposit = $row['motta'];
                    elseif (!$second_deposit) $second_deposit = $row['motta'];
                    elseif (!$third_deposit) $third_deposit = $row['motta'];
                    if (strpos($row['dinankavannuracisi'], $today) !== false) {
                        $today_deposit += $row['motta'];
                    }
                }
                $stmt->close();
                $query = "SELECT * FROM hintegedukolli WHERE balakedara = ?";
                $stmt = $conn->prepare($query);
                if (!$stmt) {
                    $error_message = "Error preparing withdrawal query: " . $conn->error;
                } else {
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $withdrawal_result = $stmt->get_result();
                    $withdrawals_today = 0;
                    $first_withdrawal = $second_withdrawal = $third_withdrawal = null;
                    while ($row = $withdrawal_result->fetch_assoc()) {
                        $withdrawals_today += $row['motta'];
                        if (!$first_withdrawal) $first_withdrawal = $row['motta'];
                        elseif (!$second_withdrawal) $second_withdrawal = $row['motta'];
                        elseif (!$third_withdrawal) $third_withdrawal = $row['motta'];
                    }
                    $stmt->close();
                    $codes = [
                        'Code1' => $user_result['code1'],
                        'Code2' => $user_result['code2'],
                        'Code3' => $user_result['code3'],
                        'Code4' => $user_result['code4'],
                        'Code5' => $user_result['code5']
                    ];
                    $total_downline_deposit_today = 0;
                    $total_downline_withdrawal_today = 0;
                    $total_downline_deposit = 0;
                    foreach ($codes as $position => $own_code) {
                        if ($own_code) {
                            $query = "SELECT id FROM shonu_subjects WHERE owncode = ?";
                            $stmt = $conn->prepare($query);
                            if (!$stmt) {
                                $error_message = "Error preparing downline query: " . $conn->error;
                                break;
                            }
                            $stmt->bind_param("s", $own_code);
                            $stmt->execute();
                            $downline_result = $stmt->get_result();
                            while ($row = $downline_result->fetch_assoc()) {
                                $downline_id = $row['id'];
                                $query = "SELECT * FROM thevani WHERE balakedara = ? ORDER BY dinankavannuracisi ASC";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("i", $downline_id);
                                $stmt->execute();
                                $deposit_result = $stmt->get_result();
                                $down_first = $down_second = $down_third = null;
                                $down_total = 0;
                                $down_today = 0;
                                while ($row = $deposit_result->fetch_assoc()) {
                                    $down_total += $row['motta'];
                                    $total_downline_deposit += $row['motta'];
                                    if (!$down_first) $down_first = $row['motta'];
                                    elseif (!$down_second) $down_second = $row['motta'];
                                    elseif (!$down_third) $down_third = $row['motta'];
                                    if (strpos($row['dinankavannuracisi'], $today) !== false) {
                                        $down_today += $row['motta'];
                                        $total_downline_deposit_today += $row['motta'];
                                    }
                                }
                                $stmt->close();
                                $query = "SELECT * FROM hintegedukolli WHERE balakedara = ?";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("i", $downline_id);
                                $stmt->execute();
                                $withdrawal_result = $stmt->get_result();
                                $down_withdrawals_today = 0;
                                $down_first_withdrawal = $down_second_withdrawal = $down_third_withdrawal = null;
                                while ($row = $withdrawal_result->fetch_assoc()) {
                                    $down_withdrawals_today += $row['motta'];
                                    $total_downline_withdrawal_today += $row['motta'];
                                    if (!$down_first_withdrawal) $down_first_withdrawal = $row['motta'];
                                    elseif (!$down_second_withdrawal) $down_second_withdrawal = $row['motta'];
                                    elseif (!$down_third_withdrawal) $down_third_withdrawal = $row['motta'];
                                }
                                $stmt->close();
                                $downline_data[] = [
                                    'position' => $position,
                                    'id' => $downline_id,
                                    'first_deposit' => $down_first ?? 'N/A',
                                    'second_deposit' => $down_second ?? 'N/A',
                                    'third_deposit' => $down_third ?? 'N/A',
                                    'total_deposit' => $down_total,
                                    'today_deposit' => $down_today,
                                    'first_withdrawal' => $down_first_withdrawal ?? 'N/A',
                                    'second_withdrawal' => $down_second_withdrawal ?? 'N/A',
                                    'third_withdrawal' => $down_third_withdrawal ?? 'N/A',
                                    'today_withdrawal' => $down_withdrawals_today
                                ];
                            }
                            $stmt->close();
                        }
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>User Downline Details | Sol-0203</title>
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/feather/feather.css">
    <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="vendors/jquery-bar-rating/fontawesome-stars-o.css">
    <link rel="stylesheet" href="vendors/jquery-bar-rating/fontawesome-stars.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="https://Sol-0203.com/favicon.ico">
    <style>
        /* ----------------------------------------- */
        /* --- ENHANCED BLUE AND WHITE THEME STYLES --- */
        /* ----------------------------------------- */
        /* General Reset & Typography */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1F2937;
        }
        /* Content Wrapper */
        .content-wrapper {
            background: linear-gradient(135deg, #F5F7FA 0%, #E0E7FF 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        /* Page Title */
        .page-title {
            color: #1E3A8A;
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            margin-bottom: 2rem;
        }
        .page-subtitle {
            color: #6B7280;
            font-size: 1rem;
            font-weight: 400;
            margin-bottom: 1.5rem;
        }
        /* Main Card Style */
        .stats-card, .data-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid rgba(219, 234, 254, 0.5);
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            color: #1F2937;
            transition: background-color 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .stats-card:hover, .data-card:hover {
            background: rgba(245, 250, 255, 0.95);
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
        }
        /* Card Column */
        .stats-container, .data-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        /* Icon Styling */
        .card-icon {
            font-size: 3.5rem;
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.2;
            color: #3B82F6;
            transition: opacity 0.4s ease;
        }
        .stats-card:hover .card-icon, .data-card:hover .card-icon {
            opacity: 0.3;
        }
        /* Card Text Styling */
        .stats-card h4, .data-card h4 {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6B7280;
            margin-bottom: 0.5rem;
        }
        .stats-value {
            font-size: 2.25rem;
            font-weight: 700;
            color: #1E3A8A;
            line-height: 1.2;
        }
        /* Form Styling */
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            font-size: 1rem;
            font-weight: 500;
            color: #1E3A8A;
            margin-bottom: 0.5rem;
            display: block;
        }
        .form-control {
            background: #F9FAFB;
            border: 1px solid #D1D5DB;
            color: #1F2937;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        .form-control:focus {
            background: #FFFFFF;
            border-color: #3B82F6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            outline: none;
        }
        .form-control::placeholder {
            color: #94a3b8;
        }
        .btn-primary {
            background: linear-gradient(90deg, #3B82F6 0%, #60A5FA 100%);
            border: none;
            color: #FFFFFF;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #1E3A8A 0%, #3B82F6 100%);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.3);
        }
        /* Table Styling */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table thead {
            background: #F9FAFB;
            border-bottom: 1px solid rgba(219, 234, 254, 0.5);
        }
        .data-table th {
            padding: 12px;
            text-align: left;
            color: #1E3A8A;
            font-weight: 600;
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .data-table td {
            padding: 12px;
            color: #1F2937;
            font-size: 0.95em;
            border-bottom: 1px solid #E5E7EB;
        }
        .data-table tbody tr:hover {
            background: #F5FAFF;
        }
        .no-data {
            text-align: center;
            padding: 2rem;
            color: #6B7280;
            font-size: 1rem;
        }
        /* Data Item Styling */
        .data-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #E5E7EB;
        }
        .data-item:last-child {
            border-bottom: none;
        }
        .data-item strong {
            color: #1E3A8A;
            font-weight: 600;
        }
        .data-item span {
            color: #1F2937;
        }
        /* Alert Styling */
        .alert-container {
            max-width: 800px;
            margin: 1.5rem auto;
        }
        .alert-error {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            border: 1px solid #f5c6cb;
            background: rgba(248, 215, 218, 0.95);
            color: #721c24;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-warning {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            border: 1px solid #FFEEBA;
            background: #FFF3CD;
            color: #856404;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .content-wrapper {
                padding: 1rem;
            }
            .page-title {
                font-size: 1.75rem;
            }
            .stats-card, .data-card {
                padding: 1rem;
            }
            .form-control {
                width: 100%;
            }
            .btn-primary {
                width: 100%;
                text-align: center;
            }
            .stats-container, .data-container {
                grid-template-columns: 1fr;
            }
            .data-table th, .data-table td {
                font-size: 0.8rem;
                padding: 8px;
            }
            .stats-value {
                font-size: 1.75rem;
            }
            .card-icon {
                font-size: 2.5rem;
            }
        }
        @media (max-width: 480px) {
            .page-title {
                font-size: 1.5rem;
            }
            .form-group label {
                font-size: 0.9rem;
            }
            .stats-value {
                font-size: 1.5rem;
            }
            .card-icon {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-title">DownLine</div>
                    <div class="page-subtitle">View user details and statistics</div>
                    <!-- Statistics Section -->
                    <div class="stats-container">
                        <div class="stats-card">
                            <h4>Today's Recharge</h4>
                            <div class="stats-value"><?php echo number_format($today_recharge, 2); ?></div>
                            <i class="mdi mdi-currency-usd card-icon"></i>
                        </div>
                        <div class="stats-card">
                            <h4>Referrer UID</h4>
                            <div class="stats-value"><?php echo $referrer_uid ?? 'N/A'; ?></div>
                            <i class="mdi mdi-account card-icon"></i>
                        </div>
                    </div>
                    <!-- Alerts -->
                    <div class="alert-container">
                        <?php if ($error_message): ?>
                            <div class="alert-error">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php elseif ($display_results && !$user_result): ?>
                            <div class="alert-warning">
                                No user found with the provided ID.
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Search Form -->
                    <div class="data-container">
                        <div class="data-card">
                            <h4>Search User</h4>
                            <form class="form" method="POST" id="userForm">
                                <div class="form-group">
                                    <label for="user_id">User ID</label>
                                    <input type="number"
                                           class="form-control"
                                           name="user_id"
                                           id="user_id"
                                           required
                                           placeholder="Enter User ID">
                                </div>
                                <button type="submit" name="submit" class="btn-primary">
                                    <i class="mdi mdi-magnify"></i> Check
                                </button>
                            </form>
                        </div>
                    </div>
                    <!-- User Details -->
                    <div class="data-container">
                        <div class="data-card">
                            <h4>User Details</h4>
                            <?php if ($display_results && $user_result): ?>
                                <div class="data-item">
                                    <strong>ID:</strong>
                                    <span><?php echo $user_result['id']; ?></span>
                                </div>
                                <div class="data-item">
                                    <strong>Own Code:</strong>
                                    <span><?php echo $user_result['owncode']; ?></span>
                                </div>
                            <?php else: ?>
                                <div class="no-data">Search user ID to get data</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Deposit and Withdrawal Summary -->
                    <div class="data-container">
                        <div class="data-card">
                            <h4>Deposit Summary</h4>
                            <?php if ($display_results && $user_result): ?>
                                <div class="data-item">
                                    <strong>First Deposit:</strong>
                                    <span><?php echo $first_deposit ?? 'N/A'; ?></span>
                                </div>
                                <div class="data-item">
                                    <strong>Second Deposit:</strong>
                                    <span><?php echo $second_deposit ?? 'N/A'; ?></span>
                                </div>
                                <div class="data-item">
                                    <strong>Third Deposit:</strong>
                                    <span><?php echo $third_deposit ?? 'N/A'; ?></span>
                                </div>
                                <div class="data-item">
                                    <strong>Total Deposits:</strong>
                                    <span><?php echo $total_deposit; ?></span>
                                </div>
                                <div class="data-item">
                                    <strong>Today's Deposits:</strong>
                                    <span><?php echo $today_deposit; ?></span>
                                </div>
                            <?php else: ?>
                                <div class="no-data">Search user ID to get data</div>
                            <?php endif; ?>
                        </div>
                        <div class="data-card">
                            <h4>Withdrawal Summary</h4>
                            <?php if ($display_results && $user_result): ?>
                                <div class="data-item">
                                    <strong>First Withdrawal:</strong>
                                    <span><?php echo $first_withdrawal ?? 'N/A'; ?></span>
                                </div>
                                <div class="data-item">
                                    <strong>Second Withdrawal:</strong>
                                    <span><?php echo $second_withdrawal ?? 'N/A'; ?></span>
                                </div>
                                <div class="data-item">
                                    <strong>Third Withdrawal:</strong>
                                    <span><?php echo $third_withdrawal ?? 'N/A'; ?></span>
                                </div>
                                <div class="data-item">
                                    <strong>Total Withdrawals Today:</strong>
                                    <span><?php echo $withdrawals_today; ?></span>
                                </div>
                            <?php else: ?>
                                <div class="no-data">Search user ID to get data</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Downline Summary and Details -->
                    <div class="data-container">
                        <div class="data-card">
                            <h4>Downline Summary</h4>
                            <?php if ($display_results && $user_result): ?>
                                <div class="data-item">
                                    <strong>Total Deposits:</strong>
                                    <span><?php echo $total_downline_deposit; ?></span>
                                </div>
                                <div class="data-item">
                                    <strong>Today's Deposits:</strong>
                                    <span><?php echo $total_downline_deposit_today; ?></span>
                                </div>
                                <div class="data-item">
                                    <strong>Total Withdrawals Today:</strong>
                                    <span><?php echo $total_downline_withdrawal_today; ?></span>
                                </div>
                            <?php else: ?>
                                <div class="no-data">Search user ID to get data</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="data-container">
                        <div class="data-card">
                            <h4>Downline Details</h4>
                            <?php if ($display_results && $user_result && !empty($downline_data)): ?>
                                <div class="table-container">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Position</th>
                                                <th>ID</th>
                                                <th>1st Deposit</th>
                                                <th>2nd Deposit</th>
                                                <th>3rd Deposit</th>
                                                <th>Total Deposits</th>
                                                <th>Today's Deposits</th>
                                                <th>1st Withdrawal</th>
                                                <th>2nd Withdrawal</th>
                                                <th>3rd Withdrawal</th>
                                                <th>Today's Withdrawals</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($downline_data as $downline): ?>
                                                <tr>
                                                    <td><?php echo $downline['position']; ?></td>
                                                    <td><?php echo $downline['id']; ?></td>
                                                    <td><?php echo $downline['first_deposit']; ?></td>
                                                    <td><?php echo $downline['second_deposit']; ?></td>
                                                    <td><?php echo $downline['third_deposit']; ?></td>
                                                    <td><?php echo $downline['total_deposit']; ?></td>
                                                    <td><?php echo $downline['today_deposit']; ?></td>
                                                    <td><?php echo $downline['first_withdrawal']; ?></td>
                                                    <td><?php echo $downline['second_withdrawal']; ?></td>
                                                    <td><?php echo $downline['third_withdrawal']; ?></td>
                                                    <td><?php echo $downline['today_withdrawal']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="no-data">Search user ID to get data</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Powered by Sol-0203.com Admin</span>
                    </div>
                </footer>
            </div>
        </div>
    </div>
    <script src="vendors/base/vendor.bundle.base.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/hoverable-collapse.js"></script>
    <script src="js/template.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userForm = document.getElementById('userForm');
            userForm.addEventListener('submit', function(e) {
                const userId = document.getElementById('user_id').value;
                if (userId <= 0) {
                    e.preventDefault();
                    alert('User ID must be a positive number.');
                }
            });
            const alerts = document.querySelectorAll('.alert-error, .alert-warning');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        });
    </script>
</body>
</html>