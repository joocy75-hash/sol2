<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");

include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $status = isset($_POST['status']) ? 1 : 0;

    if ($id == '') {
        mysqli_query($conn, "INSERT INTO website_messages (title, message, message_type, status) VALUES ('$title', '$message', '$type', $status)");
    } else {
        mysqli_query($conn, "UPDATE website_messages SET title='$title', message='$message', message_type='$type', status=$status WHERE id=$id");
    }

    header("Location: manage_webmessage.php");
    exit;
}

if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM website_messages WHERE id=$deleteId");
    header("Location: manage_webmessage.php");
    exit;
}

$messages = mysqli_query($conn, "SELECT * FROM website_messages ORDER BY created_at DESC");

$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM website_messages WHERE id=$editId");
    $editData = mysqli_fetch_assoc($res);
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Popup Message Editor</title>

    <style>
        .card-header-blue {
            background: linear-gradient(to right, #1e40af, #1f4bb9);
            color: white;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }
        .card-footer-green {
            background: #0016b5;
            color: white;
            border-bottom-left-radius: 1rem;
            border-bottom-right-radius: 1rem;
        }
        .btn-icon {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }.card.custom-editor {
    max-width: 100%;
    width: 100%;
    padding: 20px;
    margin-top: 30px;
    border-radius: 16px;
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.05);
}

.card-header.bg-primary {
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;
}

.card-body input,
.card-body textarea {
    background-color: #f9f9f9;
    border: 1px solid #ddd;
}

    .custom-editor-card {
    max-width: 1500px;
    width: 100%;
    margin: 40px auto;                /* Center horizontally */
    padding: 25px;
    border-radius: 16px;
    background-color: #ffffff;
    box-shadow: 0 0 18px rgba(0, 0, 0, 0.08);
    border: 1px solid #eee;
}

/* Extra wrapper to control spacing from sidebar */
.editor-wrapper {
    padding: 30px 20px; /* top/bottom 30px, left/right 20px */
    background: #f6f8fa;
}

    }
    .form-label {
        font-weight: 600;
    }
    .form-control {
        border-radius: 8px;
    }


	.cool-input {
        border: 2px solid #007bff;
        border-radius: 0.25rem;
        padding: 0.5rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    .cool-input:focus {
        border-color: #0056b3;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    .cool-input::placeholder {
        color: #6c757d;
        opacity: 1;
    }
	.cool-button {
        padding: 0.5rem 1rem;
        font-size: 1rem;
        border-radius: 0.25rem;
        transition: all 0.3s ease;
    }
    .cool-button:hover {
        background-color: #0056b3;
        color: #fff;
    }
    .cool-button.btn-secondary:hover {
        background-color: #343a40;
        color: #fff;
    }
	#copied{
		visibility: hidden;
		z-index: 1;
		position: fixed;
		bottom: 50%;
		background-color: #333;
		color: #fff;
		border-radius: 6px;
		padding: 16px;
		max-width: 250px;
		font-size: 17px;
	}	   
	#copied.show {
		visibility: visible;
		-webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
		animation: fadein 0.5s, fadeout 0.5s 2.5s;
	}
	body.bg-light {
    background-color: #0d1117 !important;
    color: #c9d1d9 !important;
  }

  .card.shadow-lg {
    background-color: #161b22 !important;
    color: #c9d1d9 !important;
    border: 1px solid #30363d;
  }

  .card-header-blue {
    background-color: #58a6ff !important;
    color: #000 !important;
  }

  .card-footer-green {
    background-color: #21262d !important;
    border-top: 1px solid #30363d;
  }

  .form-label {
    color: #c9d1d9 !important;
  }

  .form-control {
    background-color: #0d1117 !important;
    color: #c9d1d9 !important;
    border: 1px solid #30363d;
  }

  .form-control:focus {
    background-color: #161b22 !important;
    color: #c9d1d9 !important;
    border-color: #58a6ff !important;
  }

  .table {
    background-color: #161b22;
    color: #c9d1d9;
  }

  .table th, .table td {
    border-color: #30363d;
  }

  .table-primary {
    background-color: #1f6feb !important;
    color: #ffffff !important;
  }

  .btn {
    font-weight: 500;
  }

  .btn-warning {
    background-color: #d29922 !important;
    color: #000 !important;
  }

  .btn-danger {
    background-color: #da3633 !important;
    color: #fff !important;
  }

  .btn-light {
    background-color: #f0f6fc !important;
    color: #0d1117 !important;
  }

  .shadow-sm {
    box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important;
  }
    </style>
    
    
    <style>
/* ✅ Custom Switch Style */
.custom-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
    margin-right: 10px;
}
.custom-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.custom-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.4s;
    border-radius: 34px;
}
.custom-slider::before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}
.custom-switch input:checked + .custom-slider {
    background-color: #0016b5;
}
.custom-switch input:checked + .custom-slider::before {
    transform: translateX(26px);
}
</style>


    <?php include 'header.php'; ?>
    
    
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-lg">
        <div class="card-header card-header-blue d-flex justify-content-between align-items-center">
            <h5 class="mb-0">🎯 Popup Message Editor (Structured)</h5>
            <?php if ($editData): ?>
                <a href="manage_webmessage.php" class="btn btn-sm btn-light">Cancel Edit</a>
            <?php endif; ?>
        </div>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Popup Title</label>
                    <input type="text" name="title" class="form-control" value="<?= $editData['title'] ?? '' ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Popup Message</label>
                    <textarea name="message" rows="4" class="form-control" required><?= $editData['message'] ?? '' ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message Type</label>
                    <input type="text" name="type" class="form-control" value="<?= $editData['message_type'] ?? 'general' ?>">
                </div>
              <div class="mb-3 d-flex align-items-center">
    <label class="custom-switch">
        <input type="checkbox" name="status" id="statusSwitch" <?= (isset($editData) && $editData['status']) ? 'checked' : '' ?>>
        <span class="custom-slider"></span>
    </label>
    <label for="statusSwitch" style="margin: 0; font-size: 14px;">Show Popup (Active)</label>
</div>

</div>

<div class="card-footer card-footer-green" style="display: flex; justify-content: flex-end;">
    <button type="submit" class="btn btn-light" style="display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; font-weight: 500; padding: 10px 16px; border-radius: 6px;">
        💾 <span>Save Changes</span>
    </button>
</div>

        </form>
    </div>

    <div class="mt-5">
        <h4 class="mb-3">📋 All Popups</h4>
        <table class="table table-bordered table-striped shadow-sm bg-white">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = mysqli_fetch_assoc($messages)) { ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= $row['message_type'] ?></td>
                    <td><?= $row['status'] ? '✅ Active' : '❌ Inactive' ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this message?')">🗑️ Delete</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
