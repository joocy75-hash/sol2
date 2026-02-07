<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit();
}
include "conn.php";

// Handle upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_FILES['banners']) || isset($_FILES['drop_files']))) {
    $upload_dir = "uploads/Banners/";
    $domain = "https://Sol-0203.com/codehub94/admin/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $files = isset($_FILES['banners']) ? $_FILES['banners'] : $_FILES['drop_files'];
    $success = 0;
    $errors = [];
    
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] == 0) {
            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            if (in_array($ext, ['png','jpg','jpeg'])) {
                $filename = time().rand(1000,9999).".$ext";
                $path = $upload_dir.$filename;
                $url = $domain.$path;
                if (move_uploaded_file($files['tmp_name'][$i], $path)) {
                    $url = mysqli_real_escape_string($conn, $url);
                    if (mysqli_query($conn, "INSERT INTO banners (banner_url, is_active) VALUES ('$url', 1)")) {
                        $success++;
                    } else {
                        $errors[] = "DB Error: " . $files['name'][$i];
                    }
                } else {
                    $errors[] = "Upload Failed: " . $files['name'][$i];
                }
            } else {
                $errors[] = "Invalid Format: " . $files['name'][$i];
            }
        } elseif ($files['error'][$i] != 4) {
            $errors[] = "Error Code " . $files['error'][$i] . ": " . $files['name'][$i];
        }
    }
    
    // Silent redirect - no alerts
    header("Location: " . $_SERVER['PHP_SELF'] . "?uploaded=" . $success);
    exit();
}

// Update URL
if (isset($_POST['updateLink'])) {
    $id = (int)$_POST['id'];
    $url = mysqli_real_escape_string($conn, $_POST['redirectUrl']);
    mysqli_query($conn, "UPDATE banners SET redirect_url='$url' WHERE id=$id");
    header("Location: " . $_SERVER['PHP_SELF'] . "?updated=1");
    exit();
}

// Bulk delete
if (isset($_POST['delete_selected']) && !empty($_POST['banner_ids'])) {
    $ids = array_map('intval', $_POST['banner_ids']);
    $ids_str = implode(',', $ids);
    $res = mysqli_query($conn, "SELECT banner_url FROM banners WHERE id IN ($ids_str)");
    while ($row = mysqli_fetch_assoc($res)) {
        $file = __DIR__.'/'.str_replace("https://api.codehub94.online/fairobet/admin/","", $row['banner_url']);
        if (file_exists($file)) unlink($file);
    }
    mysqli_query($conn, "DELETE FROM banners WHERE id IN ($ids_str)");
    header("Location: " . $_SERVER['PHP_SELF'] . "?deleted=" . count($ids));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banner Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root{
            --blue:#1e40af;--blue-light:#3b82f6;--gray:#f8fafc;--danger:#dc2626;--success:#16a34a;
        }
        *{box-sizing:border-box;margin:0;padding:0;}
        html,body{background:var(--gray);font-family:'Inter',sans-serif;overflow-x:hidden;}
        .container-fluid{padding:0;}
        .main-content{padding:2rem;background:white;min-height:100vh;}
        .page-title{font-size:1.8rem;font-weight:900;color:var(--blue);margin-bottom:2rem;padding-bottom:0.8rem;border-bottom:5px solid var(--blue-light);text-align:center;}
        .card{border-radius:20px;overflow:hidden;box-shadow:0 12px 40px rgba(0,0,0,0.12);border:1px solid #e2e8f0;margin-bottom:2rem;}
        .card-header{background:linear-gradient(135deg,var(--blue),var(--blue-light));color:white;padding:1.1rem 1.5rem;font-weight:700;font-size:1.15rem;}
        .card-body{padding:1.8rem;}
        
        .upload-card .card-body{padding:1.8rem;}
        .drop-zone{
            border:2.5px dashed var(--blue-light);
            border-radius:16px;
            padding:1.8rem;
            text-align:center;
            background:#f5f8ff;
            transition:all .3s;
            cursor:pointer;
            position:relative;
            user-select:none;
        }
        .drop-zone:hover,.drop-zone.dragover{background:#e8f0ff;border-color:var(--blue);}
        .drop-zone.uploading{background:#e0f2fe;border-color:var(--success);pointer-events:none;}
        .drop-zone i{font-size:2.8rem;color:var(--blue);margin-bottom:0.8rem;}
        .drop-zone p{font-size:1rem;font-weight:600;margin:0.4rem 0;}
        .drop-zone small{color:#64748b;font-size:0.88rem;}
        
        .upload-progress{
            display:none;
            margin-top:1rem;
        }
        .upload-progress.show{display:block;}
        .progress{height:8px;border-radius:10px;}
        .upload-info{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-top:0.5rem;
            font-size:0.9rem;
            color:#64748b;
        }
        
        .btn-custom{padding:0.75rem 1.8rem;border-radius:50px;font-weight:600;font-size:0.95rem;transition:all .3s;}
        .btn-upload{background:linear-gradient(135deg,var(--success),#22c55e);color:white;display:none;}
        .btn-update{background:linear-gradient(135deg,#0d6efd,#0dcaf0);color:white;}
        .btn-delete-selected{
            background:linear-gradient(135deg,var(--danger),#ef4444);
            color:white;
            display:none;
            position:fixed;
            bottom:20px;
            right:20px;
            z-index:9999;
            padding:1rem 2.2rem;
            font-size:1rem;
            box-shadow:0 12px 35px rgba(220,38,38,0.5);
            border:none;
            border-radius:50px;
        }
        .btn-custom:hover,.btn-delete-selected:hover{transform:translateY(-4px);}
        
        .banner-img{height:78px;width:150px;object-fit:cover;border-radius:12px;border:2px solid #e2e8f0;}
        
        .table{font-size:0.94rem;}
        .table thead{background:#f8f9fa;font-weight:700;}
        .table tbody tr:hover{background:#f0f7ff !important;}
        
        .form-check-input{width:1.4em;height:1.4em;cursor:pointer;}
        .form-check-input:checked{background-color:var(--blue);border-color:var(--blue);}
        
        .url-header-cell{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding-right:1rem !important;
        }
        .url-header-text{flex:1;}
        .url-cell{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding-right:1rem !important;
        }
        .url-text{flex:1;word-break:break-all;}
        .checkbox-wrapper{
            display:flex;
            align-items:center;
            justify-content:center;
            width:50px;
        }
        
        @keyframes pulse{
            0%,100%{opacity:1;}
            50%{opacity:0.5;}
        }
        .uploading-text{animation:pulse 1.5s infinite;}
        
        @media (max-width:992px){
            .main-content{padding:1.5rem;}
        }
        @media (max-width:768px){
            .main-content{padding:1rem;}
            .page-title{font-size:1.4rem;}
            .card-header{font-size:1.05rem;padding:0.9rem 1.2rem;}
            .card-body{padding:1.4rem;}
            .drop-zone{padding:1.5rem;}
            .drop-zone i{font-size:2.5rem;}
            .btn-delete-selected{bottom:15px;right:15px;padding:0.9rem 1.8rem;font-size:0.95rem;}
            .banner-img{height:65px;width:110px;}
            .table{font-size:0.88rem;}
            .url-header-cell,.url-cell{padding-right:0.5rem !important;}
            .checkbox-wrapper{width:40px;}
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container-fluid">
    <div class="main-content">

        <h2 class="page-title">Banner Management</h2>

        <!-- Upload -->
        <div class="card upload-card">
            <div class="card-header">Upload New Banners</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="drop-zone" id="dropZone">
                        <i class="fas fa-cloud-upload-alt" id="uploadIcon"></i>
                        <p id="dropText">Drag & Drop images here</p>
                        <p class="text-muted small" id="dropSubtext">or click to select (PNG, JPG, JPEG)</p>
                        <input type="file" name="banners[]" id="fileInput" multiple accept="image/png,image/jpg,image/jpeg" style="position:absolute;inset:0;opacity:0;cursor:pointer;">
                    </div>
                    <div class="upload-progress" id="uploadProgress">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="progressBar" style="width:0%"></div>
                        </div>
                        <div class="upload-info">
                            <span id="uploadStatus">Uploading...</span>
                            <span id="uploadCount">0 / 0</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Update URL -->
        <div class="card">
            <div class="card-header">Update Redirect URL</div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-lg-5 col-md-6">
                        <select name="id" class="form-select" required>
                            <option value="">Select Banner</option>
                            <?php
                            $res = mysqli_query($conn, "SELECT id, banner_url FROM banners ORDER BY id DESC");
                            while ($r = mysqli_fetch_assoc($res)) {
                                echo "<option value='{$r['id']}'>ID: {$r['id']} - ".basename($r['banner_url'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-lg-5 col-md-6">
                        <input type="url" name="redirectUrl" class="form-control" placeholder="https://example.com" required>
                    </div>
                    <div class="col-lg-2 col-md-12">
                        <button type="submit" name="updateLink" class="btn btn-custom btn-update w-100">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- All Banners -->
        <div class="card">
            <div class="card-header">
                All Banners (<?= mysqli_num_rows(mysqli_query($conn, "SELECT id FROM banners")) ?>)
            </div>
            <div class="card-body p-0">
                <form method="POST" id="bulkForm"></form>
                <button type="submit" name="delete_selected" id="deleteBtn" form="bulkForm" class="btn btn-delete-selected">
                    Delete Selected
                </button>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="10%">ID</th>
                                <th width="22%">Banner</th>
                                <th width="68%" class="url-header-cell">
                                    <div class="url-header-text">Redirect URL</div>
                                    <div class="checkbox-wrapper">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = mysqli_query($conn, "SELECT * FROM banners ORDER BY id DESC");
                            while ($row = mysqli_fetch_array($res)) {
                            ?>
                            <tr>
                                <td><strong>#<?= $row['id'] ?></strong></td>
                                <td><img src="<?= $row['banner_url'] ?>" class="banner-img" alt=""></td>
                                <td class="url-cell">
                                    <div class="url-text">
                                        <a href="<?= htmlspecialchars($row['redirect_url']) ?>" target="_blank" class="text-primary text-decoration-underline">
                                            <?= htmlspecialchars(strlen($row['redirect_url'])>70?substr($row['redirect_url'],0,70).'...':($row['redirect_url']?:'Not set')) ?>
                                        </a>
                                    </div>
                                    <div class="checkbox-wrapper">
                                        <input type="checkbox" name="banner_ids[]" value="<?= $row['id'] ?>" class="form-check-input banner-check" form="bulkForm">
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function(){
    const $dropZone=$('#dropZone'),$fileInput=$('#fileInput'),$selectAll=$('#selectAll'),$checks=$('.banner-check'),$deleteBtn=$('#deleteBtn');
    const $uploadProgress=$('#uploadProgress'),$progressBar=$('#progressBar'),$uploadStatus=$('#uploadStatus'),$uploadCount=$('#uploadCount');
    const $uploadIcon=$('#uploadIcon'),$dropText=$('#dropText'),$dropSubtext=$('#dropSubtext');

    // Auto-submit on file selection
    $fileInput.on('change', function(){
        if(this.files.length > 0){
            startUpload(this.files.length);
            $('#uploadForm')[0].submit();
        }
    });

    // Full Click + Drag & Drop
    $dropZone.on('click', function(e){
        if(e.target === this || !$(e.target).closest('input').length){
            $fileInput.click();
        }
    });

    $dropZone.on('dragover dragenter',e=>{e.preventDefault();$dropZone.addClass('dragover');});
    $dropZone.on('dragleave dragend',()=>$dropZone.removeClass('dragover'));
    $dropZone.on('drop',e=>{
        e.preventDefault();$dropZone.removeClass('dragover');
        const files=e.originalEvent.dataTransfer.files;
        if(files.length){
            $fileInput[0].files=files;
            startUpload(files.length);
            $('#uploadForm')[0].submit();
        }
    });

    function startUpload(fileCount){
        $dropZone.addClass('uploading');
        $uploadIcon.removeClass('fa-cloud-upload-alt').addClass('fa-spinner fa-spin');
        $dropText.text('Uploading...').addClass('uploading-text');
        $dropSubtext.hide();
        $uploadProgress.addClass('show');
        $uploadCount.text('0 / ' + fileCount);
        
        // Simulate progress
        let progress = 0;
        const interval = setInterval(()=>{
            progress += Math.random() * 15;
            if(progress > 95) progress = 95;
            $progressBar.css('width', progress + '%');
        }, 200);
        
        // Store interval to clear later if needed
        window.uploadInterval = interval;
    }

    // Select All + Row Highlight
    $selectAll.click(function(){
        $checks.prop('checked', this.checked);
        $checks.closest('tr').toggleClass('table-primary', this.checked);
        toggleBtn();
    });

    $checks.click(function(){
        $(this).closest('tr').toggleClass('table-primary', this.checked);
        $selectAll.prop('checked', $checks.length === $checks.filter(':checked').length);
        toggleBtn();
    });

    function toggleBtn(){
        $checks.filter(':checked').length>0?$deleteBtn.fadeIn(300):$deleteBtn.fadeOut(300);
    }
    toggleBtn();
});
</script>

</body>
</html>