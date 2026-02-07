<?php

function checkSystemRequirements() {
    $requirements = [
        'PHP Version' => [
            'required' => '7.4',
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '7.4', '>=')
        ],
        'MySQL Extension' => [
            'required' => 'Enabled',
            'current' => extension_loaded('mysqli') ? 'Enabled' : 'Not Enabled',
            'status' => extension_loaded('mysqli')
        ],
        'cURL Extension' => [
            'required' => 'Enabled',
            'current' => extension_loaded('curl') ? 'Enabled' : 'Not Enabled',
            'status' => extension_loaded('curl')
        ],
        'File Permissions (Writable)' => [
            'required' => 'Writable',
            'current' => is_writable(__DIR__) ? 'Writable' : 'Not Writable',
            'status' => is_writable(__DIR__)
        ],
        'Memory Limit' => [
            'required' => '128M',
            'current' => ini_get('memory_limit'),
            'status' => (int)ini_get('memory_limit') >= 128
        ]
    ];
    return $requirements;
}

$results = checkSystemRequirements();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Requirement Checker</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9; color: #333; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #007BFF; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #007BFF; color: #fff; }
        .pass { color: #28a745; font-weight: bold; }
        .fail { color: #dc3545; font-weight: bold; }
        .status-box { text-align: center; padding: 10px; border-radius: 5px; font-weight: bold; }
        .pass-box { background-color: #d4edda; color: #155724; }
        .fail-box { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h2>System Requirement Checker</h2>
        <table>
            <tr>
                <th>Requirement</th>
                <th>Required</th>
                <th>Current</th>
                <th>Status</th>
            </tr>
            <?php foreach ($results as $key => $value): ?>
                <tr>
                    <td><?php echo $key; ?></td>
                    <td><?php echo $value['required']; ?></td>
                    <td><?php echo $value['current']; ?></td>
                    <td>
                        <div class="status-box <?php echo $value['status'] ? 'pass-box' : 'fail-box'; ?>">
                            <?php echo $value['status'] ? 'Pass' : 'Fail'; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
