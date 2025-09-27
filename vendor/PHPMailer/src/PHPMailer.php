<?php
namespace PHPMailer\PHPMailer;

class PHPMailer {
    public function __construct($exceptions = null) {
        // Constructor básico
    }
    
    public function isSMTP() {
        return $this;
    }
    
    public function setHost($host) {
        return $this;
    }
    
    public function setPort($port) {
        return $this;
    }
    
    public function setSMTPAuth($auth) {
        return $this;
    }
    
    public function setUsername($username) {
        return $this;
    }
    
    public function setPassword($password) {
        return $this;
    }
    
    public function setSMTPSecure($secure) {
        return $this;
    }
    
    public function setFrom($address, $name = "") {
        return $this;
    }
    
    public function addAddress($address, $name = "") {
        return $this;
    }
    
    public function addReplyTo($address, $name = "") {
        return $this;
    }
    
    public function isHTML($isHtml = true) {
        return $this;
    }
    
    public function setSubject($subject) {
        return $this;
    }
    
    public function setBody($body) {
        return $this;
    }
    
    public function send() {
        return true;
    }
    
    public function getErrorInfo() {
        return "";
    }
}
?>