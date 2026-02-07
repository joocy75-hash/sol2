<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");
include 'conn.php';

if (!$conn) die("Database Connection Failed");

// === SAVE SETTINGS ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_settings'])) {
    $register_bonus = (int)$_POST['register_bonus'];
    $app_download   = $_POST['app_download'];
    $web_name       = $_POST['web_name'];

    $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT web_logo, favicon_logo FROM web_setting WHERE id=1"));
    $web_logo = $old['web_logo'];
    $favicon_logo = $old['favicon_logo'];

    $upload_dir = __DIR__ . "/logos/";
    $base_url   = "https://Sol-0203.com/codehub94/admin/logos/";
    !is_dir($upload_dir) && mkdir($upload_dir, 0755, true);

    $allowed = ['jpg','jpeg','png','ico'];
    $max_size = 5 * 1024 * 1024;

    if (!empty($_FILES['web_logo']['name']) && $_FILES['web_logo']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['web_logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['web_logo']['size'] <= $max_size) {
            $name = $_FILES['web_logo']['name'];
            if (file_exists($upload_dir . $name)) $name = pathinfo($_FILES['web_logo']['name'], PATHINFO_FILENAME) . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['web_logo']['tmp_name'], $upload_dir . $name);
            $web_logo = $base_url . $name;
            @copy($upload_dir . $name, $_SERVER['DOCUMENT_ROOT'] . "/assets/png/logo-e926b199.png");
        }
    }

    if (!empty($_FILES['favicon_logo']['name']) && $_FILES['favicon_logo']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['favicon_logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['favicon_logo']['size'] <= $max_size) {
            $name = $_FILES['favicon_logo']['name'];
            if (file_exists($upload_dir . $name)) $name = pathinfo($_FILES['favicon_logo']['name'], PATHINFO_FILENAME) . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['favicon_logo']['tmp_name'], $upload_dir . $name);
            $favicon_logo = $base_url . $name;
        }
    }

    $stmt = $conn->prepare("UPDATE web_setting SET register_bonus=?, app_download=?, web_name=?, web_logo=?, favicon_logo=? WHERE id=1");
    $stmt->bind_param("issss", $register_bonus, $app_download, $web_name, $web_logo, $favicon_logo);
    $msg = $stmt->execute() ? "Settings updated successfully" : "Error occurred";
    $stmt->close();
}

// === SUPPORT LINKS ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_link'])) {
    $id = (int)$_POST['link_id'];
    $name = trim($_POST['link_name']);
    $url = trim($_POST['link_url']);
    $sort = (int)$_POST['link_sort'];
    $active = isset($_POST['link_active']) ? 1 : 0;

    $name = $conn->real_escape_string($name);
    $url = $conn->real_escape_string($url);

    if ($id > 0) {
        $conn->query("UPDATE support_links SET name='$name', url='$url', sort_order=$sort, is_active=$active WHERE id=$id");
    } else {
        $conn->query("INSERT INTO support_links (name, url, sort_order, is_active) VALUES ('$name', '$url', $sort, $active)");
    }
    $msg = "Link saved";
}

if (isset($_GET['delete_link'])) {
    $id = (int)$_GET['delete_link'];
    $conn->query("DELETE FROM support_links WHERE id=$id");
    $msg = "Link removed";
}

$settings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM web_setting WHERE id=1"));
$links = mysqli_query($conn, "SELECT * FROM support_links ORDER BY sort_order ASC, id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Settings</title>
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
        .form-control,.form-select{
            border-radius:14px;padding:0.85rem 1.2rem;border:2px solid #cbd5e1;font-size:0.98rem;transition:border-color .3s;
        }
        .form-control:focus,.form-select:focus{
            border-color:var(--blue-light);box-shadow:0 0 0 .25rem rgba(59,130,246,.25);
        }
        .btn-custom{padding:0.8rem 2rem;border-radius:50px;font-weight:600;font-size:1rem;transition:all .3s;}
        .btn-save{background:linear-gradient(135deg,var(--success),#22c55e);color:white;}
        .btn-add{background:linear-gradient(135deg,var(--blue),var(--blue-light));color:white;}
        .btn-delete{background:#dc2626;color:white;}
        .btn-custom:hover{opacity:0.9;}
        .logo-preview{height:90px;width:160px;object-fit:contain;border-radius:12px;border:2px solid #e2e8f0;background:#f8f9fa;padding:8px;}
        .link-item{
            background:#f8f9ff;border:1px solid #e2e8f0;border-radius:16px;padding:1.2rem;
            margin-bottom:1rem;transition:all .3s;
        }
        .link-item:hover{background:#f0f4ff;}
        .switch{position:relative;display:inline-block;width:52px;height:28px;}
        .switch input{opacity:0;width:0;height:0;}
        .slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#cbd5e1;transition:.3s;border-radius:50px;}
        .slider:before{position:absolute;content:"";height:22px;width:22px;left:3px;bottom:3px;background:white;transition:.3s;border-radius:50%;}
        input:checked + .slider{background:var(--blue);}
        input:checked + .slider:before{transform:translateX(24px);}
        @media (max-width:768px){
            .main-content{padding:1rem;}
            .page-title{font-size:1.5rem;}
            .card-header{font-size:1.1rem;padding:1rem;}
            .card-body{padding:1.5rem;}
            .btn-custom{padding:0.75rem 1.6rem;font-size:0.92rem;}
            .logo-preview{height:70px;width:120px;}
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container-fluid">
    <div class="main-content">

        <h2 class="page-title">Website Settings</h2>

        <?php if (isset($msg)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <strong><?= htmlspecialchars($msg) ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Website Settings -->
        <div class="card mb-4">
            <div class="card-header">Website Configuration</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="save_settings" value="1">
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-bold">Register Bonus</label>
                            <input type="number" name="register_bonus" class="form-control" value="<?= $settings['register_bonus'] ?>" required>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-bold">App Download URL</label>
                            <input type="url" name="app_download" class="form-control" value="<?= $settings['app_download'] ?>" required>
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <label class="form-label fw-bold">Website Name</label>
                            <input type="text" name="web_name" class="form-control" value="<?= htmlspecialchars($settings['web_name']) ?>" required>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label fw-bold">Website Logo</label>
                            <input type="file" name="web_logo" class="form-control" accept=".jpg,.jpeg,.png">
                            <?php if ($settings['web_logo']): ?>
                                <img src="<?= $settings['web_logo'] ?>" class="logo-preview mt-2" alt="Current Logo">
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label fw-bold">Favicon</label>
                            <input type="file" name="favicon_logo" class="form-control" accept=".jpg,.jpeg,.png,.ico">
                            <?php if ($settings['favicon_logo']): ?>
                                <img src="<?= $settings['favicon_logo'] ?>" class="logo-preview mt-2" alt="Current Favicon">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-custom btn-save">Update Settings</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Support Links -->
        <div class="card">
            <div class="card-header">
                Support Links Management
                <small class="text-light opacity-75 d-block">Add & manage customer support channels</small>
            </div>
            <div class="card-body">

                <!-- Existing Links -->
                <?php mysqli_data_seek($links, 0); while($l = mysqli_fetch_assoc($links)): ?>
                <form method="POST" class="link-item mb-3">
                    <input type="hidden" name="save_link" value="1">
                    <input type="hidden" name="link_id" value="<?= $l['id'] ?>">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-3 col-md-6">
                            <input type="text" name="link_name" class="form-control" value="<?= htmlspecialchars($l['name']) ?>" placeholder="Link Name" required>
                        </div>
                        <div class="col-lg-5 col-md-6">
                            <input type="url" name="link_url" class="form-control" value="<?= htmlspecialchars($l['url']) ?>" placeholder="https://t.me/..." required>
                        </div>
                        <div class="col-lg-1">
                            <input type="number" name="link_sort" class="form-control text-center" value="<?= $l['sort_order'] ?>" min="1">
                        </div>
                        <div class="col-lg-1">
                            <label class="switch">
                                <input type="checkbox" name="link_active" <?= $l['is_active']?'checked':'' ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="col-lg-2 text-end">
                            <button type="submit" class="btn btn-sm btn-save">Update</button>
                            <a href="?delete_link=<?= $l['id'] ?>" class="btn btn-sm btn-delete" onclick="return confirm('Delete this link?')">Delete</a>
                        </div>
                    </div>
                </form>
                <?php endwhile; ?>

                <!-- Add New Link -->
                <form method="POST" class="link-item" style="border:2px dashed var(--blue-light);background:#f0f7ff;">
                    <input type="hidden" name="save_link" value="1">
                    <input type="hidden" name="link_id" value="0">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-3 col-md-6">
                            <input type="text" name="link_name" class="form-control" placeholder="New Link Name" required>
                        </div>
                        <div class="col-lg-5 col-md-6">
                            <input type="url" name="link_url" class="form-control" placeholder="https://t.me/support" required>
                        </div>
                        <div class="col-lg-1">
                            <input type="number" name="link_sort" class="form-control text-center" value="<?= mysqli_num_rows(mysqli_query($conn,'SELECT id FROM support_links'))+1 ?>">
                        </div>
                        <div class="col-lg-1">
                            <label class="switch">
                                <input type="checkbox" name="link_active" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="col-lg-2 text-end">
                            <button type="submit" class="btn btn-custom btn-add">Add Link</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

</body>
</html>