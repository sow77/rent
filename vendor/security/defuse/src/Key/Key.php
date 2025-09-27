<?php
namespace Defuse\Crypto\Key;

class Key {
    private $key;
    
    public function __construct($key) {
        $this->key = $key;
    }
    
    public function getRawBytes() {
        return $this->key;
    }
    
    public static function createNewRandomKey() {
        return new self(random_bytes(32));
    }
    
    public static function loadFromAsciiSafeString($savedKeyString) {
        return new self(base64_decode($savedKeyString));
    }
    
    public function saveToAsciiSafeString() {
        return base64_encode($this->key);
    }
}
?>