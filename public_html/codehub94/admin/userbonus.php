<?php
session_start();
if (empty($_SESSION['unohs'])) {
    header("Location: index.php?msg=unauthorized");
    exit();
}
include("conn.php");
$bonusTypes = [
    3   => "Red envelope",
    8   => "Agent red envelope recharge",
    10  => "Recharge gift",
    13  => "Bonus recharge",
    14  => "First full gift",
    20  => "Invite bonus",
    25  => "Card binding gift",
    107 => "Weekly Awards",
    124 => "Agent Bonus",
    118 => "Daily Awards",
    117 => "New members get bonuses by playing games",
    115 => "Return Awards",
];

// Function to add bonus
function addBonus($userId, $type, $amount, $remark, $conn, $bonusTypes) {
    $tableNames = [
        3   => "hodike_balakedara",
        8   => "agent_red_envelope_recharge_table",
        10  => "recharge_gift_table",
        13  => "bonus_recharge_table",
        14  => "first_full_gift_table",
        20  => "invite_bonus_table",
        25  => "card_binding_gift_table",
        107 => "weekly_awards_table",
        124 => "agent_bonus_table",
        118 => "daily_awards_table",
        117 => "new_members_bonus_table",
        115 => "return_awards_table",
    ];

    if (!isset($tableNames[$type])) {
        return "Invalid bonus type.";
    }

    $tableName = $tableNames[$type];
    date_default_timezone_set('Asia/Kolkata');
    $date = date("Y-m-d H:i:s");
    $serial = "Imitator";

    $stmt = $conn->prepare("INSERT INTO $tableName (userkani, price, serial, shonu, remark) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        return "Error preparing statement: " . $conn->error;
    }

    if (!$stmt->bind_param("idsss", $userId, $amount, $serial, $date, $remark)) {
        return "Error binding parameters: " . $stmt->error;
    }

    if ($stmt->execute()) {
        $stmt->close();
        $updateStmt = $conn->prepare("UPDATE shonu_kaichila SET motta = ROUND(motta + ?, 2) WHERE balakedara = ?");
        if (!$updateStmt) {
            return "Error preparing update statement: " . $conn->error;
        }

        if (!$updateStmt->bind_param("di", $amount, $userId)) {
            return "Error binding parameters for update: " . $updateStmt->error;
        }

        if ($updateStmt->execute()) {
            $updateStmt->close();
            return "Bonus added and balance updated successfully!";
        } else {
            $updateStmt->close();
            return "Error updating balance: " . $updateStmt->error;
        }
    } else {
        $stmt->close();
        return "Error executing statement: " . $stmt->error;
    }
}

// Input handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id']);
    $type = intval($_POST['type']);
    $amount = floatval($_POST['amount']);
    $remark = htmlspecialchars($_POST['remark'] ?? '');

    if ($userId > 0 && $type > 0 && $amount > 0) {
        $result = addBonus($userId, $type, $amount, $remark, $conn, $bonusTypes);
        $_SESSION['msg'] = $result;
    } else {
        $_SESSION['error'] = "Please provide valid inputs for all required fields: user_id, type, and amount.";
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>User Bonus Management | Sol-0203</title>
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
  margin-bottom: 0.5rem;
}
.page-subtitle {
  color: #6B7280;
  font-size: 1.125rem;
  font-weight: 400;
  margin-bottom: 2rem;
}

/* Alert Container */
.alert-container {
  max-width: 700px;
  margin: 1.5rem auto;
}
.alert {
  padding: 1rem;
  margin-bottom: 1.5rem;
  border-radius: 8px;
  border: 1px solid transparent;
  font-size: 0.875rem;
  font-weight: 500;
  transition: opacity 0.3s ease;
}
.alert-success {
  color: #155724;
  background: rgba(212, 237, 218, 0.95);
  border-color: #c3e6cb;
  backdrop-filter: blur(10px);
}
.alert-danger {
  color: #721c24;
  background: rgba(248, 215, 218, 0.95);
  border-color: #f5c6cb;
  backdrop-filter: blur(10px);
}

/* Settings Form Styling */
.settings-form {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(219, 234, 254, 0.5);
  border-radius: 16px;
  box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
  padding: 2rem;
  margin: 1.5rem 0.5rem;
}
.settings-form h4 {
  color: #1E3A8A;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}
.settings-form label {
  font-weight: 600;
  color: #4B5563;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  display: block;
}
.settings-form .form-control {
  background: #F9FAFB;
  border: 1px solid #D1D5DB;
  color: #1F2937;
  border-radius: 8px;
  padding: 0.75rem;
  font-size: 1rem;
  transition: all 0.3s ease;
}
.settings-form .form-control:focus {
  background: #FFFFFF;
  border-color: #3B82F6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
  outline: none;
}
.settings-form textarea.form-control {
  resize: vertical;
  min-height: 80px;
}
.settings-form .btn-primary {
  background: linear-gradient(90deg, #3B82F6 0%, #60A5FA 100%);
  border: none;
  color: #FFFFFF;
  font-weight: 600;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  transition: all 0.3s ease;
}
.settings-form .btn-primary:hover {
  background: linear-gradient(90deg, #1E3A8A 0%, #3B82F6 100%);
  transform: translateY(-3px);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.3);
}
.settings-form .btn-primary:focus {
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.25);
  outline: none;
}
.settings-form .btn-primary i {
  margin-right: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .content-wrapper {
    padding: 1rem;
  }
  .page-title {
    font-size: 1.75rem;
  }
  .page-subtitle {
    font-size: 1rem;
  }
  .settings-form {
    padding: 1.5rem;
  }
  .alert-container {
    margin: 1rem;
  }
}

@media (max-width: 480px) {
  .page-title {
    font-size: 1.5rem;
  }
  .page-subtitle {
    font-size: 0.875rem;
  }
  .settings-form {
    padding: 1rem;
  }
  .settings-form .btn-primary {
    width: 100%;
    text-align: center;
  }
}
</style>
</head>
<body>
<?php include 'header.php';?>
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="row">
                        
                    </div>
                    <div class="alert-container">
                        <?php if(isset($_SESSION['msg'])): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($_SESSION['msg']) ?>
                                <?php unset($_SESSION['msg']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($_SESSION['error']) ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="settings-form">
                        <h4 class="font-weight-bold mb-4">Assign User Bonus</h4>
                        <form class="bonus-form" method="POST" id="bonusForm">
                            <div class="form-group">
                                <label for="user_id">User ID</label>
                                <input type="number" 
                                       class="form-control" 
                                       name="user_id" 
                                       id="user_id" 
                                       required
                                       placeholder="Enter User ID">
                            </div>
                            <div class="form-group">
                                <label for="type">Bonus Type</label>
                                <select class="form-control" name="type" id="type" required>
                                    <?php foreach ($bonusTypes as $typeId => $typeName): ?>
                                        <option value="<?= $typeId ?>"><?= htmlspecialchars($typeName) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="amount">Bonus Amount</label>
                                <input type="number" 
                                       step="0.01" 
                                       class="form-control" 
                                       name="amount" 
                                       id="amount" 
                                       required
                                       placeholder="Enter Amount">
                            </div>
                            <div class="form-group">
                                <label for="remark">Remark (Optional)</label>
                                <textarea class="form-control" 
                                          name="remark" 
                                          id="remark" 
                                          rows="3"
                                          placeholder="Add any remarks..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-gift-outline mr-2"></i>
                                Assign Bonus
                            </button>
                        </form>
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
            const bonusForm = document.getElementById('bonusForm');
            bonusForm.addEventListener('submit', function(e) {
                const userId = document.getElementById('user_id').value;
                const amount = document.getElementById('amount').value;
                if (userId <= 0 || amount <= 0) {
                    e.preventDefault();
                    alert('User ID and Amount must be positive numbers.');
                }
            });
            const alerts = document.querySelectorAll('.alert');
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