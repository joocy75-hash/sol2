<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");
include 'conn.php';

$isEdit = false;
$editData = null;

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM table_activity WHERE id = $id");
    header("Location: update_activitybanner.php?msg=deleted");
    exit;
}

// EDIT MODE
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $get = $conn->query("SELECT * FROM table_activity WHERE id = $id");
    $editData = $get->fetch_assoc();
    $isEdit = true;
}

// SAVE (ADD OR UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['bannerTitle']);
    $bannerID = (int)$_POST['bannerID'];
    $url = trim($_POST['bannerUrl']);
    $jump = (int)$_POST['jumpType'];
    $contents = trim($_POST['contents']);

    $title = $conn->real_escape_string($title);
    $url = $conn->real_escape_string($url);
    $contents = $conn->real_escape_string($contents);

    if (isset($_POST['edit_id'])) {
        $editId = (int)$_POST['edit_id'];
        $sql = "UPDATE table_activity SET bannerTitle='$title', bannerID=$bannerID, bannerUrl='$url', jumpType=$jump, contents='$contents' WHERE id=$editId";
    } else {
        $sql = "INSERT INTO table_activity (bannerTitle, bannerID, bannerUrl, jumpType, contents) VALUES ('$title', $bannerID, '$url', $jump, '$contents')";
    }

    if ($conn->query($sql)) {
        header("Location: update_activitybanner.php?msg=saved");
    } else {
        header("Location: update_activitybanner.php?msg=error");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Banner Management</title>
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
            border-radius:14px;
            padding:0.85rem 1.2rem;
            border:2px solid #cbd5e1;
            font-size:0.98rem;
            transition:border-color .3s;
        }
        .form-control:focus,.form-select:focus{
            border-color:var(--blue-light);
            box-shadow:0 0 0 .25rem rgba(59,130,246,.25);
        }
        .btn-custom{
            padding:0.8rem 2rem;
            border-radius:50px;
            font-weight:600;
            font-size:1rem;
            transition:all .3s;
        }
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
        .badge-internal{background:#dbeafe;color:#1d4ed8;}
        .badge-external{background:#fef3c7;color:#92400e;}
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

        <h2 class="page-title">Activity Banner Management</h2>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-<?= $_GET['msg']=='saved'?'success':($_GET['msg']=='deleted'?'warning':'danger') ?> alert-dismissible fade show">
                <strong>
                    <?= $_GET['msg']=='saved'?'Saved Successfully!':($_GET['msg']=='deleted'?'Deleted!':'Error') ?>
                </strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header"><?= $isEdit ? 'Edit' : 'Add New' ?> Banner</div>
            <div class="card-body">
                <form method="POST">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="edit_id" value="<?= $editData['id'] ?>">
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-lg-6 col-md-6">
                            <label class="form-label fw-bold">Banner Title</label>
                            <input type="text" name="bannerTitle" class="form-control" value="<?= $editData['bannerTitle']??'' ?>" required>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <label class="form-label fw-bold">Banner ID</label>
                            <input type="number" name="bannerID" class="form-control" value="<?= $editData['bannerID']??'' ?>" required>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <label class="form-label fw-bold">Banner Image URL</label>
                            <input type="url" name="bannerUrl" class="form-control" value="<?= $editData['bannerUrl']??'' ?>" required>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <label class="form-label fw-bold">Jump Type</label>
                            <select name="jumpType" class="form-select" required>
                                <option value="1" <?= ($editData['jumpType']??1)==1?'selected':'' ?>>Internal</option>
                                <option value="2" <?= ($editData['jumpType']??1)==2?'selected':'' ?>>External</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Redirect URL / Contents (Optional)</label>
                            <input type="text" name="contents" class="form-control" placeholder="Leave blank if not needed" value="<?= $editData['contents']??'' ?>">
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-custom btn-save"><?= $isEdit?'Update':'Add' ?> Banner</button>
                        <?php if ($isEdit): ?>
                            <a href="update_activitybanner.php" class="btn btn-custom btn-cancel ms-2">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                All Activity Banners (<?= $conn->query("SELECT COUNT(*) FROM table_activity")->fetch_row()[0] ?>)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="18%">Image</th>
                                <th width="20%">Title</th>
                                <th width="10%">ID</th>
                                <th width="12%">Jump</th>
                                <th width="25%">Contents</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            $result = $conn->query("SELECT * FROM table_activity ORDER BY id DESC");
                            while ($row = $result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><strong><?= $i++ ?></strong></td>
                                <td>
                                    <img src="<?= htmlspecialchars($row['bannerUrl']) ?>" 
                                         onerror="this.src='https://via.placeholder.com/160x90/1e40af/ffffff?text=No+Image'" 
                                         class="banner-img" alt="">
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($row['bannerTitle']) ?></td>
                                <td><?= $row['bannerID'] ?></td>
                                <td>
                                    <span class="badge badge-<?= $row['jumpType']==1?'internal':'external' ?>">
                                        <?= $row['jumpType']==1?'Internal':'External' ?>
                                    </span>
                                </td>
                                <td class="text-break"><?= htmlspecialchars($row['contents']?:'-') ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-edit">Edit</a>
                                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-delete" 
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

</body>
</html>