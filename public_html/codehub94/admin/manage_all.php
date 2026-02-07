<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("Location: index.php?msg=unauthorized");
    exit;
}

$basePath = dirname(__FILE__);
$chub94Path = $basePath . '/Chub94/';
$dataBasePath = $basePath . '/../api/webapi/data/';

// Define the PHP files for each section
$sectionFiles = [
    'all' => $chub94Path . 'getallgames.php'
];

// Set current tab to 'all' only
$currentTab = 'all';
$selectedFile = $_GET['file'] ?? '';
$currentData = [];
$sectionError = '';

// Load data based on section
try {
    if (file_exists($sectionFiles[$currentTab])) {
        // Set paths for the section file
        $GLOBALS['dataBasePath'] = $dataBasePath;
        $GLOBALS['currentTab'] = $currentTab;
        $GLOBALS['selectedFile'] = $selectedFile;
        
        ob_start();
        include $sectionFiles[$currentTab];
        $output = ob_get_clean();
        
        if (isset($gameData)) {
            $currentData = $gameData;
        } elseif (isset($data)) {
            $currentData = $data;
        } else {
            $jsonData = json_decode($output, true);
            if ($jsonData) {
                $currentData = $jsonData;
            } else {
                $sectionError = "No valid game data found";
            }
        }
    } else {
        $sectionError = "Configuration file not found: " . $sectionFiles[$currentTab];
    }
} catch (Exception $e) {
    $sectionError = "Error loading data: " . $e->getMessage();
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset_data'])) {
        try {
            $tab = 'all';
            $filename = $_POST['filename'] ?? '';
            if (file_exists($sectionFiles[$tab])) {
                // Include the file to process reset
                $GLOBALS['dataBasePath'] = $dataBasePath;
                $GLOBALS['selectedFile'] = $filename;
                include $sectionFiles[$tab];
                
                // Redirect after reset
                header("Location: ?file=" . urlencode($filename) . "&reset=1");
                exit;
            }
        } catch (Exception $e) {
            $sectionError = "Reset failed: " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] ?? '' === 'save_game') {
        try {
            $tab = 'all';
            $filename = $_POST['filename'] ?? '';
            if (file_exists($sectionFiles[$tab])) {
                // Include the file to process save
                $GLOBALS['dataBasePath'] = $dataBasePath;
                $GLOBALS['selectedFile'] = $filename;
                include $sectionFiles[$tab];
                
                // Redirect after save
                header("Location: ?file=" . urlencode($filename) . "&success=1");
                exit;
            }
        } catch (Exception $e) {
            $sectionError = "Save failed: " . $e->getMessage();
        }
    }
}

// Check if we need to create default JSON files
if (empty($selectedFile)) {
    $allFiles = glob($dataBasePath . '*.json');
    if (empty($allFiles)) {
        // Create a default games.json file
        $defaultData = [
            "gameCustomTypeLists" => [],
            "gameLists" => [
                [
                    "gameNameEn" => "Sample Game 1",
                    "gameID" => "sample_game_1",
                    "img" => "https://placehold.co/330x440/eff6ff/1e40af?text=Sample+Game+1",
                    "vendorId" => 1,
                    "vendorCode" => "",
                    "imgUrl2" => "",
                    "customGameType" => 0
                ],
                [
                    "gameNameEn" => "Sample Game 2", 
                    "gameID" => "sample_game_2",
                    "img" => "https://placehold.co/330x440/eff6ff/1e40af?text=Sample+Game+2",
                    "vendorId" => 2,
                    "vendorCode" => "VENDOR2",
                    "imgUrl2" => "",
                    "customGameType" => 0
                ]
            ]
        ];
        
        $defaultFilePath = $dataBasePath . 'games.json';
        file_put_contents($defaultFilePath, json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

// Process and validate game data with image loading optimization
function processGameData($games, $selectedFile) {
    $processedGames = [];
    
    foreach ($games as $i => $game) {
        $img = $game['img'] ?? $game['image'] ?? $game['imgUrl'] ?? $game['imgURL'] ?? $game['icon'] ?? $game['thumbnail'] ?? $game['vendorImg'] ?? $game['categoryImg'] ?? '';
        $gameName = $game['gameNameEn'] ?? $game['name'] ?? $game['title'] ?? $game['gameName'] ?? $game['gameTitle'] ?? $game['game_name'] ?? $game['slotsName'] ?? $game['categoryName'] ?? 'Game ' . ($i + 1);
        
        // Handle vendorCode/vendorId logic - only show one that exists
        $vendorCode = $game['vendorCode'] ?? '';
        $vendorId = $game['vendorId'] ?? $game['vendorID'] ?? '';
        
        // Remove empty imgUrl2
        if (isset($game['imgUrl2']) && empty($game['imgUrl2'])) {
            unset($game['imgUrl2']);
        }
        
        // Check if image loads successfully
        $imageLoaded = false;
        $finalImg = $img;
        
        if (!empty($img) && filter_var($img, FILTER_VALIDATE_URL)) {
            // We'll check image loading in JavaScript for better performance
            $finalImg = $img;
        } else {
            // Use placeholder with game name
            $finalImg = "https://placehold.co/330x440/000000/ffffff?text=" . urlencode($gameName);
            $imageLoaded = false;
        }
        
        $processedGame = $game;
        $processedGame['processed_img'] = $finalImg;
        $processedGame['image_loaded'] = $imageLoaded;
        $processedGame['game_name_display'] = $gameName;
        $processedGame['vendor_display'] = !empty($vendorCode) ? $vendorCode : (!empty($vendorId) ? $vendorId : 'Unknown');
        
        $processedGames[] = $processedGame;
    }
    
    return $processedGames;
}

// Process current data if available
if (!empty($currentData)) {
    $games = [];
    if (isset($currentData['gameLists']) && is_array($currentData['gameLists'])) {
        $games = $currentData['gameLists'];
    } elseif (is_array($currentData)) {
        $games = $currentData;
    }
    
    if (!empty($games)) {
        $processedGames = processGameData($games, $selectedFile);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Games Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2563eb;
            --primary-blue-dark: #1d4ed8;
            --primary-blue-light: #3b82f6;
            --success-green: #10b981;
            --warning-orange: #f59e0b;
            --error-red: #ef4444;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
            min-height: 100vh;
        }
        .glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 
                0 4px 6px -1px rgba(0, 0, 0, 0.1),
                0 2px 4px -1px rgba(0, 0, 0, 0.06),
                0 0 0 1px rgba(255, 255, 255, 0.1);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 
                0 4px 6px -1px rgba(0, 0, 0, 0.05),
                0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        .glass-modal {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.25),
                0 0 0 1px rgba(255, 255, 255, 0.1);
        }
        .hover-lift { 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        .hover-lift:hover { 
            transform: translateY(-4px); 
            box-shadow: 
                0 20px 25px -5px rgba(0, 0, 0, 0.1),
                0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .img-ratio { 
            position: relative; 
            width: 100%; 
            padding-top: 133.33%; 
            overflow: hidden; 
            background: var(--gray-100);
            border-radius: 12px;
        }
        .img-ratio img { 
            position: absolute; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            transition: transform 0.3s ease;
        }
        .game-card:hover .img-ratio img {
            transform: scale(1.05);
        }
        .preview-img {
            max-height: 120px;
            object-fit: contain;
            border-radius: 8px;
        }
        #toast { 
            position: fixed; 
            top: 24px; 
            right: 24px; 
            z-index: 9999; 
            opacity: 0; 
            transform: translateY(-20px) scale(0.95); 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        #toast.show { 
            opacity: 1; 
            transform: translateY(0) scale(1); 
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .modal-content {
            transform: scale(0.9) translateY(20px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .modal-overlay.active .modal-content {
            transform: scale(1) translateY(0);
            opacity: 1;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
            color: white;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-blue-light) 0%, var(--primary-blue) 100%);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
            transform: translateY(-1px);
        }
        .error-section {
            background: rgba(254, 226, 226, 0.9);
            border: 1px solid rgba(248, 113, 113, 0.3);
            color: var(--error-red);
        }
        
        /* Custom Dropdown */
        .custom-dropdown {
            position: relative;
            width: 100%;
        }
        .dropdown-toggle {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            justify-content: between;
            align-items: center;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .dropdown-toggle:hover {
            border-color: var(--primary-blue);
        }
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
            z-index: 40;
            max-height: 240px;
            overflow-y: auto;
            display: none;
            margin-top: 4px;
        }
        .dropdown-menu.show {
            display: block;
            animation: dropdownSlide 0.2s ease-out;
        }
        @keyframes dropdownSlide {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dropdown-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid var(--gray-100);
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .dropdown-item:hover {
            background: var(--gray-50);
        }
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        /* Success Messages */
        .success-message {
            background: linear-gradient(135deg, var(--success-green) 0%, #059669 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Input Styles */
        .input-field {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
        }
        .input-field:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            background: white;
        }

        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        .spinner {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Read-only fields */
        .readonly-field {
            background: rgba(243, 244, 246, 0.9);
            border: 1px solid var(--gray-300);
            color: var(--gray-600);
            cursor: not-allowed;
        }

        /* Image loading states */
        .image-loading {
            opacity: 0.7;
            filter: blur(2px);
        }
        .image-loaded {
            opacity: 1;
            filter: blur(0);
            transition: opacity 0.3s ease, filter 0.3s ease;
        }
        .image-error {
            background: linear-gradient(45deg, #f3f4f6, #e5e7eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 12px;
        }
    </style>
</head>
<body class="text-gray-800 min-h-screen">

<!-- Include External Header -->
<?php include "Chub94/header.php"; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Success Messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            Game updated successfully!
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['reset'])): ?>
        <div class="success-message" style="background: linear-gradient(135deg, var(--warning-orange) 0%, #d97706 100%);">
            <i class="fas fa-check-circle"></i>
            File reset successfully! Data copied from reset folder.
        </div>
    <?php endif; ?>

    <!-- Section-specific Controls -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <!-- File Selection for All Games -->
            <div class="custom-dropdown w-full sm:w-64">
                <div class="dropdown-toggle" onclick="toggleDropdown()">
                    <span id="dropdown-text"><?= $selectedFile ?: 'Select JSON File' ?></span>
                    <i class="fas fa-chevron-down ml-2 text-gray-400"></i>
                </div>
                <div class="dropdown-menu" id="dropdown-menu">
                    <?php
                    $allFiles = glob($dataBasePath . '*.json');
                    foreach($allFiles as $file): 
                        $name = basename($file);
                    ?>
                        <div class="dropdown-item" onclick="selectFile('<?= $name ?>')">
                            <?= $name ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Reset Button - Only show if file is selected -->
            <?php if ($selectedFile): ?>
            <button id="resetBtn" onclick="openResetModal()" class="w-full sm:w-auto px-5 py-3 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-semibold flex items-center justify-center gap-2 hover-lift transition-all duration-300 text-sm shadow-lg shadow-rose-500/25">
                <i class="fas fa-rotate text-sm"></i>
                Reset File
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Games Count -->
        <?php if ($selectedFile && !empty($currentData)): ?>
            <div class="text-sm text-gray-600 bg-white px-4 py-2 rounded-xl border border-gray-200">
                <i class="fas fa-gamepad mr-2 text-blue-500"></i>
                <span class="font-semibold">
                    <?php
                    $games = [];
                    if (isset($currentData['gameLists']) && is_array($currentData['gameLists'])) {
                        $games = $currentData['gameLists'];
                    } elseif (is_array($currentData)) {
                        $games = $currentData;
                    }
                    echo count($games) . ' Games';
                    ?>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Toast -->
    <div id="toast" class="hidden"></div>

    <!-- Section Error Display -->
    <?php if (!empty($sectionError)): ?>
        <div class="error-section rounded-xl p-6 mb-6 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
            </div>
            <h3 class="text-lg font-semibold text-red-700 mb-2">Section Error</h3>
            <p class="text-red-600 text-sm"><?= htmlspecialchars($sectionError) ?></p>
        </div>
    <?php endif; ?>

    <!-- All Games Section -->
    <?php if (empty($selectedFile) && empty($sectionError)): ?>
        <div class="text-center py-20">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-blue-100 mb-6">
                <i class="fas fa-file-code text-3xl text-blue-500"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-700 mb-3">Select a JSON File</h3>
            <p class="text-gray-500 text-sm">Choose a file from the dropdown to manage games</p>
        </div>
    <?php elseif (empty($currentData) && !empty($sectionError)): ?>
        <div class="text-center py-20">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-amber-100 mb-6">
                <i class="fas fa-database text-3xl text-amber-500"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-700 mb-3">No Data Available</h3>
            <p class="text-gray-500 text-sm">Unable to load games data from database</p>
        </div>
    <?php else: ?>
        <?php
        $games = [];
        if (isset($currentData['gameLists']) && is_array($currentData['gameLists'])) {
            $games = $currentData['gameLists'];
        } elseif (is_array($currentData)) {
            $games = $currentData;
        }
        
        if (empty($games)): ?>
            <div class="text-center py-20">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-blue-100 mb-6">
                    <i class="fas fa-gamepad text-3xl text-blue-500"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-700 mb-3">No Games Found</h3>
                <p class="text-gray-500 text-sm">No games available in this file</p>
                <?php if ($selectedFile): ?>
                <button onclick="openResetModal()" class="mt-4 px-6 py-3 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-semibold flex items-center justify-center gap-2 hover-lift transition-all duration-300">
                    <i class="fas fa-rotate text-sm"></i>
                    Reset File to Load Default Games
                </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        All Games
                    </h2>
                    <p class="text-gray-500 text-sm mt-1">
                        Manage all your casino games in one place
                    </p>
                </div>
                <?php if ($selectedFile): ?>
                    <div class="text-xs text-gray-500 bg-gray-100 px-3 py-2 rounded-xl font-medium">
                        <i class="fas fa-file-code mr-2"></i>
                        <?= $selectedFile ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4" id="games-grid">
                <?php 
                if (is_array($games) && isset($games[0])) {
                    foreach($games as $i => $game): 
                        $img = $game['img'] ?? $game['image'] ?? $game['imgUrl'] ?? $game['imgURL'] ?? $game['icon'] ?? $game['thumbnail'] ?? $game['vendorImg'] ?? $game['categoryImg'] ?? '';
                        $gameName = $game['gameNameEn'] ?? $game['name'] ?? $game['title'] ?? $game['gameName'] ?? $game['gameTitle'] ?? $game['game_name'] ?? $game['slotsName'] ?? $game['categoryName'] ?? 'Game ' . ($i + 1);
                        $gameID = $game['gameID'] ?? $game['gameCode'] ?? $game['id'] ?? $game['code'] ?? $game['vendorId'] ?? $game['gameId'] ?? $game['slotsTypeID'] ?? $game['categoryCode'] ?? 'N/A';
                        
                        // Handle vendorCode/vendorId - show only one that exists
                        $vendorCode = $game['vendorCode'] ?? '';
                        $vendorId = $game['vendorId'] ?? $game['vendorID'] ?? '';
                        $vendorDisplay = !empty($vendorCode) ? $vendorCode : (!empty($vendorId) ? $vendorId : 'Unknown');
                        
                        $customGameType = $game['customGameType'] ?? 0;
                        $imgUrl2 = $game['imgUrl2'] ?? '';
                        
                        // Use placeholder initially, will be updated by JavaScript
                        $placeholder = "https://placehold.co/330x440/000000/ffffff?text=" . urlencode($gameName);
                        $finalImg = (!empty($img) && filter_var($img, FILTER_VALIDATE_URL)) ? $img : $placeholder;
                ?>
                    <div class="glass-card rounded-2xl overflow-hidden hover-lift game-card relative" data-game-index="<?= $i ?>">
                        <div class="img-ratio">
                            <img src="<?= $finalImg ?>" 
                                 alt="<?= htmlspecialchars($gameName) ?>" 
                                 class="rounded-t-2xl image-loading"
                                 data-src="<?= $finalImg ?>"
                                 data-placeholder="<?= $placeholder ?>"
                                 data-game-name="<?= htmlspecialchars($gameName) ?>"
                                 onload="this.classList.add('image-loaded')"
                                 onerror="handleImageError(this)">
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-800 text-sm mb-2 truncate" title="<?= htmlspecialchars($gameName) ?>">
                                <?= htmlspecialchars($gameName) ?>
                            </h3>
                            <div class="text-xs text-gray-500 mb-2 flex items-center gap-1">
                                <i class="fas fa-tag"></i>
                                <span><?= $vendorDisplay ?></span>
                            </div>
                            <div class="flex gap-2">
                                <button onclick='openEdit(<?= $i ?>, <?= json_encode($game, JSON_HEX_QUOT|JSON_HEX_APOS) ?>, "all")' 
                                        class="w-full py-2.5 btn-primary rounded-xl font-semibold text-xs flex items-center justify-center gap-1 hover-lift">
                                    <i class="fas fa-edit text-xs"></i>
                                    Edit
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; 
                } ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-overlay">
    <div class="modal-content w-full max-w-md mx-4">
        <div class="glass-modal rounded-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3" id="modalTitle">
                    <i class="fas fa-edit text-blue-500 text-lg"></i>
                    <span id="modalTitleText">Edit Game</span>
                </h2>
                <button onclick="closeModal()" class="text-lg text-gray-500 hover:text-gray-700 transition-colors p-2 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="post" id="editForm">
                <input type="hidden" name="action" value="save_game">
                <input type="hidden" name="filename" value="<?= $selectedFile ?>">
                <input type="hidden" name="index" id="editIndex">
                <input type="hidden" name="tab" id="editTab" value="all">
                
                <!-- Only include imgUrl2 if it has a value -->
                <input type="hidden" name="imgUrl2" id="editImgUrl2">

                <div class="space-y-4">
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700 text-sm">Game Name</label>
                        <input type="text" name="gameNameEn" id="editName" required 
                               class="w-full input-field" placeholder="Enter game name">
                    </div>

                    <div>
                        <label class="block mb-2 font-semibold text-gray-700 text-sm">Game ID</label>
                        <input type="text" name="gameID" id="editGameCode" required 
                               class="w-full input-field" placeholder="Game ID">
                    </div>

                    <div>
                        <label class="block mb-2 font-semibold text-gray-700 text-sm">Game Image URL</label>
                        <input type="text" name="img" id="editImg" placeholder="https://example.com/image.jpg" 
                               oninput="previewImg()" 
                               class="w-full input-field">
                        <p class="text-xs text-gray-500 mt-1">Leave empty for auto-generated placeholder</p>
                    </div>

                    <!-- Vendor info - show only one that exists -->
                    <div id="vendorInfoContainer">
                        <!-- Will be populated dynamically -->
                    </div>

                    <div class="bg-gray-50 p-4 rounded-xl text-center border border-gray-200">
                        <p class="font-semibold mb-3 text-blue-600 flex items-center justify-center gap-2 text-sm">
                            <i class="fas fa-image"></i>
                            Image Preview
                        </p>
                        <div class="flex justify-center">
                            <img id="preview" src="" class="preview-img rounded-xl border-2 border-gray-200">
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" id="saveBtn" class="flex-1 py-3 btn-primary rounded-xl font-semibold text-sm flex items-center justify-center gap-2 hover-lift">
                            <i class="fas fa-save text-sm"></i>
                            Save Changes
                        </button>
                        <button type="button" onclick="closeModal()" class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-semibold text-sm hover-lift">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Modal -->
<div id="resetModal" class="modal-overlay">
    <div class="modal-content w-full max-w-md mx-4">
        <div class="glass-modal rounded-2xl p-6 text-center">
            <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mb-4 mx-auto">
                <i class="fas fa-exclamation-triangle text-2xl text-rose-500"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-3">Reset File</h3>
            <p class="text-gray-600 mb-6">Are you sure you want to reset <strong><?= $selectedFile ?></strong> to default data? This will copy data from reset folder and cannot be undone.</p>
            
            <div class="flex gap-3">
                <form method="post" id="resetForm" class="flex-1">
                    <input type="hidden" name="tab" value="all">
                    <input type="hidden" name="reset_data" value="1">
                    <input type="hidden" name="filename" value="<?= $selectedFile ?>">
                    <button type="submit" class="w-full py-3 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-semibold transition-all duration-300 hover-lift">
                        Yes, Reset File
                    </button>
                </form>
                <button onclick="closeResetModal()" class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-semibold transition-all duration-300 hover-lift">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Image loading optimization
class ImageLoader {
    constructor() {
        this.images = [];
        this.loadedCount = 0;
        this.totalCount = 0;
        this.batchSize = 5; // Load 5 images at a time
        this.currentBatch = 0;
    }

    init() {
        this.images = Array.from(document.querySelectorAll('#games-grid img[data-src]'));
        this.totalCount = this.images.length;
        this.loadNextBatch();
    }

    loadNextBatch() {
        const start = this.currentBatch * this.batchSize;
        const end = Math.min(start + this.batchSize, this.totalCount);
        
        for (let i = start; i < end; i++) {
            this.loadImage(this.images[i]);
        }
        
        this.currentBatch++;
        
        // Load next batch if there are more images
        if (end < this.totalCount) {
            setTimeout(() => this.loadNextBatch(), 100);
        }
    }

    loadImage(img) {
        const src = img.getAttribute('data-src');
        const placeholder = img.getAttribute('data-placeholder');
        
        // Create a new image to test loading
        const testImg = new Image();
        testImg.onload = () => {
            img.src = src;
            img.classList.add('image-loaded');
            this.loadedCount++;
        };
        
        testImg.onerror = () => {
            img.src = placeholder;
            img.classList.add('image-loaded');
            this.loadedCount++;
            
            // Update JSON with placeholder if needed
            this.updateImageInJSON(img, placeholder);
        };
        
        testImg.src = src;
    }

    updateImageInJSON(img, placeholder) {
        // This would need to be implemented to update the JSON file
        // For now, we'll just log it
        const gameName = img.getAttribute('data-game-name');
        console.log(`Image failed to load for ${gameName}, using placeholder: ${placeholder}`);
    }
}

// Initialize image loader when page loads
document.addEventListener('DOMContentLoaded', function() {
    const imageLoader = new ImageLoader();
    imageLoader.init();
    
    <?php if (empty($selectedFile)): ?>
        const firstFile = document.querySelector('.dropdown-item');
        if (firstFile) {
            const fileName = firstFile.textContent.trim();
            selectFile(fileName);
        }
    <?php endif; ?>
    
    // Show success messages with auto-hide
    const successParam = new URLSearchParams(window.location.search).get('success');
    const resetParam = new URLSearchParams(window.location.search).get('reset');
    
    if (successParam || resetParam) {
        setTimeout(() => {
            // Remove success parameters from URL without reload
            const url = new URL(window.location);
            url.searchParams.delete('success');
            url.searchParams.delete('reset');
            window.history.replaceState({}, '', url);
        }, 3000);
    }
});

function handleImageError(img) {
    const placeholder = img.getAttribute('data-placeholder');
    img.src = placeholder;
    img.classList.add('image-loaded');
}

function toggleDropdown() {
    const menu = document.getElementById('dropdown-menu');
    menu.classList.toggle('show');
}

function selectFile(filename) {
    document.getElementById('dropdown-text').textContent = filename;
    toggleDropdown();
    window.location.href = `?file=${filename}`;
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.custom-dropdown')) {
        const menu = document.getElementById('dropdown-menu');
        menu.classList.remove('show');
    }
});

// Reset Modal Functions
function openResetModal() {
    const modal = document.getElementById('resetModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeResetModal() {
    const modal = document.getElementById('resetModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// Edit existing game
function openEdit(index, game, tab) {
    document.getElementById('editIndex').value = index;
    document.getElementById('editName').value = game.gameNameEn || game.name || game.title || '';
    document.getElementById('editGameCode').value = game.gameID || game.gameCode || game.id || game.code || '';
    document.getElementById('editImg').value = game.img || game.image || game.imgUrl || '';
    
    // Handle vendorCode/vendorId - only include one that exists
    const vendorCode = game.vendorCode || '';
    const vendorId = game.vendorId || game.vendorID || '';
    
    // Only include imgUrl2 if it has a value
    if (game.imgUrl2 && game.imgUrl2.trim() !== '') {
        document.getElementById('editImgUrl2').value = game.imgUrl2;
    } else {
        document.getElementById('editImgUrl2').removeAttribute('name');
    }
    
    // Update vendor info display
    const vendorContainer = document.getElementById('vendorInfoContainer');
    vendorContainer.innerHTML = '';
    
    if (vendorCode) {
        vendorContainer.innerHTML = `
            <div>
                <label class="block mb-2 font-semibold text-gray-700 text-sm">Vendor Code</label>
                <input type="text" name="vendorCode" value="${vendorCode}" 
                       class="w-full input-field readonly-field" readonly>
            </div>
        `;
    } else if (vendorId) {
        vendorContainer.innerHTML = `
            <div>
                <label class="block mb-2 font-semibold text-gray-700 text-sm">Vendor ID</label>
                <input type="text" name="vendorId" value="${vendorId}" 
                       class="w-full input-field readonly-field" readonly>
            </div>
        `;
    }
    
    document.getElementById('editTab').value = tab;
    
    // Update modal title
    document.getElementById('modalTitleText').textContent = 'Edit Game';
    
    previewImg();
    
    const modal = document.getElementById('editModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('editModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
}

function previewImg() {
    const url = document.getElementById('editImg').value.trim();
    const name = document.getElementById('editName').value || 'Game';
    const img = document.getElementById('preview');
    
    if (!url) {
        img.src = `https://placehold.co/200x200/000000/ffffff?text=${encodeURIComponent(name)}`;
    } else {
        img.src = url;
        img.onerror = () => {
            img.src = `https://placehold.co/200x200/000000/ffffff?text=${encodeURIComponent(name)}`;
        };
    }
}

// Enhanced Save functionality with success animation
document.getElementById('editForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const saveBtn = document.getElementById('saveBtn');
    const originalText = saveBtn.innerHTML;
    
    // Show saving state
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i> Saving...';
    saveBtn.disabled = true;
    saveBtn.classList.add('loading');
    
    // Simulate save delay and show success
    setTimeout(() => {
        saveBtn.innerHTML = '<i class="fas fa-check text-sm"></i> Saved!';
        saveBtn.classList.add('save-success');
        
        setTimeout(() => {
            // Submit the form after showing success
            e.target.submit();
        }, 800);
    }, 600);
});

// Enhanced Reset functionality with success animation
document.getElementById('resetForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const resetBtn = document.getElementById('resetBtn');
    const originalText = resetBtn.innerHTML;
    
    // Show resetting state
    resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i> Resetting...';
    resetBtn.disabled = true;
    resetBtn.classList.add('loading');
    
    // Simulate reset delay and show success
    setTimeout(() => {
        resetBtn.innerHTML = '<i class="fas fa-check text-sm"></i> Reset Success!';
        resetBtn.classList.add('reset-success');
        
        setTimeout(() => {
            // Submit the form after showing success
            e.target.submit();
        }, 800);
    }, 600);
});

// Close modals on outside click
window.addEventListener('click', e => {
    if (e.target.id === 'editModal') closeModal();
    if (e.target.id === 'resetModal') closeResetModal();
});

// Close modals on Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModal();
        closeResetModal();
    }
});

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.innerHTML = `
        <div class="px-4 py-3 rounded-lg ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white font-medium text-sm">
            ${message}
        </div>
    `;
    toast.classList.remove('hidden');
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}
</script>

</body>
</html>