<?php
// Chub94/getallgames.php

// Get the base path from global
$dataBasePath = $GLOBALS['dataBasePath'] ?? dirname(__FILE__) . '/../../api/webapi/data/';
$resetBasePath = dirname(__FILE__) . '/../../api/webapi/reset/'; // Reset folder path
$selectedFile = $GLOBALS['selectedFile'] ?? '';
$currentTab = $GLOBALS['currentTab'] ?? 'all';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $filename = $_POST['filename'] ?? '';
    $index = $_POST['index'] ?? null;
    $tab = $_POST['tab'] ?? 'all';
    
    $filePath = $dataBasePath . $filename;
    $resetFilePath = $resetBasePath . $filename; // Corresponding reset file
    
    if ($action === 'save_game') {
        // Load current data
        $currentData = [];
        if (file_exists($filePath)) {
            $jsonContent = file_get_contents($filePath);
            $currentData = json_decode($jsonContent, true) ?? [];
        }
        
        // Handle different JSON structures
        if (isset($currentData['gameLists']) && is_array($currentData['gameLists'])) {
            // Structure with gameLists array
            if (isset($currentData['gameLists'][$index])) {
                // Update existing game - ONLY editable fields
                $currentData['gameLists'][$index]['gameNameEn'] = $_POST['gameNameEn'] ?? '';
                $currentData['gameLists'][$index]['gameID'] = $_POST['gameID'] ?? '';
                $currentData['gameLists'][$index]['img'] = $_POST['img'] ?? '';
                
                // Preserve other fields EXACTLY as they were
                // vendorId as number (without quotes)
                $currentData['gameLists'][$index]['vendorId'] = isset($_POST['vendorId']) ? (int)$_POST['vendorId'] : ($currentData['gameLists'][$index]['vendorId'] ?? 0);
                
                // vendorCode as string (with quotes if it's string in original)
                $currentData['gameLists'][$index]['vendorCode'] = $_POST['vendorCode'] ?? $currentData['gameLists'][$index]['vendorCode'] ?? '';
                
                // customGameType as number (without quotes)
                $currentData['gameLists'][$index]['customGameType'] = isset($_POST['customGameType']) ? (int)$_POST['customGameType'] : ($currentData['gameLists'][$index]['customGameType'] ?? 0);
                
                // imgUrl2 as string
                $currentData['gameLists'][$index]['imgUrl2'] = $_POST['imgUrl2'] ?? $currentData['gameLists'][$index]['imgUrl2'] ?? '';
            }
        } elseif (is_array($currentData) && isset($currentData[0])) {
            // Flat array structure
            if (isset($currentData[$index])) {
                $currentData[$index]['gameNameEn'] = $_POST['gameNameEn'] ?? '';
                $currentData[$index]['gameID'] = $_POST['gameID'] ?? '';
                $currentData[$index]['img'] = $_POST['img'] ?? '';
                
                // Preserve other fields EXACTLY as they were
                $currentData[$index]['vendorId'] = isset($_POST['vendorId']) ? (int)$_POST['vendorId'] : ($currentData[$index]['vendorId'] ?? 0);
                $currentData[$index]['vendorCode'] = $_POST['vendorCode'] ?? $currentData[$index]['vendorCode'] ?? '';
                $currentData[$index]['customGameType'] = isset($_POST['customGameType']) ? (int)$_POST['customGameType'] : ($currentData[$index]['customGameType'] ?? 0);
                $currentData[$index]['imgUrl2'] = $_POST['imgUrl2'] ?? $currentData[$index]['imgUrl2'] ?? '';
            }
        }
        
        // Save back to file with proper JSON encoding
        $jsonString = json_encode($currentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (file_put_contents($filePath, $jsonString) === false) {
            error_log("Failed to write to file: " . $filePath);
        }
        
    } elseif ($action === 'delete_game') {
        // Load current data
        $currentData = [];
        if (file_exists($filePath)) {
            $jsonContent = file_get_contents($filePath);
            $currentData = json_decode($jsonContent, true) ?? [];
        }
        
        // Handle deletion based on structure
        if (isset($currentData['gameLists']) && is_array($currentData['gameLists'])) {
            if (isset($currentData['gameLists'][$index])) {
                array_splice($currentData['gameLists'], $index, 1);
            }
        } elseif (is_array($currentData) && isset($currentData[0])) {
            if (isset($currentData[$index])) {
                array_splice($currentData, $index, 1);
            }
        }
        
        // Save back to file
        $jsonString = json_encode($currentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($filePath, $jsonString);
        
    } elseif (isset($_POST['reset_data'])) {
        // RESET FEATURE - Copy from reset folder to data folder
        if (file_exists($resetFilePath)) {
            // Copy reset file to data folder
            if (copy($resetFilePath, $filePath)) {
                $_SESSION['reset_success'] = true;
                error_log("Reset successful: " . $filename);
            } else {
                error_log("Reset failed: Could not copy " . $resetFilePath . " to " . $filePath);
            }
        } else {
            // If reset file doesn't exist, create empty data
            $defaultData = [
                "gameCustomTypeLists" => [],
                "gameLists" => []
            ];
            
            $jsonString = json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if (file_put_contents($filePath, $jsonString)) {
                $_SESSION['reset_success'] = true;
            }
            error_log("Reset file not found: " . $resetFilePath);
        }
    }
    
    // Prevent further execution after POST
    return;
}

// Handle GET requests - load data for display
if (!empty($selectedFile)) {
    $filePath = $dataBasePath . $selectedFile;
    
    if (file_exists($filePath)) {
        $jsonContent = file_get_contents($filePath);
        $gameData = json_decode($jsonContent, true);
        
        if ($gameData === null) {
            $gameData = [
                "gameCustomTypeLists" => [],
                "gameLists" => [],
                "error" => "Invalid JSON format in file"
            ];
        }
    } else {
        // File doesn't exist, check if reset file exists and copy it
        $resetFilePath = $resetBasePath . $selectedFile;
        if (file_exists($resetFilePath)) {
            if (copy($resetFilePath, $filePath)) {
                $jsonContent = file_get_contents($filePath);
                $gameData = json_decode($jsonContent, true);
            } else {
                $gameData = [
                    "gameCustomTypeLists" => [],
                    "gameLists" => [],
                    "error" => "Could not copy from reset folder"
                ];
            }
        } else {
            // Create with default structure
            $gameData = [
                "gameCustomTypeLists" => [],
                "gameLists" => []
            ];
            
            $jsonString = json_encode($gameData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            file_put_contents($filePath, $jsonString);
        }
    }
} else {
    // If no file selected, show empty data
    $gameData = [
        "gameCustomTypeLists" => [],
        "gameLists" => []
    ];
}

// Return data for inclusion in main file
return;
?>