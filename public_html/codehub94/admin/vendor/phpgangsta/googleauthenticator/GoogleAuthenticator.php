<?php

namespace PHPGangsta;

class GoogleAuthenticator {
    protected $_codeLength = 6;

    public function createSecret($secretLength = 16) {
        $validChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $secretLength; $i++) {
            $secret .= $validChars[random_int(0, strlen($validChars) - 1)];
        }
        return $secret;
    }

    public function getQRCodeGoogleUrl($name, $secret, $title = null, $params = []) {
        $urlencoded = urlencode("otpauth://totp/{$name}?secret={$secret}&issuer=" . urlencode($title));
        return "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=" . $urlencoded;
    }

    public function verifyCode($secret, $code, $discrepancy = 1) {
        $currentCode = $this->getCode($secret);
        return $currentCode == $code;
    }

    public function getCode($secret, $timeSlice = null) {
    if ($timeSlice === null) {
        $timeSlice = floor(time() / 30);
    }

    $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $base32 = array_flip(str_split($base32chars));
    $binary = '';

    // ✅ Properly Decode Base32 Secret Key
    $secret = str_replace(' ', '', $secret); // Remove spaces
    foreach (str_split($secret) as $char) {
        if (!isset($base32[$char])) {
            throw new \Exception('Invalid character in Secret Key.');
        }
        $binary .= str_pad(base_convert($base32[$char], 10, 2), 5, '0', STR_PAD_LEFT);
    }
    $binary = pack('H*', dechex(bindec($binary)));

    $time = pack('N*', 0) . pack('N*', $timeSlice);
    $hash = hash_hmac('sha1', $time, $binary, true);
    $offset = ord($hash[19]) & 0xf;

    return str_pad(((ord($hash[$offset]) & 0x7f) << 24 |
                    (ord($hash[$offset + 1]) & 0xff) << 16 |
                    (ord($hash[$offset + 2]) & 0xff) << 8 |
                    (ord($hash[$offset + 3]) & 0xff)) % pow(10, 6), 
                    6, '0', STR_PAD_LEFT);
}
}
