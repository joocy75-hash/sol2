<?php 
session_start();
include "header.php"; 
include "../conn.php";

// Database category mapping
$categoryMap = [
    'Popular Games' => 'popular_platform',
    'Trending Now' => 'popular_clicks_top', 
    'Slot Games' => 'slot',
    'Live Casino' => 'video',
    'Fishing Games' => 'fish',
    'Instant Games' => 'flash',
    'Card Games' => 'chess'
];

// Category configurations - what fields each category needs
$categoryConfigs = [
    'Popular Games' => ['vendorId', 'gameCode'],
    'Trending Now' => ['vendorId', 'gameCode'],
    'Slot Games' => ['vendorCode'],
    'Live Casino' => ['vendorId', 'gameCode'],
    'Fishing Games' => ['vendorId', 'gameCode'],
    'Instant Games' => ['vendorId', 'gameCode'],
    'Card Games' => ['vendorCode']
];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'save_game') {
            try {
                $category = trim($_POST['category']);
                $index = $_POST['index'];
                $name = trim($_POST['gameName']);
                $image = trim($_POST['image']);
                
                // Generate professional placeholder if no image provided
                if (empty($image)) {
                    $image = "https://placehold.co/330x440/000000/FFFFFF?font=source-sans-pro&text=" . urlencode($name);
                }
                
                $dbCategory = $categoryMap[$category];
                $config = $categoryConfigs[$category];
                
                // Get current games
                $stmt = $conn->prepare("SELECT game_data FROM game_data WHERE category_type = ?");
                $stmt->bind_param("s", $dbCategory);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $games = [];
                if ($row = $result->fetch_assoc()) {
                    $games = json_decode($row['game_data'], true) ?: [];
                }
                $stmt->close();

                // Build game data based on category configuration and original format
                $gameData = [];
                
                // Handle different category formats
                switch($dbCategory) {
                    case 'popular_platform':
                    case 'popular_clicks_top':
                        $gameData = [
                            'vendorId' => trim($_POST['vendorId']),
                            'game_name' => $name,
                            'gameCode' => trim($_POST['gameCode']),
                            'game_type' => 'Instant',
                            'imgUrl' => $image,
                            'winOdds' => 95.0
                        ];
                        break;
                        
                    case 'slot':
                    case 'chess':
                        $gameData = [
                            'slotsTypeID' => (int)trim($_POST['vendorCode']),
                            'slotsName' => $name,
                            'state' => 1,
                            'vendorImg' => $image
                        ];
                        break;
                        
                    case 'video':
                        $gameData = [
                            'slotsTypeID' => (int)trim($_POST['vendorCode']),
                            'slotsName' => $name,
                            'vendorId' => (int)trim($_POST['vendorId']),
                            'gameCode' => trim($_POST['gameCode']),
                            'state' => 1,
                            'vendorImg' => $image
                        ];
                        break;
                        
                    case 'fish':
                        $gameData = [
                            'gameID' => trim($_POST['gameCode']),
                            'gameNameEn' => $name,
                            'img' => $image,
                            'vendorId' => (int)trim($_POST['vendorId']),
                            'imgUrl2' => '',
                            'customGameType' => 0
                        ];
                        break;
                        
                    case 'flash':
                        $gameData = [
                            'game_name' => $name,
                            'gameID' => trim($_POST['gameCode']),
                            'game_type' => 'CasinoTable',
                            'img' => $image,
                            'vendorId' => (int)trim($_POST['vendorId']),
                            'customGameType' => 0
                        ];
                        break;
                        
                    default:
                        $gameData = ['gameNameEn' => $name];
                        if (in_array('vendorCode', $config)) {
                            $gameData['vendorCode'] = trim($_POST['vendorCode']);
                        }
                        if (in_array('vendorId', $config)) {
                            $gameData['vendorId'] = trim($_POST['vendorId']);
                        }
                        if (in_array('gameCode', $config)) {
                            $gameData['gameCode'] = trim($_POST['gameCode']);
                        }
                        $gameData['img'] = $image;
                }

                // Update or add game
                if ($index === 'new') {
                    $games[] = $gameData;
                } else {
                    $games[(int)$index] = $gameData;
                }

                // Save to database
                $jsonData = json_encode($games, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                
                // Check if category exists
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM game_data WHERE category_type = ?");
                $checkStmt->bind_param("s", $dbCategory);
                $checkStmt->execute();
                $checkStmt->bind_result($count);
                $checkStmt->fetch();
                $checkStmt->close();
                
                if ($count > 0) {
                    // Update existing
                    $updateStmt = $conn->prepare("UPDATE game_data SET game_data = ? WHERE category_type = ?");
                    $updateStmt->bind_param("ss", $jsonData, $dbCategory);
                } else {
                    // Insert new
                    $updateStmt = $conn->prepare("INSERT INTO game_data (category_type, game_data) VALUES (?, ?)");
                    $updateStmt->bind_param("ss", $dbCategory, $jsonData);
                }
                
                if ($updateStmt->execute()) {
                    $_SESSION['success'] = "Game saved successfully!";
                } else {
                    throw new Exception("Database update failed: " . $updateStmt->error);
                }
                $updateStmt->close();

                // Redirect without form resubmission
                header("Location: home-games.php");
                exit;
                
            } catch (Exception $e) {
                $_SESSION['error'] = "Error saving game: " . $e->getMessage();
                header("Location: home-games.php");
                exit;
            }
        }

        if ($_POST['action'] === 'delete_game') {
            try {
                $category = $_POST['category'];
                $index = (int)$_POST['index'];
                $dbCategory = $categoryMap[$category];
                
                // Get current games
                $stmt = $conn->prepare("SELECT game_data FROM game_data WHERE category_type = ?");
                $stmt->bind_param("s", $dbCategory);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $games = [];
                if ($row = $result->fetch_assoc()) {
                    $games = json_decode($row['game_data'], true) ?: [];
                }
                $stmt->close();

                // Remove game
                if (isset($games[$index])) {
                    array_splice($games, $index, 1);
                    
                    // Save back
                    $jsonData = json_encode($games, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $updateStmt = $conn->prepare("UPDATE game_data SET game_data = ? WHERE category_type = ?");
                    $updateStmt->bind_param("ss", $jsonData, $dbCategory);
                    
                    if ($updateStmt->execute()) {
                        $_SESSION['success'] = "Game deleted successfully!";
                    } else {
                        throw new Exception("Database update failed");
                    }
                    $updateStmt->close();
                }
                
                // Redirect without form resubmission
                header("Location: home-games.php");
                exit;
                
            } catch (Exception $e) {
                $_SESSION['error'] = "Error deleting game: " . $e->getMessage();
                header("Location: home-games.php");
                exit;
            }
        }
    }
}

// Handle image update when image fails to load
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_game_image') {
    try {
        $category = $_POST['category'];
        $index = (int)$_POST['index'];
        $newImage = $_POST['image'];
        $gameName = $_POST['gameName'];
        
        $dbCategory = $categoryMap[$category];
        
        // Get current games
        $stmt = $conn->prepare("SELECT game_data FROM game_data WHERE category_type = ?");
        $stmt->bind_param("s", $dbCategory);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $games = [];
        if ($row = $result->fetch_assoc()) {
            $games = json_decode($row['game_data'], true) ?: [];
        }
        $stmt->close();

        // Update image in the specific game
        if (isset($games[$index])) {
            // Update image based on category format
            if (isset($games[$index]['imgUrl'])) {
                $games[$index]['imgUrl'] = $newImage;
            } elseif (isset($games[$index]['img'])) {
                $games[$index]['img'] = $newImage;
            } elseif (isset($games[$index]['vendorImg'])) {
                $games[$index]['vendorImg'] = $newImage;
            } elseif (isset($games[$index]['categoryImg'])) {
                $games[$index]['categoryImg'] = $newImage;
            }
            
            // Save back to database
            $jsonData = json_encode($games, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $updateStmt = $conn->prepare("UPDATE game_data SET game_data = ? WHERE category_type = ?");
            $updateStmt->bind_param("ss", $jsonData, $dbCategory);
            $updateStmt->execute();
            $updateStmt->close();
            
            echo "SUCCESS";
        } else {
            echo "ERROR: Game not found";
        }
        
        exit;
        
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage();
        exit;
    }
}

// Function to get home games by category with proper data mapping
function getHomeGames($conn, $categoryMap) {
    $gamesData = [];
    
    foreach ($categoryMap as $displayName => $dbCat) {
        $stmt = $conn->prepare("SELECT game_data FROM game_data WHERE category_type = ?");
        $stmt->bind_param("s", $dbCat);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $games = [];
        if ($row = $result->fetch_assoc()) {
            $categoryGames = json_decode($row['game_data'], true) ?: [];
            
            foreach ($categoryGames as $game) {
                // Handle different data formats from SQL with proper mapping
                $gameName = '';
                $gameImage = '';
                $vendorCode = '';
                $vendorId = '';
                $gameCode = '';
                $slotsTypeID = '';
                $slotsName = '';
                $displayId = '';

                // Extract data based on available fields in different formats
                if (isset($game['game_name'])) {
                    $gameName = $game['game_name'];
                    $gameImage = $game['imgUrl'] ?? $game['img'] ?? '';
                    $vendorId = $game['vendorId'] ?? '';
                    $gameCode = $game['gameCode'] ?? $game['gameID'] ?? '';
                    $displayId = ($vendorId ? 'Vendor ID: ' . $vendorId : '') . ($gameCode ? ($vendorId ? ' | ' : '') . 'Game Code: ' . $gameCode : '');
                    
                } elseif (isset($game['gameNameEn'])) {
                    $gameName = $game['gameNameEn'];
                    $gameImage = $game['imgUrl'] ?? $game['img'] ?? '';
                    $vendorId = $game['vendorId'] ?? '';
                    $gameCode = $game['gameCode'] ?? $game['gameID'] ?? '';
                    $displayId = ($vendorId ? 'Vendor ID: ' . $vendorId : '') . ($gameCode ? ($vendorId ? ' | ' : '') . 'Game Code: ' . $gameCode : '');
                    
                } elseif (isset($game['slotsName'])) {
                    $gameName = $game['slotsName'];
                    $gameImage = $game['vendorImg'] ?? '';
                    $slotsTypeID = $game['slotsTypeID'] ?? '';
                    $vendorId = $game['vendorId'] ?? $game['slotsTypeID'] ?? '';
                    $gameCode = $game['gameCode'] ?? '';
                    $displayId = ($slotsTypeID ? 'Type ID: ' . $slotsTypeID : '') . ($gameCode ? ($slotsTypeID ? ' | ' : '') . 'Game Code: ' . $gameCode : '');
                    
                } elseif (isset($game['categoryName'])) {
                    $gameName = $game['categoryName'];
                    $gameImage = $game['categoryImg'] ?? '';
                    $vendorCode = $game['categoryCode'] ?? '';
                    $displayId = $vendorCode ? 'Category: ' . $vendorCode : '';
                    
                } elseif (isset($game['userName'])) {
                    $gameName = $game['gameName'] . ' - ' . $game['userName'];
                    $gameImage = $game['imgUrl'] ?? '';
                    $displayId = 'Bonus: ' . ($game['bonusAmount'] ?? '0');
                    
                } else {
                    $gameName = 'Unnamed Game';
                    $gameImage = '';
                    if (isset($game['gameID'])) {
                        $gameName = 'Game ' . $game['gameID'];
                        $gameImage = $game['img'] ?? '';
                        $vendorId = $game['vendorId'] ?? '';
                        $gameCode = $game['gameID'] ?? '';
                        $displayId = ($vendorId ? 'Vendor ID: ' . $vendorId : '') . ($gameCode ? ($vendorId ? ' | ' : '') . 'Game Code: ' . $gameCode : '');
                    }
                }

                // Clean up display ID
                $displayId = trim($displayId, ' |');
                
                $games[] = [
                    'name' => $gameName ?: 'Unnamed Game',
                    'vendorCode' => $vendorCode,
                    'vendorId' => $vendorId,
                    'gameCode' => $gameCode,
                    'image' => $gameImage,
                    'displayId' => $displayId,
                    'slotsTypeID' => $slotsTypeID,
                    'slotsName' => $slotsName,
                    'rawData' => $game
                ];
            }
        }
        $stmt->close();
        $gamesData[$displayName] = $games;
    }
    
    return $gamesData;
}

$homeGames = getHomeGames($conn, $categoryMap);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Games Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
<style>
:root {
    --primary-blue: #3b82f6;
    --primary-blue-dark: #2563eb;
    --success-green: #10b981;
    --danger-red: #ef4444;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: #f8fafc;
    min-height: 100vh;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.5;
}

.glass-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid #f1f5f9;
}

.glass-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transform: translateY(-1px);
}

.btn-primary {
    background: var(--primary-blue);
    border: none;
    color: white;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
}

.btn-primary:hover {
    background: var(--primary-blue-dark);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    backdrop-filter: blur(4px);
}

.modal-overlay.active {
    display: flex;
}

.modal-content {
    animation: modalSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    max-height: 90vh;
    overflow-y: auto;
    transform-origin: center;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.input-field {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 0.875rem;
    background: white;
    width: 100%;
}

.input-field:focus {
    border-color: var(--primary-blue);
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

.success-message {
    background: var(--success-green);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.error-message {
    background: var(--danger-red);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

/* Fixed Image Ratio Container */
.image-container {
    position: relative;
    width: 100%;
    height: 0;
    padding-bottom: 133.33%;
    overflow: hidden;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 8px 8px 0 0;
}

.image-container img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.image-container:hover img {
    transform: scale(1.03);
}

.badge {
    display: inline-block;
    padding: 0.35rem 0.65rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
    background: rgba(59, 130, 246, 0.08);
    color: var(--primary-blue);
    border: 1px solid rgba(59, 130, 246, 0.15);
}

.loading-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.field-group {
    display: none;
}

.field-group.active {
    display: block;
}

.game-card {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.game-card:hover {
    transform: translateY(-3px);
}

/* Grid improvements */
.grid {
    display: grid;
    gap: 1rem;
}

.grid-cols-2 {
    grid-template-columns: repeat(2, 1fr);
}

.grid-cols-3 {
    grid-template-columns: repeat(3, 1fr);
}

@media (min-width: 768px) {
    .md\:grid-cols-3 {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .md\:grid-cols-4 {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (min-width: 1024px) {
    .lg\:grid-cols-6 {
        grid-template-columns: repeat(6, 1fr);
    }
}

/* Button improvements */
button {
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    outline: none;
}

button:active {
    transform: scale(0.98);
}

.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Focus styles for accessibility */
button:focus-visible,
input:focus-visible,
select:focus-visible {
    outline: 2px solid var(--primary-blue);
    outline-offset: 2px;
}

/* Loading states */
.loading {
    opacity: 0.7;
    pointer-events: none;
}
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message">
            <i class="fas fa-check-circle text-lg"></i>
            <span><?= $_SESSION['success'] ?></span>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle text-lg"></i>
            <span><?= $_SESSION['error'] ?></span>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="glass-card p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-1">Home Games Management</h1>
                <p class="text-gray-600 text-sm">Manage games displayed on the home page</p>
            </div>
            <button onclick="openAddModal()" class="px-5 py-2.5 btn-primary rounded-lg font-semibold flex items-center gap-2 justify-center transition-all duration-300">
                <i class="fas fa-plus"></i>
                Add New Game
            </button>
        </div>
    </div>

    <!-- Games by Category -->
    <?php foreach($homeGames as $categoryName => $games): ?>
    <div class="mb-8">
        <div class="glass-card p-4 mb-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                        <i class="fas fa-folder text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($categoryName) ?></h2>
                        <p class="text-gray-600 text-sm"><?= count($games) ?> games</p>
                    </div>
                </div>
                <button onclick="openAddModal('<?= htmlspecialchars($categoryName) ?>')" class="px-4 py-2 btn-primary rounded-lg font-semibold flex items-center gap-2 justify-center text-sm transition-all duration-300">
                    <i class="fas fa-plus"></i>
                    Add Game
                </button>
            </div>
        </div>

        <?php if (empty($games)): ?>
            <div class="glass-card p-8 text-center">
                <i class="fas fa-gamepad text-5xl text-gray-400 mb-3"></i>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">No Games Yet</h3>
                <p class="text-gray-500 text-sm mb-4">Start by adding games to this category</p>
                <button onclick="openAddModal('<?= htmlspecialchars($categoryName) ?>')" class="px-5 py-2.5 btn-primary rounded-lg font-semibold inline-flex items-center gap-2 transition-all duration-300">
                    <i class="fas fa-plus"></i>Add First Game
                </button>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <?php foreach($games as $index => $game): ?>
                <div class="glass-card rounded-lg overflow-hidden game-card group">
                    <div class="image-container" id="image-container-<?= $categoryName . '-' . $index ?>">
                        <img 
                            src="<?= htmlspecialchars($game['image']) ?>" 
                            alt="<?= htmlspecialchars($game['name']) ?>"
                            onerror="handleImageErrorAndUpdate(this, '<?= htmlspecialchars($game['name']) ?>', '<?= $categoryName ?>', <?= $index ?>)"
                            loading="lazy"
                            class="group-hover:scale-105"
                        >
                    </div>
                    <div class="p-3">
                        <h3 class="font-semibold text-gray-800 text-xs mb-2 truncate" title="<?= htmlspecialchars($game['name']) ?>">
                            <?= htmlspecialchars($game['name']) ?>
                        </h3>
                        <?php if ($game['displayId']): ?>
                            <div class="mb-2">
                                <span class="badge text-xs"><?= htmlspecialchars($game['displayId']) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex gap-2">
                            <button onclick='openEditModal(<?= json_encode([
                                "index" => $index,
                                "category" => $categoryName,
                                "name" => $game["name"],
                                "vendorCode" => $game["vendorCode"],
                                "vendorId" => $game["vendorId"], 
                                "gameCode" => $game["gameCode"],
                                "image" => $game["image"],
                                "slotsTypeID" => $game["slotsTypeID"],
                                "slotsName" => $game["slotsName"],
                                "rawData" => $game["rawData"]
                            ]) ?>)' 
                                    class="flex-1 py-2 btn-primary rounded-lg text-xs font-semibold transition-all duration-300 hover:shadow-lg">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteGame(<?= $index ?>, '<?= $categoryName ?>')" 
                                    class="px-3 py-2 bg-gradient-to-br from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg text-xs font-semibold transition-all duration-300 hover:shadow-lg">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add/Edit Modal -->
<div id="gameModal" class="modal-overlay">
    <div class="modal-content w-full max-w-md">
        <div class="glass-card p-6 m-4">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800" id="modalTitle">Add New Game</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100 transition-all duration-200">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form method="post" id="gameForm">
                <input type="hidden" name="action" value="save_game">
                <input type="hidden" name="index" id="formIndex">
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                    <select name="category" id="categorySelect" class="w-full input-field" onchange="updateFormFields()" required>
                        <option value="">Select Category</option>
                        <?php foreach(array_keys($categoryMap) as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Game Name</label>
                    <input type="text" name="gameName" id="gameName" required class="w-full input-field" placeholder="Enter game name">
                </div>

                <div class="field-group mb-4" id="vendorCodeField">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Vendor Code / Slots Type ID</label>
                    <input type="text" name="vendorCode" id="vendorCode" class="w-full input-field" placeholder="Enter vendor code or slots type ID">
                </div>

                <div class="field-group mb-4" id="vendorIdField">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Vendor ID</label>
                    <input type="text" name="vendorId" id="vendorId" class="w-full input-field" placeholder="Enter vendor ID">
                </div>

                <div class="field-group mb-4" id="gameCodeField">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Game Code / Game ID</label>
                    <input type="text" name="gameCode" id="gameCode" class="w-full input-field" placeholder="Enter game code or game ID">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Image URL 
                        <span class="text-gray-500 font-normal">(330x440 ratio recommended)</span>
                    </label>
                    <input type="text" name="image" id="gameImage" class="w-full input-field" 
                           placeholder="Leave empty for professional placeholder">
                    <p class="text-xs text-gray-500 mt-1">Professional black & white placeholder will be generated automatically</p>
                </div>

                <div class="flex gap-3">
                    <button type="submit" id="submitBtn" class="flex-1 py-3 btn-primary rounded-lg font-semibold transition-all duration-300 hover:shadow-lg">
                        <i class="fas fa-save mr-2"></i>Save Game
                    </button>
                    <button type="button" onclick="closeModal()" class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition-all duration-300">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const categoryConfigs = <?= json_encode($categoryConfigs) ?>;

// Handle image loading errors and update database with placeholder
function handleImageErrorAndUpdate(img, gameName, category, index) {
    // Generate professional placeholder
    const encodedName = encodeURIComponent(gameName);
    const placeholderUrl = `https://placehold.co/330x440/000000/FFFFFF?font=source-sans-pro&text=${encodedName}`;
    
    // Update the image src immediately
    img.src = placeholderUrl;
    img.alt = gameName;
    img.onerror = null; // Prevent infinite loop
    
    // Update database with placeholder URL
    updateGameImageInDatabase(category, index, placeholderUrl, gameName);
}

// Function to update game image in database when image fails to load
function updateGameImageInDatabase(category, index, placeholderUrl, gameName) {
    const formData = new FormData();
    formData.append('action', 'update_game_image');
    formData.append('category', category);
    formData.append('index', index);
    formData.append('image', placeholderUrl);
    formData.append('gameName', gameName);
    
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log('Image updated in database:', data);
    })
    .catch(error => {
        console.error('Error updating image:', error);
    });
}

function updateFormFields() {
    const category = document.getElementById('categorySelect').value;
    const config = categoryConfigs[category] || [];
    
    // Hide all fields first
    document.getElementById('vendorCodeField').classList.remove('active');
    document.getElementById('vendorIdField').classList.remove('active');
    document.getElementById('gameCodeField').classList.remove('active');
    
    // Reset required attributes
    document.getElementById('vendorCode').required = false;
    document.getElementById('vendorId').required = false;
    document.getElementById('gameCode').required = false;
    
    // Show and require fields based on category config
    if (config.includes('vendorCode')) {
        document.getElementById('vendorCodeField').classList.add('active');
        document.getElementById('vendorCode').required = true;
    }
    if (config.includes('vendorId')) {
        document.getElementById('vendorIdField').classList.add('active');
        document.getElementById('vendorId').required = true;
    }
    if (config.includes('gameCode')) {
        document.getElementById('gameCodeField').classList.add('active');
        document.getElementById('gameCode').required = true;
    }
}

function openAddModal(category = '') {
    document.getElementById('modalTitle').textContent = 'Add New Game';
    document.getElementById('formIndex').value = 'new';
    document.getElementById('gameForm').reset();
    
    if (category) {
        document.getElementById('categorySelect').value = category;
        updateFormFields();
    }
    
    document.getElementById('gameModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function openEditModal(data) {
    document.getElementById('modalTitle').textContent = 'Edit Game';
    document.getElementById('formIndex').value = data.index;
    document.getElementById('categorySelect').value = data.category;
    document.getElementById('gameName').value = data.name;
    document.getElementById('vendorCode').value = data.vendorCode || data.slotsTypeID || '';
    document.getElementById('vendorId').value = data.vendorId || '';
    document.getElementById('gameCode').value = data.gameCode || '';
    document.getElementById('gameImage').value = data.image || '';
    
    updateFormFields();
    document.getElementById('gameModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('gameModal').classList.remove('active');
    document.body.style.overflow = 'auto';
}

function deleteGame(index, category) {
    if (confirm('Are you sure you want to delete this game?')) {
        // Create and submit form directly - immediate page refresh
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_game">
            <input type="hidden" name="index" value="${index}">
            <input type="hidden" name="category" value="${category}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Handle form submission with immediate page refresh
document.getElementById('gameForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner loading-spinner mr-2"></i>Saving...';
    submitBtn.disabled = true;
    submitBtn.classList.add('loading');
    
    // Submit the form normally for immediate page refresh
    this.submit();
});

// Close modal on outside click
document.getElementById('gameModal').addEventListener('click', function(e) {
    if (e.target.id === 'gameModal') closeModal();
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

// Initialize tooltips for truncated game names
document.addEventListener('DOMContentLoaded', function() {
    const gameNames = document.querySelectorAll('.truncate');
    gameNames.forEach(name => {
        name.addEventListener('mouseenter', function() {
            if (this.scrollWidth > this.clientWidth) {
                this.title = this.textContent;
            }
        });
    });
});

// Prevent form resubmission on page refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// Clear any form data that might cause resubmission
window.onpageshow = function(event) {
    if (event.persisted) {
        window.location.reload();
    }
};
</script>
</body>
</html>