<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("Location: index.php?msg=unauthorized");
    exit;
}
include("conn.php");
include("header.php");

// Fetch Current Config
$q = $conn->query("SELECT * FROM gamblly_config LIMIT 1");
$config = $q->fetch_assoc();

// Save Updated Values
$popup_msg = '';
if (isset($_POST['save'])) {
    $stmt = $conn->prepare("
        UPDATE gamblly_config SET
            API_AGENCY_UID=?,
            API_MEMBER_PREFIX=?,
            API_MEMBER_SUFFIX=?,
            API_CURRENCY=?,
            API_LANGUAGE=?,
            API_PLATFORM=?
        WHERE id=1
    ");
    $stmt->bind_param("ssssss",
        $_POST['API_AGENCY_UID'],
        $_POST['API_MEMBER_PREFIX'],
        $_POST['API_MEMBER_SUFFIX'],
        $_POST['API_CURRENCY'],
        $_POST['API_LANGUAGE'],
        $_POST['API_PLATFORM']
    );
    if ($stmt->execute()) {
        $popup_msg = "Configuration Saved Successfully!";
    } else {
        $popup_msg = "Error! Try Again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Config Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --success: #06d6a0;
            --radius: 16px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e9fd 100%);
            color: #2d3436;
            min-height: 100vh;
            padding: 20px 12px;
        }
        .main-container { max-width: 800px; margin: 0 auto; width: 100%; }

        .card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(14px);
            border-radius: var(--radius);
            padding: 36px 32px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.09);
            border: 1px solid rgba(219,234,254,0.6);
        }
        .card h3 {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 32px;
            color: #1e293b;
        }

        .form-group { margin-bottom: 22px; }
        .form-group label {
            display: block;
            margin-bottom: 9px;
            font-weight: 600;
            color: #374151;
            font-size: 15px;
        }
        .form-group input {
            width: 100%;
            padding: 15px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            background: #fff;
            transition: all 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 5px rgba(67,97,238,0.18);
        }

        /* Access Panel Button - Clean & Smooth */
        .access-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 32px;
            background: rgba(67, 97, 238, 0.13);
            color: var(--primary);
            font-weight: 700;
            font-size: 16.5px;
            border-radius: 14px;
            text-decoration: none;
            border: 2px solid rgba(67, 97, 238, 0.3);
            transition: all 0.4s ease;
            margin: 32px auto 20px;
            width: fit-content;
        }
        .access-btn i {
            font-size: 18px;
            transition: transform 0.4s ease;
        }
        .access-btn:hover {
            background: rgba(67, 97, 238, 0.22);
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(67, 97, 238, 0.25);
        }
        .access-btn:hover i {
            transform: translateX(4px);
        }

        /* Save Button */
        .btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 14px;
            font-size: 17px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(90deg, #3B82F6 0%, #60A5FA 100%);
            cursor: pointer;
            transition: all 0.4s ease;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(59,130,246,0.4);
        }

        /* Compact Premium Popup - Ab chhota aur perfect */
        #popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.85);
            background: rgba(0,0,0,0.95);
            color: white;
            padding: 18px 36px;
            border-radius: 18px;
            font-size: 17px;
            font-weight: 600;
            z-index: 99999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.15);
            min-width: 280px;
            text-align: center;
            letter-spacing: 0.3px;
        }
        #popup.show {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
            visibility: visible;
        }

        /* Mobile Perfection */
        @media (max-width: 768px) {
            .card { padding: 28px 24px; }
            .card h3 { font-size: 22px; }
            .access-btn { padding: 14px 26px; font-size: 16px; }
        }
        @media (max-width: 480px) {
            body { padding: 15px 10px; }
            .card { padding: 24px 20px; }
            #popup {
                padding: 16px 30px;
                font-size: 16px;
                border-radius: 16px;
                min-width: 260px;
            }
            .access-btn {
                width: 100%;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<div class="main-container">
    <div id="popup"><?php echo htmlspecialchars($popup_msg); ?></div>

    <div class="card">
        <h3>Manage API Configuration</h3>

        <form method="POST">
            <div class="form-group">
                <label>Agency UID</label>
                <input type="text" name="API_AGENCY_UID" value="<?php echo htmlspecialchars($config['API_AGENCY_UID']); ?>" required>
            </div>
            <div class="form-group">
                <label>Member Prefix</label>
                <input type="text" name="API_MEMBER_PREFIX" value="<?php echo htmlspecialchars($config['API_MEMBER_PREFIX']); ?>">
            </div>
            <div class="form-group">
                <label>Member Suffix</label>
                <input type="text" name="API_MEMBER_SUFFIX" value="<?php echo htmlspecialchars($config['API_MEMBER_SUFFIX']); ?>">
            </div>
            <div class="form-group">
                <label>Currency</label>
                <input type="text" name="API_CURRENCY" value="<?php echo htmlspecialchars($config['API_CURRENCY']); ?>" required>
            </div>
            <div class="form-group">
                <label>Language</label>
                <input type="text" name="API_LANGUAGE" value="<?php echo htmlspecialchars($config['API_LANGUAGE']); ?>" required>
            </div>
            <div class="form-group">
                <label>Platform</label>
                <input type="text" name="API_PLATFORM" value="<?php echo htmlspecialchars($config['API_PLATFORM']); ?>" required>
            </div>

            <!-- Clean Access Panel Button (No right arrow) -->
            <a href="https://portal.gamblly.com/" target="_blank" class="access-btn">
                <i class="fas fa-arrow-up-right-from-square"></i>
                Access Panel
            </a>

            <button type="submit" name="save" class="btn">Save Configuration</button>
        </form>
    </div>
</div>

<?php if($popup_msg): ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const popup = document.getElementById('popup');
        popup.classList.add('show');
        setTimeout(() => popup.classList.remove('show'), 2600);
    });
</script>
<?php endif; ?>

</body>
</html>