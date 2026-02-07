<?php
session_start();
if($_SESSION['unohs'] == null){
    header("location:index.php?msg=unauthorized");
}
date_default_timezone_set("Asia/Dhaka");
include "conn.php";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $level1 = $_POST['level1'];
    $level2 = $_POST['level2'];
    $level3 = $_POST['level3'];
    $level4 = $_POST['level4'];
    $level5 = $_POST['level5'];
    $level6 = $_POST['level6'];

    $updateQuery = "UPDATE web_commission SET 
        level1 = ?, 
        level2 = ?, 
        level3 = ?, 
        level4 = ?, 
        level5 = ?, 
        level6 = ? 
        WHERE id = 1";

    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "dddddd", $level1, $level2, $level3, $level4, $level5, $level6);
    
    if (mysqli_stmt_execute($stmt)) {
        $msg = "Commission levels updated successfully!";
    } else {
        $msg = "Error updating levels: " . mysqli_error($conn);
    }
}

// Fetch current settings
$query = "SELECT * FROM web_commission LIMIT 1";
$result = mysqli_query($conn, $query);
$settings = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commission Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root{
            --blue:#1e40af;--blue-light:#3b82f6;--gray:#f8fafc;--danger:#dc2626;--success:#16a34a;
        }
        *{box-sizing:border-box;margin:0;padding:0;}
        html,body{background:var(--gray);font-family:'Inter',sans-serif;overflow-x:hidden;}
        .main-content{padding:2rem;background:white;min-height:100vh;}
        .page-title{font-size:1.9rem;font-weight:900;color:var(--blue);margin-bottom:2.5rem;padding-bottom:1rem;border-bottom:6px solid var(--blue-light);text-align:center;}
        .card{border-radius:20px;overflow:hidden;box-shadow:0 12px 40px rgba(0,0,0,0.12);border:1px solid #e2e8f0;margin-bottom:2.5rem;}
        .card-header{background:linear-gradient(135deg,var(--blue),var(--blue-light));color:white;padding:1.2rem 1.8rem;font-weight:700;font-size:1.25rem;}
        .card-body{padding:2rem;}
        .form-control{
            border-radius:14px;padding:0.85rem 1.2rem;border:2px solid #cbd5e1;font-size:0.98rem;transition:border-color .3s;
        }
        .form-control:focus{
            border-color:var(--blue-light);box-shadow:0 0 0 .25rem rgba(59,130,246,.25);
        }
        .btn-custom{
            padding:0.85rem 2.2rem;border-radius:50px;font-weight:600;font-size:1rem;transition:all .3s;
        }
        .btn-save{background:linear-gradient(135deg,var(--success),#22c55e);color:white;}
        .btn-custom:hover{opacity:0.9;}
        .level-item{
            background:#f8f9ff;border:1px solid #e2e8f0;border-radius:16px;padding:1.2rem;
            margin-bottom:1rem;transition:all .3s;
        }
        .level-item:hover{background:#f0f4ff;}
        .level-label{
            font-weight:700;color:var(--blue);font-size:1.1rem;margin-bottom:0.5rem;
        }
        @media (max-width:768px){
            .main-content{padding:1rem;}
            .page-title{font-size:1.5rem;}
            .card-header{font-size:1.1rem;padding:1rem;}
            .card-body{padding:1.5rem;}
            .btn-custom{padding:0.75rem 1.6rem;font-size:0.92rem;}
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container-fluid">
    <div class="main-content">

        <h2 class="page-title">Commission Levels Management</h2>

        <?php if (isset($msg)): ?>
            <div class="alert alert-<?= strpos($msg,'successfully')!==false?'success':'danger' ?> alert-dismissible fade show">
                <strong><?= htmlspecialchars($msg) ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                Update Commission Levels
                <small class="text-light opacity-75 d-block">Set percentage for each referral level</small>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="level-item">
                                <div class="level-label">
                                    Level <?= $i ?> Commission (%)
                                </div>
                                <input type="number" step="0.01" name="level<?= $i ?>" class="form-control" 
                                       value="<?= $settings['level' . $i] ?>" required placeholder="e.g. 5.50">
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-custom btn-save">
                            Update All Levels
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Current Commission Structure
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                    <div class="col-lg  col-lg-4 col-md-6">
                        <div class="p-3 text-center bg-light rounded-3 border">
                            <h5 class="fw-bold text-primary mb-1">Level <?= $i ?></h5>
                            <h3 class="text-success fw-bold"><?= number_format($settings['level' . $i], 2) ?>%</h3>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>