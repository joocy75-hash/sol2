<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");
include 'conn.php';
// Insert/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bannerId = intval($_POST['bannerId']);
    $title = trim($_POST['title']);
    $img = trim($_POST['img']);
    $coverUrl = trim($_POST['coverUrl']);
    $jumpType = intval($_POST['jumpType']);
    $title = $conn->real_escape_string($title);
    $img = $conn->real_escape_string($img);
    $coverUrl = $conn->real_escape_string($coverUrl);
    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
        $id = intval($_POST['edit_id']);
        $sql = "UPDATE activity_banner_custom_data SET bannerId=$bannerId, title='$title', img='$img', coverUrl='$coverUrl', jumpType=$jumpType WHERE id=$id";
    } else {
        $sql = "INSERT INTO activity_banner_custom_data (bannerId, title, img, coverUrl, jumpType) VALUES ($bannerId, '$title', '$img', '$coverUrl', $jumpType)";
    }
    if ($conn->query($sql)) {
        header("Location: manage_custom_activitybanner.php?msg=success");
    } else {
        header("Location: manage_custom_activitybanner.php?msg=error");
    }
    exit;
}
// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM activity_banner_custom_data WHERE id=$id");
    header("Location: manage_custom_activitybanner.php?msg=deleted");
    exit;
}
// Edit
$editData = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $get = $conn->query("SELECT * FROM activity_banner_custom_data WHERE id=$id");
    $editData = $get->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Activity Banner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
   
    <!-- TINY MCE WITH YOUR API KEY - 100% WORKING, NO WARNING -->
    <script src="https://cdn.tiny.cloud/1/gjrcsv6cf1croapbp9epcmj1dchaghsl8ix1psc6dyx9mzcn/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
   
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
        .form-control,.form-select,textarea.form-control{
            border-radius:14px;padding:0.85rem 1.2rem;border:2px solid #cbd5e1;font-size:0.98rem;transition:border-color .3s;
        }
        .form-control:focus,.form-select:focus,textarea:focus{
            border-color:var(--blue-light);box-shadow:0 0 0 .25rem rgba(59,130,246,.25);
        }
        .btn-custom{padding:0.8rem 2rem;border-radius:50px;font-weight:600;font-size:1rem;transition:all .3s;}
        .btn-save{background:linear-gradient(135deg,var(--success),#22c55e);color:white;}
        .btn-cancel{background:#6b7280;color:white;}
        .btn-edit{background:#0d6efd;color:white;}
        .btn-delete{background:#dc2626;color:white;}
        .btn-custom:hover{opacity:0.9;}
        .banner-img{height:85px;width:155px;object-fit:cover;border-radius:12px;border:2px solid #e2e8f0;}
        .table{font-size:0.95rem;}
        .table thead{background:#f8f9fa;font-weight:700;}
        .table tbody tr:hover{background:#f0f7ff;}
        .badge{padding:0.5rem 1rem;border-radius:50px;font-weight:600;font-size:0.85rem;}
        .badge-1{background:#dbeafe;color:#1d4ed8;}
        .badge-2{background:#fef3c7;color:#92400e;}
        .badge-3{background:#d1fae5;color:#065f46;}
        .tox-tinymce{border-radius:14px;border:2px solid #cbd5e1 !important;}
        .tox-statusbar{border-top:1px solid #e2e8f0 !important;}
        @media (max-width:768px){
            .main-content{padding:1rem;}
            .page-title{font-size:1.5rem;}
            .card-header{font-size:1.1rem;padding:1rem;}
            .card-body{padding:1.5rem;}
            .btn-custom{padding:0.75rem 1.6rem;font-size:0.92rem;}
            .banner-img{height:70px;width:120px;}
            .table{font-size:0.88rem;}
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container-fluid">
    <div class="main-content">
        <h2 class="page-title">Custom Activity Banner Management</h2>
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-<?= $_GET['msg']=='success'?'success':($_GET['msg']=='deleted'?'warning':'danger') ?> alert-dismissible fade show">
                <strong>
                    <?= $_GET['msg']=='success'?'Saved Successfully!':($_GET['msg']=='deleted'?'Deleted!':'Error') ?>
                </strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <div class="card mb-4">
            <div class="card-header"><?= $editData ? 'Edit' : 'Add New' ?> Custom Banner</div>
            <div class="card-body">
                <form method="POST">
                    <?php if ($editData): ?>
                        <input type="hidden" name="edit_id" value="<?= $editData['id'] ?>">
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-bold">Banner ID</label>
                            <input type="number" name="bannerId" class="form-control" value="<?= $editData['bannerId']??'' ?>" required>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-bold">Jump Type</label>
                            <select name="jumpType" class="form-select" required>
                                <option value="1" <?= ($editData['jumpType']??1)==1?'selected':'' ?>>Internal</option>
                                <option value="2" <?= ($editData['jumpType']??1)==2?'selected':'' ?>>External</option>
                                <option value="3" <?= ($editData['jumpType']??1)==3?'selected':'' ?>>Image List (JSON)</option>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <label class="form-label fw-bold">Title</label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editData['title']??'') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Image / HTML / JSON Content</label>
                            <textarea name="img" id="tinymce-editor"><?= htmlspecialchars($editData['img']??'') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Cover URL</label>
                            <input type="url" name="coverUrl" class="form-control" value="<?= $editData['coverUrl']??'' ?>" required>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-custom btn-save"><?= $editData?'Update':'Add' ?> Banner</button>
                        <?php if ($editData): ?>
                            <a href="manage_custom_activitybanner.php" class="btn btn-custom btn-cancel ms-2">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                All Custom Banners (<?= $conn->query("SELECT COUNT(*) FROM activity_banner_custom_data")->fetch_row()[0] ?>)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="6%">#</th>
                                <th width="12%">Banner ID</th>
                                <th width="20%">Title</th>
                                <th width="12%">Jump</th>
                                <th width="18%">Cover</th>
                                <th width="20%">Content Preview</th>
                                <th width="12%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            $all = $conn->query("SELECT * FROM activity_banner_custom_data ORDER BY id DESC");
                            while ($r = $all->fetch_assoc()):
                            ?>
                            <tr>
                                <td><strong><?= $i++ ?></strong></td>
                                <td><?= $r['bannerId'] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($r['title']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $r['jumpType'] ?>">
                                        <?= $r['jumpType']==1?'Internal':($r['jumpType']==2?'External':'JSON') ?>
                                    </span>
                                </td>
                                <td>
                                    <img src="<?= htmlspecialchars($r['coverUrl']) ?>"
                                         onerror="this.src='https://via.placeholder.com/160x90/1e40af/ffffff?text=Cover'"
                                         class="banner-img" alt="">
                                </td>
                                <td class="text-start small text-muted" style="max-width:200px;overflow:hidden;">
                                    <?= htmlspecialchars(substr(strip_tags($r['img']), 0, 80)) ?>...
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="?edit=<?= $r['id'] ?>" class="btn btn-sm btn-edit">Edit</a>
                                        <a href="?delete=<?= $r['id'] ?>" class="btn btn-sm btn-delete"
                                           onclick="return confirm('Delete this banner?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
tinymce.init({
    selector: '#tinymce-editor',
    height: 450,
    plugins: 'image link lists code table',
    toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code',
    menubar: false,
    branding: false,
    content_style: "body { font-family: 'Inter', sans-serif; font-size: 14px; }",
    setup: function(editor) {
        editor.on('change keyup paste', function() {
            editor.save();
        });
    }
});
</script>
</body>
</html>