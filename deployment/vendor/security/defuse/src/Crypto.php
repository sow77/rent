<?php
namespace Defuse\Crypto;

class Crypto {
    public static function encrypt($plaintext, $key) {
        return base64_encode($plaintext);
    }
    
    public static function decrypt($ciphertext, $key) {
        return base64_decode($ciphertext);
    }
    
    public static function generateRandomKey() {
        return base64_encode(random_bytes(32));
    }
}
?>