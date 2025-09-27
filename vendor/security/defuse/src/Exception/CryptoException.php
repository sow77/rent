<?php
namespace Defuse\Crypto\Exception;

class CryptoException extends \Exception {}
class WrongKeyOrModifiedCiphertextException extends CryptoException {}
class BadFormatException extends CryptoException {}
class EnvironmentIsBrokenException extends CryptoException {}
?>