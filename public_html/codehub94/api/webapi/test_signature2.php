<?php
// Test data from the actual request
$testData = [
    'smsCode' => '',
    'ifsccode' => 'BARB0VJKHAS',
    'bankid' => 16,
    'beneficiaryname' => 'SAHIL KUMAR',
    'accountno' => '645634563456',
    'email' => '',
    'mobileno' => '918709122562',
    'bankcitycode' => '',
    'bankprovincecode' => '',
    'bankbranchaddress' => '',
    'type' => '',
    'codeType' => 6,
    'language' => 0,
    'random' => '8ad79dcec3f34514a977b85e8305948e',
    'timestamp' => 1747507760
];

$output = "Debug Results:\n";
$output .= "-------------\n\n";

// Test 1: Current method
$output .= "Test 1 - Current method:\n";
$jsonStr1 = json_encode($testData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$sig1 = strtoupper(md5($jsonStr1));
$output .= "JSON string:\n" . $jsonStr1 . "\n\n";
$output .= "Calculated signature: " . $sig1 . "\n";
$output .= "Expected signature:   7FCA0692184EF68442D35E17B90A5324\n\n";

// Test 2: All values as strings
$testData2 = array_map(function($value) {
    return (string)$value;
}, $testData);
$jsonStr2 = json_encode($testData2, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$sig2 = strtoupper(md5($jsonStr2));
$output .= "Test 2 - All values as strings:\n";
$output .= "JSON string:\n" . $jsonStr2 . "\n\n";
$output .= "Calculated signature: " . $sig2 . "\n\n";

// Test 3: No JSON encoding options
$jsonStr3 = json_encode($testData);
$sig3 = strtoupper(md5($jsonStr3));
$output .= "Test 3 - No JSON encoding options:\n";
$output .= "JSON string:\n" . $jsonStr3 . "\n\n";
$output .= "Calculated signature: " . $sig3 . "\n\n";

// Test 4: With JSON_PRESERVE_ZERO_FRACTION
$jsonStr4 = json_encode($testData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
$sig4 = strtoupper(md5($jsonStr4));
$output .= "Test 4 - With JSON_PRESERVE_ZERO_FRACTION:\n";
$output .= "JSON string:\n" . $jsonStr4 . "\n\n";
$output .= "Calculated signature: " . $sig4 . "\n\n";

// Test 5: Manual JSON construction
$manualJson = '{';
$first = true;
foreach ($testData as $key => $value) {
    if (!$first) {
        $manualJson .= ',';
    }
    $first = false;
    
    if (is_int($value)) {
        $manualJson .= '"' . $key . '":' . $value;
    } else {
        $manualJson .= '"' . $key . '":"' . $value . '"';
    }
}
$manualJson .= '}';
$sig5 = strtoupper(md5($manualJson));
$output .= "Test 5 - Manual JSON construction:\n";
$output .= "JSON string:\n" . $manualJson . "\n\n";
$output .= "Calculated signature: " . $sig5 . "\n\n";

// Write output to file
file_put_contents('debug_results.txt', $output);
echo "Test results have been written to debug_results.txt\n";
?> 