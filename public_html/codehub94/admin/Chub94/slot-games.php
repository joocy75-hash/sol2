<?php include "header.php"; ?>
<?php include "../conn.php"; ?>

<?php
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
$selected_vendor = $_GET['vendor'] ?? '';

// === SAVE GAME ===
if ($_POST && $_POST['action'] === 'save') {
    $vendor_code = trim($_POST['vendor_code']);
    
    // Count games for this vendor
    $count_q = $conn->prepare("SELECT COUNT(*) FROM game_slot WHERE vendor_code = ?");
    $count_q->bind_param("s", $vendor_code);
    $count_q->execute();
    $count_q->bind_result($count);
    $count_q->fetch();
    $count_q->close();

    if ($count >= 6 && empty($_POST['id'])) {
        header("Location: slot-games.php?vendor=$vendor_code&error=max6");
        exit;
    }

    $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $game_id = trim($_POST['game_id']);
    $game_name_en = trim($_POST['game_name_en']);
    $game_image = trim($_POST['game_image'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $status = isset($_POST['status']) ? 1 : 0;

    // Auto get vendor name
    $v_q = $conn->prepare("SELECT vendor_name FROM game_slot WHERE vendor_code = ? LIMIT 1");
    $v_q->bind_param("s", $vendor_code);
    $v_q->execute();
    $v_res = $v_q->get_result();
    $vendor_name = $v_res->num_rows ? $v_res->fetch_assoc()['vendor_name'] : $vendor_code;
    $v_q->close();

    if ($id) {
        $stmt = $conn->prepare("UPDATE game_slot SET vendor_code=?, vendor_name=?, game_id=?, game_name_en=?, game_image=?, sort_order=?, status=? WHERE id=?");
        $stmt->bind_param("sssssiii", $vendor_code, $vendor_name, $game_id, $game_name_en, $game_image, $sort_order, $status, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO game_slot (vendor_code, vendor_name, game_id, game_name_en, game_image, sort_order, status) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssii", $vendor_code, $vendor_name, $game_id, $game_name_en, $game_image, $sort_order, $status);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: slot-games.php?vendor=" . urlencode($vendor_code) . "&success=1");
    exit;
}

// === DELETE & TOGGLE ===
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM game_slot WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: slot-games.php?vendor=$selected_vendor&success=1");
    exit;
}
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $conn->prepare("UPDATE game_slot SET status = IF(status=1,0,1) WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: slot-games.php?vendor=$selected_vendor&success=1");
    exit;
}

// Fetch all vendors
$vendors = [];
$v_result = $conn->query("SELECT DISTINCT vendor_code, vendor_name FROM game_slot ORDER BY vendor_name");
while ($row = $v_result->fetch_assoc()) {
    $vendors[] = $row;
}

// Selected vendor games
$games = false;
$vendor_display = '';
$game_count = 0;
if ($selected_vendor) {
    $stmt = $conn->prepare("SELECT * FROM game_slot WHERE vendor_code = ? ORDER BY sort_order ASC, game_name_en");
    $stmt->bind_param("s", $selected_vendor);
    $stmt->execute();
    $games = $stmt->get_result();
    $game_count = $games->num_rows;

    $name_q = $conn->prepare("SELECT vendor_name FROM game_slot WHERE vendor_code = ? LIMIT 1");
    $name_q->bind_param("s", $selected_vendor);
    $name_q->execute();
    $res = $name_q->get_result();
    if ($r = $res->fetch_assoc()) $vendor_display = $r['vendor_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slot Games Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2563eb; --primary-blue-dark: #1d4ed8; --primary-blue-light: #3b82f6;
            --success-green: #10b981; --error-red: #ef4444;
            --gray-50: #f8fafc; --gray-100: #f1f5f9; --gray-200: #e2e8f0;
        }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%); }
        .glass { background: rgba(255,255,255,0.9); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.3); }
        .glass-card { @apply glass rounded-2xl overflow-hidden shadow-lg; }
        .img-ratio { position: relative; width: 100%; padding-top: 133.33%; overflow: hidden; background: #f1f5f9; }
        .img-ratio img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; }
        .hover-lift { transition: all 0.3s ease; }
        .hover-lift:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(0,0,0,0.12); }
        .btn-primary { background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-dark)); color: white; }
        .btn-primary:hover { background: linear-gradient(135deg, var(--primary-blue-light), var(--primary-blue)); }

        /* MODAL WITH PROPER BACKGROUND */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(12px);
            display: flex; align-items: center; justify-content: center; z-index: 9999;
            opacity: 0; visibility: hidden; transition: all 0.4s ease;
        }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        .modal-content {
            transform: scale(0.9); opacity: 0; transition: all 0.4s ease;
            max-width: 600px; width: 95%;
        }
        .modal-overlay.active .modal-content { transform: scale(1); opacity: 1; }
        .input-field { @apply w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-200 focus:border-blue-500 outline-none; }
    </style>
</head>
<body class="text-gray-800">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <?php if ($success): ?>
        <div class="glass p-5 rounded-2xl mb-6 flex items-center gap-3 text-green-700">
            <i class="fas fa-check-circle text-2xl"></i> Game saved successfully!
        </div>
    <?php endif; ?>

    <?php if ($error === 'max6'): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 p-5 rounded-2xl mb-6 flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-2xl"></i>
            <strong>Maximum 6 games allowed per vendor!</strong>
        </div>
    <?php endif; ?>

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Slot Games Management</h1>
            <p class="text-gray-600 mt-1">Max 6 games per vendor</p>
        </div>
        <?php if (!$selected_vendor || $game_count < 6): ?>
            <button onclick="openAddModal()" class="px-6 py-3 btn-primary hover-lift rounded-xl font-semibold flex items-center gap-2">
                <i class="fas fa-plus"></i> Add New Game
            </button>
        <?php else: ?>
            <div class="px-6 py-3 bg-gray-300 text-gray-600 rounded-xl font-semibold flex items-center gap-2">
                <i class="fas fa-lock"></i> Max 6 Reached
            </div>
        <?php endif; ?>
    </div>

    <!-- VENDORS GRID -->
    <?php if (!$selected_vendor): ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
            <?php foreach ($vendors as $vendor): 
                // Fetch ONE game image from this vendor
                $img_q = $conn->prepare("SELECT game_image FROM game_slot WHERE vendor_code = ? AND game_image != '' AND game_image IS NOT NULL LIMIT 1");
                $img_q->bind_param("s", $vendor['vendor_code']);
                $img_q->execute();
                $img_res = $img_q->get_result();
                $vendor_img = $img_res->num_rows ? $img_res->fetch_assoc()['game_image'] : '';
                $img_q->close();

                $count = $conn->query("SELECT COUNT(*) FROM game_slot WHERE vendor_code = '{$vendor['vendor_code']}'")->fetch_row()[0];
            ?>
                <a href="?vendor=<?= urlencode($vendor['vendor_code']) ?>" class="glass-card hover-lift text-center">
                    <div class="img-ratio">
                        <img src="<?= htmlspecialchars($vendor_img ?: 'https://placehold.co/330x440/6366f1/ffffff?text=' . substr($vendor['vendor_name'],0,10)) ?>" 
                             alt="<?= htmlspecialchars($vendor['vendor_name']) ?>">
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-lg text-gray-800"><?= htmlspecialchars($vendor['vendor_name']) ?></h3>
                        <p class="text-3xl font-black text-blue-600 mt-2"><?= $count ?>/6</p>
                        <span class="inline-block mt-3 px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">
                            <?= strtoupper($vendor['vendor_code']) ?>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- GAMES LIST -->
    <?php if ($selected_vendor && $games): ?>
        <div class="mb-6">
            <a href="slot-games.php" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Vendors
            </a>
        </div>
        <h2 class="text-2xl font-bold mb-6">
            <?= htmlspecialchars($vendor_display) ?> 
            <span class="text-blue-600">(<?= strtoupper($selected_vendor) ?>)</span>
            <span class="text-gray-500 ml-3">• <?= $game_count ?>/6 Games</span>
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
            <?php while ($game = $games->fetch_assoc()): ?>
                <div class="glass-card hover-lift rounded-2xl overflow-hidden">
                    <div class="img-ratio">
                        <img src="<?= htmlspecialchars($game['game_image'] ?: 'https://placehold.co/330x440?text=No+Image') ?>" alt="">
                    </div>
                    <div class="p-4">
                        <h4 class="font-semibold text-sm truncate"><?= htmlspecialchars($game['game_name_en']) ?></h4>
                        <div class="flex gap-2 mt-4">
                            <button onclick='openEditModal(<?= json_encode($game, JSON_HEX_QUOT|JSON_HEX_APOS) ?>)' 
                                    class="flex-1 py-2 btn-primary rounded-xl text-xs font-semibold">
                                Edit
                            </button>
                            <a href="?vendor=<?= urlencode($selected_vendor) ?>&toggle=<?= $game['id'] ?>" 
                               class="px-3 py-2 <?= $game['status'] ? 'bg-green-500' : 'bg-gray-400' ?> text-white rounded-xl">
                                <i class="fas fa-power-off"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL - FULLY FIXED WITH BLUR BACKGROUND -->
<div id="gameModal" class="modal-overlay">
    <div class="modal-content glass rounded-3xl p-8 shadow-2xl">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800" id="modalTitle">Add New Game</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl p-2 rounded-lg hover:bg-gray-100">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="post">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="game_id">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block font-semibold mb-2">Vendor</label>
                    <select name="vendor_code" id="vendor_code" required class="input-field">
                        <?php foreach ($vendors as $v): ?>
                            <option value="<?= $v['vendor_code'] ?>" <?= $selected_vendor == $v['vendor_code'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($v['vendor_name']) ?> (<?= $v['vendor_code'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-2">Game ID</label>
                    <input type="text" name="game_id" id="game_id_field" required class="input-field">
                </div>
                <div>
                    <label class="block font-semibold mb-2">Game Name</label>
                    <input type="text" name="game_name_en" id="game_name_en" required class="input-field">
                </div>
                <div>
                    <label class="block font-semibold mb-2">Image URL</label>
                    <input type="text" name="game_image" id="game_image" class="input-field">
                </div>
                <div>
                    <label class="block font-semibold mb-2">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="0" class="input-field">
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="status" id="status" checked class="w-5 h-5">
                    <label class="font-semibold">Active</label>
                </div>
            </div>

            <div class="flex gap-4 mt-8">
                <button type="submit" class="flex-1 py-3 btn-primary rounded-xl font-semibold hover-lift">
                    Save Game
                </button>
                <button type="button" onclick="closeModal()" class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 rounded-xl font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Game';
    document.querySelector('#gameModal form').reset();
    document.getElementById('game_id').value = '';
    document.getElementById('status').checked = true;
    document.getElementById('sort_order').value = 0;
    <?php if ($selected_vendor): ?>
        document.getElementById('vendor_code').value = '<?= addslashes($selected_vendor) ?>';
    <?php endif; ?>
    document.getElementById('gameModal').classList.add('active');
}

function openEditModal(game) {
    document.getElementById('modalTitle').textContent = 'Edit Game';
    document.getElementById('game_id').value = game.id;
    document.getElementById('vendor_code').value = game.vendor_code;
    document.getElementById('game_id_field').value = game.game_id;
    document.getElementById('game_name_en').value = game.game_name_en;
    document.getElementById('game_image').value = game.game_image || '';
    document.getElementById('sort_order').value = game.sort_order;
    document.getElementById('status').checked = game.status == 1;
    document.getElementById('gameModal').classList.add('active');
}

function closeModal() {
    document.getElementById('gameModal').classList.remove('active');
}

// Close on background click
document.getElementById('gameModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

</body>
</html>