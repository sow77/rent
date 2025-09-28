<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }

    public function query($sql, $params = []) {
        try {
            if (empty($params)) {
                $stmt = $this->connection->query($sql);
                return $stmt->fetchAll();
            } else {
                $stmt = $this->connection->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetchAll();
            }
        } catch (PDOException $e) {
            die("Error en la consulta: " . $e->getMessage());
        }
    }

    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            die("Error en la ejecución: " . $e->getMessage());
        }
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    // public static function executeQuery(string $sql, array $params = []) {
    //     // Método estático que reutiliza query()
    //     return self::getInstance()->query($sql, $params);
    // }

    public static function executeQuery(string $sql, ...$params) {
        return self::getInstance()->query($sql, $params);
    }

    public function close() {
        $this->connection = null;
    }
}

// Wrapper global para compatibilidad con llamadas directas.
function executeQuery(string $sql, ...$args) {
    // Si sólo hay un argumento y es array, usarlo como parámetros.
    if (count($args) === 1 && is_array($args[0])) {
        $params = $args[0];
    } else {
        $params = $args;
    }
    return Database::executeQuery($sql, ...$params);
}