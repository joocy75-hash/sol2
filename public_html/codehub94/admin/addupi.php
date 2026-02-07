<?php

session_start();

if (!isset($_SESSION['unohs'])) {
    header("Location: index.php?msg=unauthorized");
    exit;
}

include("conn.php");
include("header.php");

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// FOLDERS
$upload_dir = "uploads/";
$method1_dir = $upload_dir . "Method_1/";
$method2_dir = $upload_dir . "Method_2/";
$rocket_dir = $upload_dir . "Rocket/";
$upay_dir = $upload_dir . "Upay/";
$usdt_dir = $upload_dir . "USDT/";

!is_dir($method1_dir) && mkdir($method1_dir, 0755, true);
!is_dir($method2_dir) && mkdir($method2_dir, 0755, true);
!is_dir($rocket_dir) && mkdir($rocket_dir, 0755, true);
!is_dir($upay_dir) && mkdir($upay_dir, 0755, true);
!is_dir($usdt_dir) && mkdir($usdt_dir, 0755, true);

$popup_msg = $_SESSION['popup_msg'] ?? '';
unset($_SESSION['popup_msg']);

// AJAX HANDLER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['success' => false]); exit;
    }
    $msg = '';
    $done = false;
    
    // ADD
    if (!empty($_POST['new_method1'])) {
        $num = trim($_POST['new_method1']);
        $stmt = $conn->prepare("INSERT IGNORE INTO deyya (maulya, sthiti) VALUES (?, '0')");
        $stmt->bind_param("s", $num);
        $stmt->execute();
        $msg = 'Added';
        $done = true;
    }
    
    if (!empty($_POST['new_method2'])) {
        $num = trim($_POST['new_method2']);
        $stmt = $conn->prepare("INSERT IGNORE INTO nagad_pay (upi_id, status) VALUES (?, '0')");
        $stmt->bind_param("s", $num);
        $stmt->execute();
        $msg = 'Added';
        $done = true;
    }
    
    if (!empty($_POST['new_rocket'])) {
        $num = trim($_POST['new_rocket']);
        $stmt = $conn->prepare("INSERT IGNORE INTO rocket_payment (number, status) VALUES (?, '0')");
        $stmt->bind_param("s", $num);
        $stmt->execute();
        $msg = 'Rocket Added';
        $done = true;
    }
    
    if (!empty($_POST['new_upay'])) {
        $num = trim($_POST['new_upay']);
        $stmt = $conn->prepare("INSERT IGNORE INTO upay_payment (number, status) VALUES (?, '0')");
        $stmt->bind_param("s", $num);
        $stmt->execute();
        $msg = 'Upay Added';
        $done = true;
    }
    
    if (!empty($_POST['new_usdt'])) {
        $addr = trim($_POST['new_usdt']);
        $stmt = $conn->prepare("INSERT IGNORE INTO deyyamrici (maulya, sthiti) VALUES (?, '0')");
        $stmt->bind_param("s", $addr);
        $stmt->execute();
        $msg = 'USDT Added';
        $done = true;
    }
    
    // DELETE
    if (isset($_POST['delete_id'], $_POST['type'])) {
        $id = (int)$_POST['delete_id'];
        $type = $_POST['type'];
        
        if ($type === 'm1_num') $conn->query("DELETE FROM deyya WHERE shonu = $id");
        if ($type === 'm1_qr') { $r = $conn->query("SELECT filename FROM images WHERE id = $id")->fetch_assoc(); if ($r) @unlink("uploads/Method_1/" . basename($r['filename'])); $conn->query("DELETE FROM images WHERE id = $id"); }
        if ($type === 'm2_num') $conn->query("DELETE FROM nagad_pay WHERE id = $id AND image_path IS NULL");
        if ($type === 'm2_qr') { $r = $conn->query("SELECT image_path FROM nagad_pay WHERE id = $id")->fetch_assoc(); if ($r) @unlink("uploads/Method_2/" . basename($r['image_path'])); $conn->query("DELETE FROM nagad_pay WHERE id = $id"); }
        if ($type === 'rocket_num') $conn->query("DELETE FROM rocket_payment WHERE id = $id");
        if ($type === 'rocket_qr') { $r = $conn->query("SELECT image_url FROM rocket_payment WHERE id = $id")->fetch_assoc(); if ($r && $r['image_url']) @unlink("uploads/Rocket/" . basename($r['image_url'])); $conn->query("DELETE FROM rocket_payment WHERE id = $id"); }
        if ($type === 'upay_num') $conn->query("DELETE FROM upay_payment WHERE id = $id");
        if ($type === 'upay_qr') { $r = $conn->query("SELECT image_url FROM upay_payment WHERE id = $id")->fetch_assoc(); if ($r && $r['image_url']) @unlink("uploads/Upay/" . basename($r['image_url'])); $conn->query("DELETE FROM upay_payment WHERE id = $id"); }
        if ($type === 'usdt_id') { $check = $conn->query("SELECT sthiti FROM deyyamrici WHERE shonu = $id")->fetch_assoc(); if ($check && $check['sthiti'] == '1') { $msg = 'Cannot delete active'; } else { $conn->query("DELETE FROM deyyamrici WHERE shonu = $id"); $msg = 'Deleted'; $done = true; } }
        if ($type === 'usdt_qr') { $r = $conn->query("SELECT filename FROM images_usdt WHERE id = $id")->fetch_assoc(); if ($r) @unlink("uploads/USDT/" . $r['filename']); $conn->query("DELETE FROM images_usdt WHERE id = $id"); $msg = 'QR Deleted'; $done = true; }
        
        $done = true;
    }
    
    // ACTIVATE
    if (isset($_POST['activate_id'], $_POST['type'])) {
        $id = $_POST['activate_id'];
        $type = $_POST['type'];
        
        if ($type === 'm1_num') { $conn->query("UPDATE deyya SET sthiti = '0'"); $conn->query("UPDATE deyya SET sthiti = '1' WHERE shonu = $id"); }
        if ($type === 'm1_qr') { $conn->query("UPDATE images SET status = '0'"); $conn->query("UPDATE images SET status = '1' WHERE id = $id"); }
        if ($type === 'm2_num') { $conn->query("UPDATE nagad_pay SET status = '0'"); $conn->query("UPDATE nagad_pay SET status = '1' WHERE id = $id"); }
        if ($type === 'm2_qr') { $conn->query("UPDATE nagad_pay SET status = '0'"); $conn->query("UPDATE nagad_pay SET status = '1' WHERE id = $id"); }
        if ($type === 'rocket_num') { $conn->query("UPDATE rocket_payment SET status = '0'"); $conn->query("UPDATE rocket_payment SET status = '1' WHERE id = $id"); }
        if ($type === 'rocket_qr') { $conn->query("UPDATE rocket_payment SET status = '0'"); $conn->query("UPDATE rocket_payment SET status = '1' WHERE id = $id"); }
        if ($type === 'upay_num') { $conn->query("UPDATE upay_payment SET status = '0'"); $conn->query("UPDATE upay_payment SET status = '1' WHERE id = $id"); }
        if ($type === 'upay_qr') { $conn->query("UPDATE upay_payment SET status = '0'"); $conn->query("UPDATE upay_payment SET status = '1' WHERE id = $id"); }
        if ($type === 'usdt_id') { $conn->query("UPDATE deyyamrici SET sthiti = '0'"); $conn->query("UPDATE deyyamrici SET sthiti = '1' WHERE maulya = '$id'"); }
        if ($type === 'usdt_qr') { $conn->query("UPDATE images_usdt SET status = '0'"); $conn->query("UPDATE images_usdt SET status = '1' WHERE filename = '$id'"); }
        
        $msg = 'Activated';
        $done = true;
    }
    
    // UPLOAD
    if (isset($_FILES['qr_method1']) && $_FILES['qr_method1']['error'] == 0) {
        $file = $_FILES['qr_method1'];
        $name = "m1_" . time() . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
        $path = "Method_1/$name";
        if (move_uploaded_file($file['tmp_name'], $method1_dir . $name)) {
            $stmt = $conn->prepare("INSERT INTO images (filename, status) VALUES (?, '0')");
            $stmt->bind_param("s", $path);
            $stmt->execute();
            $msg = 'QR Uploaded';
            $done = true;
        }
    }
    
    if (isset($_FILES['qr_method2']) && $_FILES['qr_method2']['error'] == 0) {
        $file = $_FILES['qr_method2'];
        $name = "m2_" . time() . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
        $path = "Method_2/$name";
        if (move_uploaded_file($file['tmp_name'], $method2_dir . $name)) {
            $stmt = $conn->prepare("INSERT INTO nagad_pay (image_path, status) VALUES (?, '0')");
            $stmt->bind_param("s", $path);
            $stmt->execute();
            $msg = 'QR Uploaded';
            $done = true;
        }
    }
    
    if (isset($_FILES['rocket_qr']) && $_FILES['rocket_qr']['error'] == 0) {
        $file = $_FILES['rocket_qr'];
        $name = "rocket_" . time() . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
        $path = "Rocket/$name";
        if (move_uploaded_file($file['tmp_name'], $rocket_dir . $name)) {
            $stmt = $conn->prepare("INSERT INTO rocket_payment (number, image_url, status) VALUES (?, ?, '0')");
            $num = "QR_" . time();
            $stmt->bind_param("ss", $num, $path);
            $stmt->execute();
            $msg = 'Rocket QR Uploaded';
            $done = true;
        }
    }
    
    if (isset($_FILES['upay_qr']) && $_FILES['upay_qr']['error'] == 0) {
        $file = $_FILES['upay_qr'];
        $name = "upay_" . time() . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
        $path = "Upay/$name";
        if (move_uploaded_file($file['tmp_name'], $upay_dir . $name)) {
            $stmt = $conn->prepare("INSERT INTO upay_payment (number, image_url, status) VALUES (?, ?, '0')");
            $num = "QR_" . time();
            $stmt->bind_param("ss", $num, $path);
            $stmt->execute();
            $msg = 'Upay QR Uploaded';
            $done = true;
        }
    }
    
    if (isset($_FILES['usdt_qr']) && $_FILES['usdt_qr']['error'] == 0) {
        $file = $_FILES['usdt_qr'];
        $name = "usdt_" . time() . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
        $path = "USDT/$name";
        if (move_uploaded_file($file['tmp_name'], $usdt_dir . $name)) {
            $stmt = $conn->prepare("INSERT INTO images_usdt (filename, status) VALUES (?, '0')");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $msg = 'USDT QR Uploaded';
            $done = true;
        }
    }
    
    if ($done) {
        $_SESSION['popup_msg'] = $msg;
    }
    echo json_encode(['success' => true, 'refresh' => true]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --success: #06d6a0;
            --danger: #ef476f;
            --dark: #2d3436;
            --light: #f8f9fa;
            --gray: #e9ecef;
            --radius: 16px;
            --container-width: 1200px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e9fd 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px 12px;
        }
        
        /* MAIN CONTAINER - SAME WIDTH AS HEADER */
        .main-container {
            max-width: var(--container-width);
            margin: 0 auto;
            width: 100%;
        }
        /* BLACK POPUP */
        #popup {
            position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) scale(0.8);
            background: rgba(0,0,0,0.95); color: white; padding: 22px 48px; border-radius: 20px;
            font-size: 18px; font-weight: 600; z-index: 99999; opacity: 0;
            transition: all 0.45s ease; box-shadow: 0 20px 60px rgba(0,0,0,0.6);
            backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.15);
        }
        #popup.show { transform: translate(-50%, -50%) scale(1); opacity: 1; }
        /* TABS - FULL WIDTH */
        .tab-container {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--radius);
            padding: 12px;
            margin-bottom: 20px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(219,234,254,0.5);
            width: 100%;
        }
        .tabs {
            display: flex; overflow-x: auto; gap: 10px; scrollbar-width: none;
        }
        .tabs::-webkit-scrollbar { display: none; }
        .tab {
            flex: 1; min-width: 120px; padding: 14px 16px; text-align: center;
            border-radius: 12px; font-weight: 700; font-size: 15px; cursor: pointer;
            background: #f1f3f5; color: #555; white-space: nowrap;
            text-decoration: none !important; transition: all 0.3s ease;
        }
        .tab.active { background: var(--primary); color: #fff; }
        /* CONTENT - SMOOTH SLIDE - FULL WIDTH */
        .content-wrapper { 
            position: relative; 
            overflow: hidden; 
            min-height: 500px; 
            width: 100%;
        }
        .tab-content {
            position: absolute; top: 0; left: 0; width: 100%; opacity: 0;
            transform: translateX(30px); transition: all 0.4s ease;
            pointer-events: none;
        }
        .tab-content.active {
            position: relative; opacity: 1; transform: translateX(0);
            pointer-events: auto;
        }
        /* CARDS - FULL WIDTH */
        .card {
            background: rgba(255,255,255,0.95); 
            backdrop-filter: blur(10px);
            border-radius: var(--radius); 
            padding: 24px; 
            margin-bottom: 24px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            border: 1px solid rgba(219,234,254,0.5);
            width: 100%;
        }
        .card h3 { margin: 0 0 18px; font-size: 18px; font-weight: 700; color: var(--dark); }
        /* SECTIONS - FULL WIDTH */
        .section { 
            background: #f8fafc; 
            border-radius: 14px; 
            padding: 20px; 
            width: 100%;
        }
        /* INPUT GROUPS - FULL WIDTH */
        .input-group {
            display: flex; 
            gap: 10px; 
            margin-bottom: 18px; 
            align-items: center;
            width: 100%;
        }
        .input-group input[type="text"], .input-group input[type="file"] {
            flex: 1; 
            padding: 12px; 
            border: 1.8px solid #dee2e6; 
            border-radius: 10px;
            font-size: 14.5px; 
            background: #fff;
            width: 100%;
        }
        .input-group input:focus { border-color: var(--primary); outline: none; }
        .btn {
            padding: 12px 28px; 
            border: none; 
            border-radius: 12px; 
            cursor: pointer;
            font-size: 15px; 
            font-weight: 700; 
            color: #fff;
            background: linear-gradient(90deg, #3B82F6 0%, #60A5FA 100%);
            white-space: nowrap;
        }
        /* ITEMS - FULL WIDTH */
        .item {
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            background: #ffffff; 
            padding: 14px 16px; 
            border-radius: 12px;
            margin: 8px 0; 
            border: 2px solid transparent;
            box-shadow: 0 3px 10px rgba(0,0,0,0.06);
            width: 100%;
        }
        .item.active { border-color: var(--success); background: #d1f2eb; }
        .item-left { 
            display: flex; 
            align-items: center; 
            flex: 1; 
            gap: 12px; 
            width: 100%;
            overflow: hidden;
        }
        .item-text { 
            font-weight: 600; 
            font-size: 14.5px; 
            color: #1e293b; 
            flex: 1;
            word-break: break-word;
        }
        .item img { 
            width: 50px; 
            height: 50px; 
            object-fit: cover; 
            border-radius: 8px; 
            border: 2px solid white; 
            flex-shrink: 0;
        }
        .item-actions { 
            display: flex; 
            gap: 8px; 
            flex-shrink: 0;
        }
        .icon-btn {
            width: 36px; 
            height: 36px; 
            border-radius: 10px; 
            display: flex;
            align-items: center; 
            justify-content: center; 
            font-size: 16px; 
            color: white;
            cursor: pointer; 
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            flex-shrink: 0;
        }
        .icon-activate { background: #06d6a0; }
        .icon-delete { background: #ef476f; }
        .icon-activated { background: #6b7280; cursor: default; }
        /* RESPONSIVE DESIGN */
        @media (max-width: 1200px) {
            .main-container {
                padding: 0 15px;
            }
        }
        
        @media (max-width: 768px) {
            .main-container { padding: 0 10px; }
            .tab-container { padding: 10px; }
            .tab { min-width: 100px; padding: 10px 12px; font-size: 13.5px; }
            .card { padding: 18px; }
            .input-group { flex-direction: column; }
            .btn { width: 100%; padding: 14px; }
            .item { 
                padding: 12px; 
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .item-left { width: 100%; }
            .item-text { font-size: 13.5px; }
            .item-actions { 
                width: 100%; 
                justify-content: flex-end;
            }
            .icon-btn { width: 34px; height: 34px; font-size: 15px; }
        }
        
        @media (max-width: 480px) {
            .main-container { padding: 0 8px; }
            .card { padding: 15px; }
            .section { padding: 15px; }
            .input-group { margin-bottom: 15px; }
            .item { padding: 10px; }
        }
    </style>
</head>
<body>
<div class="main-container">
    <div id="popup"><?php echo htmlspecialchars($popup_msg); ?></div>
    <div class="tab-container">
        <div class="tabs">
            <div class="tab <?php echo (!isset($_GET['tab']) || $_GET['tab']=='method1') ? 'active' : '' ?>" data-tab="method1">bKash</div>
            <div class="tab <?php echo $_GET['tab']=='method2' ? 'active' : '' ?>" data-tab="method2">Nagad</div>
            <div class="tab <?php echo $_GET['tab']=='rocket' ? 'active' : '' ?>" data-tab="rocket">Rocket</div>
            <div class="tab <?php echo $_GET['tab']=='upay' ? 'active' : '' ?>" data-tab="upay">Upay</div>
            <div class="tab <?php echo $_GET['tab']=='usdt' ? 'active' : '' ?>" data-tab="usdt">USDT</div>
        </div>
    </div>
    <div class="content-wrapper">
        <!-- bKash -->
        <div id="method1" class="tab-content <?php echo (!isset($_GET['tab']) || $_GET['tab']=='method1') ? 'active' : '' ?>">
            <div class="card">
                <h3>bKash - Numbers</h3>
                <div class="section">
                    <div class="input-group">
                        <input type="text" id="m1_num" placeholder="Enter number / UPI">
                        <button class="btn" onclick="add('m1_num','new_method1')">Add</button>
                    </div>
                    <div>
                        <?php $res = $conn->query("SELECT * FROM deyya ORDER BY sthiti DESC"); ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <div class="item <?php echo $row['sthiti']=='1'?'active':''; ?>">
                            <div class="item-left">
                                <div class="item-text"><?php echo htmlspecialchars($row['maulya']); ?></div>
                            </div>
                            <div class="item-actions">
                                <?php if($row['sthiti'] != '1'): ?>
                                <div class="icon-btn icon-activate" onclick="activate(<?php echo $row['shonu']; ?>, 'm1_num')">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php else: ?>
                                <div class="icon-btn icon-activated">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php endif; ?>
                                <div class="icon-btn icon-delete" onclick="del(<?php echo $row['shonu']; ?>, 'm1_num')">
                                    <i class="fas fa-trash"></i>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <div class="card">
                <h3>bKash - QR Codes</h3>
                <div class="section">
                    <form id="qr1_form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="ajax" value="1">
                        <div class="input-group">
                            <input type="file" name="qr_method1" accept="image/*" required>
                            <button type="submit" class="btn">Upload QR</button>
                        </div>
                    </form>
                    <div>
                        <?php $res = $conn->query("SELECT * FROM images ORDER BY status DESC"); ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <div class="item <?php echo $row['status']=='1'?'active':''; ?>">
                            <div class="item-left">
                                <img src="uploads/<?php echo $row['filename']; ?>" alt="QR">
                                <div class="item-text"><?php echo basename($row['filename']); ?></div>
                            </div>
                            <div class="item-actions">
                                <?php if($row['status'] != '1'): ?>
                                <div class="icon-btn icon-activate" onclick="activate(<?php echo $row['id']; ?>, 'm1_qr')">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php else: ?>
                                <div class="icon-btn icon-activated">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php endif; ?>
                                <div class="icon-btn icon-delete" onclick="del(<?php echo $row['id']; ?>, 'm1_qr')">
                                    <i class="fas fa-trash"></i>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nagad -->
        <div id="method2" class="tab-content <?php echo $_GET['tab']=='method2' ? 'active' : '' ?>">
            <div class="card">
                <h3>Nagad - Numbers</h3>
                <div class="section">
                    <div class="input-group">
                        <input type="text" id="m2_num" placeholder="Enter number / UPI">
                        <button class="btn" onclick="add('m2_num','new_method2')">Add</button>
                    </div>
                    <div>
                        <?php $res = $conn->query("SELECT * FROM nagad_pay WHERE image_path IS NULL ORDER BY status DESC"); ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <div class="item <?php echo $row['status']=='1'?'active':''; ?>">
                            <div class="item-left">
                                <div class="item-text"><?php echo htmlspecialchars($row['upi_id']); ?></div>
                            </div>
                            <div class="item-actions">
                                <?php if($row['status'] != '1'): ?>
                                <div class="icon-btn icon-activate" onclick="activate(<?php echo $row['id']; ?>, 'm2_num')">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php else: ?>
                                <div class="icon-btn icon-activated">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php endif; ?>
                                <div class="icon-btn icon-delete" onclick="del(<?php echo $row['id']; ?>, 'm2_num')">
                                    <i class="fas fa-trash"></i>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <div class="card">
                <h3>Nagad - QR Codes</h3>
                <div class="section">
                    <form id="qr2_form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="ajax" value="1">
                        <div class="input-group">
                            <input type="file" name="qr_method2" accept="image/*" required>
                            <button type="submit" class="btn">Upload QR</button>
                        </div>
                    </form>
                    <div>
                        <?php $res = $conn->query("SELECT * FROM nagad_pay WHERE image_path IS NOT NULL ORDER BY status DESC"); ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <div class="item <?php echo $row['status']=='1'?'active':''; ?>">
                            <div class="item-left">
                                <img src="uploads/<?php echo $row['image_path']; ?>" alt="QR">
                                <div class="item-text"><?php echo basename($row['image_path']); ?></div>
                            </div>
                            <div class="item-actions">
                                <?php if($row['status'] != '1'): ?>
                                <div class="icon-btn icon-activate" onclick="activate(<?php echo $row['id']; ?>, 'm2_qr')">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php else: ?>
                                <div class="icon-btn icon-activated">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php endif; ?>
                                <div class="icon-btn icon-delete" onclick="del(<?php echo $row['id']; ?>, 'm2_qr')">
                                    <i class="fas fa-trash"></i>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROCKET -->
        <div id="rocket" class="tab-content <?php echo $_GET['tab']=='rocket' ? 'active' : '' ?>">
            <div class="card">
                <h3>Rocket - Numbers</h3>
                <div class="section">
                    <div class="input-group">
                        <input type="text" id="rocket_num" placeholder="Enter Rocket number">
                        <button class="btn" onclick="addRocket()">Add</button>
                    </div>
                    <div>
                        <?php $res = $conn->query("SELECT * FROM rocket_payment WHERE image_url IS NULL ORDER BY status DESC"); ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <div class="item <?php echo $row['status']=='active'?'active':''; ?>">
                            <div class="item-left">
                                <div class="item-text"><?php echo htmlspecialchars($row['number']); ?></div>
                            </div>
                            <div class="item-actions">
                                <?php if($row['status'] != 'active'): ?>
                                <div class="icon-btn icon-activate" onclick="activate(<?php echo $row['id']; ?>, 'rocket_num')">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php else: ?>
                                <div class="icon-btn icon-activated">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php endif; ?>
                                <div class="icon-btn icon-delete" onclick="del(<?php echo $row['id']; ?>, 'rocket_num')">
                                    <i class="fas fa-trash"></i>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <div class="card">
                <h3>Rocket - QR Codes</h3>
                <div class="section">
                    <form id="rocket_qr_form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="ajax" value="1">
                        <div class="input-group">
                            <input type="file" name="rocket_qr" accept="image/*" required>
                            <button type="submit" class="btn">Upload QR</button>
                        </div>
                    </form>
                    <div>
                        <?php $res = $conn->query("SELECT * FROM rocket_payment WHERE image_url IS NOT NULL ORDER BY status DESC"); ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <div class="item <?php echo $row['status']=='active'?'active':''; ?>">
                            <div class="item-left">
                                <?php if($row['image_url']): ?>
                                <img src="uploads/<?php echo $row['image_url']; ?>" alt="QR">
                                <?php endif; ?>
                                <div class="item-text"><?php echo basename($row['image_url']); ?></div>
                            </div>
                            <div class="item-actions">
                                <?php if($row['status'] != 'active'): ?>
                                <div class="icon-btn icon-activate" onclick="activate(<?php echo $row['id']; ?>, 'rocket_qr')">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php else: ?>
                                <div class="icon-btn icon-activated">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php endif; ?>
                                <div class="icon-btn icon-delete" onclick="del(<?php echo $row['id']; ?>, 'rocket_qr')">
                                    <i class="fas fa-trash"></i>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- UPAY -->
        <div id="upay" class="tab-content <?php echo $_GET['tab']=='upay' ? 'active' : '' ?>">
            <div class="card">
                <h3>Upay - Numbers</h3>
                <div class="section">
                    <div class="input-group">
                        <input type="text" id="upay_num" placeholder="Enter Upay number">
                        <button class="btn" onclick="addUpay()">Add</button>
                    </div>
                    <div>
                        <?php $res = $conn->query("SELECT * FROM upay_payment WHERE image_url IS NULL ORDER BY status DESC"); ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <div class="item <?php echo $row['status']=='active'?'active':''; ?>">
                            <div class="item-left">
                                <div class="item-text"><?php echo htmlspecialchars($row['number']); ?></div>
                            </div>
                            <div class="item-actions">
                                <?php if($row['status'] != 'active'): ?>
                                <div class="icon-btn icon-activate" onclick="activate(<?php echo $row['id']; ?>, 'upay_num')">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php else: ?>
                                <div class="icon-btn icon-activated">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php endif; ?>
                                <div class="icon-btn icon-delete" onclick="del(<?php echo $row['id']; ?>, 'upay_num')">
                                    <i class="fas fa-trash"></i>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <div class="card">
                <h3>Upay - QR Codes</h3>
                <div class="section">
                    <form id="upay_qr_form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="ajax" value="1">
                        <div class="input-group">
                            <input type="file" name="upay_qr" accept="image/*" required>
                            <button type="submit" class="btn">Upload QR</button>
                        </div>
                    </form>
                    <div>
                        <?php $res = $conn->query("SELECT * FROM upay_payment WHERE image_url IS NOT NULL ORDER BY status DESC"); ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <div class="item <?php echo $row['status']=='active'?'active':''; ?>">
                            <div class="item-left">
                                <?php if($row['image_url']): ?>
                                <img src="uploads/<?php echo $row['image_url']; ?>" alt="QR">
                                <?php endif; ?>
                                <div class="item-text"><?php echo basename($row['image_url']); ?></div>
                            </div>
                            <div class="item-actions">
                                <?php if($row['status'] != 'active'): ?>
                                <div class="icon-btn icon-activate" onclick="activate(<?php echo $row['id']; ?>, 'upay_qr')">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php else: ?>
                                <div class="icon-btn icon-activated">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php endif; ?>
                                <div class="icon-btn icon-delete" onclick="del(<?php echo $row['id']; ?>, 'upay_qr')">
                                    <i class="fas fa-trash"></i>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- USDT -->
        <div id="usdt" class="tab-content <?php echo $_GET['tab']=='usdt' ? 'active' : '' ?>">
            <div class="card">
                <h3>USDT Addresses</h3>
                <div class="section">
                    <div class="input-group">
                        <input type="text" id="usdt_addr" placeholder="Enter USDT Address">
                        <button class="btn" onclick="addUSDT()">Add</button>
                    </div>
                    <div>
                        <?php $res = $conn->query("SELECT * FROM deyyamrici ORDER BY sthiti DESC"); ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <div class="item <?php echo $row['sthiti']=='1'?'active':''; ?>">
                            <div class="item-left">
                                <div class="item-text"><?php echo htmlspecialchars($row['maulya']); ?></div>
                            </div>
                            <div class="item-actions">
                                <?php if($row['sthiti'] != '1'): ?>
                                <div class="icon-btn icon-activate" onclick="activateUSDT('<?php echo addslashes($row['maulya']); ?>')">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php else: ?>
                                <div class="icon-btn icon-activated">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php endif; ?>
                                <div class="icon-btn icon-delete" onclick="delUSDT(<?php echo $row['shonu']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <div class="card">
                <h3>USDT QR Codes</h3>
                <div class="section">
                    <form id="usdt_qr_form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="ajax" value="1">
                        <div class="input-group">
                            <input type="file" name="usdt_qr" accept="image/*" required>
                            <button type="submit" class="btn">Upload QR</button>
                        </div>
                    </form>
                    <div>
                        <?php $res = $conn->query("SELECT * FROM images_usdt ORDER BY status DESC"); ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <div class="item <?php echo $row['status']=='1'?'active':''; ?>">
                            <div class="item-left">
                                <img src="uploads/USDT/<?php echo $row['filename']; ?>" alt="QR">
                                <div class="item-text"><?php echo $row['filename']; ?></div>
                            </div>
                            <div class="item-actions">
                                <?php if($row['status'] != '1'): ?>
                                <div class="icon-btn icon-activate" onclick="activateUSDTQR('<?php echo $row['filename']; ?>')">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php else: ?>
                                <div class="icon-btn icon-activated">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php endif; ?>
                                <div class="icon-btn icon-delete" onclick="delUSDTQR(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const csrf = '<?php echo $_SESSION['csrf_token']; ?>';
    
    // POPUP
    <?php if($popup_msg): ?>
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('popup').classList.add('show');
        setTimeout(() => document.getElementById('popup').classList.remove('show'), 2200);
    });
    <?php endif; ?>
    
    // SMOOTH TAB SWITCH - NO REFRESH
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(target).classList.add('active');
            history.replaceState(null, null, '?tab=' + target);
        });
    });
    
    function action(data) {
        data.append('csrf_token', csrf);
        data.append('ajax', '1');
        fetch('', { method: 'POST', body: data })
            .then(() => setTimeout(() => location.reload(), 500));
    }
    
    function add(id, field) {
        const val = document.getElementById(id).value.trim();
        if (!val) return;
        const fd = new FormData();
        fd.append(field, val);
        action(fd);
        document.getElementById(id).value = '';
    }
    
    function addRocket() {
        const num = document.getElementById('rocket_num').value.trim();
        if (!num) return;
        
        const fd = new FormData();
        fd.append('new_rocket', num);
        action(fd);
        
        document.getElementById('rocket_num').value = '';
    }
    
    function addUpay() {
        const num = document.getElementById('upay_num').value.trim();
        if (!num) return;
        
        const fd = new FormData();
        fd.append('new_upay', num);
        action(fd);
        
        document.getElementById('upay_num').value = '';
    }
    
    function addUSDT() {
        const val = document.getElementById('usdt_addr').value.trim();
        if (!val) return;
        const fd = new FormData();
        fd.append('new_usdt', val);
        action(fd);
        document.getElementById('usdt_addr').value = '';
    }
    
    function activate(id, type) {
        const fd = new FormData();
        fd.append('activate_id', id);
        fd.append('type', type);
        action(fd);
    }
    
    function activateUSDT(addr) { activate(addr, 'usdt_id'); }
    function activateUSDTQR(name) { activate(name, 'usdt_qr'); }
    
    function del(id, type) {
        if (!confirm('Delete?')) return;
        const fd = new FormData();
        fd.append('delete_id', id);
        fd.append('type', type);
        action(fd);
    }
    
    function delUSDT(id) { del(id, 'usdt_id'); }
    function delUSDTQR(id) { del(id, 'usdt_qr'); }
    
    document.querySelectorAll('form').forEach(f => f.addEventListener('submit', e => { e.preventDefault(); action(new FormData(f)); }));
</script>
</body>
</html>