<?php

class Database {
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct(array $cfg) {
        $dns = "mysql:host={$cfg['host']};dbname={$cfg['dbname']};charset={$cfg['charset']}";
        $this->pdo = new PDO($dns, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    public static function init(array $cfg): void {
        if (self::$instance === null) {
            self::$instance = new Database($cfg);
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            throw new RuntimeException("Database not initialized.");
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }
}
