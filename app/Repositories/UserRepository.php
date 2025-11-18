<?php

class UserRepository {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail(string $email) {
        $stm = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stm->execute([$email]);
        return $stm->fetch();
    }

    public function create(array $data) {
        $stm = $this->db->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
        $stm->execute([
            $data["name"],
            $data["email"],
            password_hash($data["password"], PASSWORD_DEFAULT),
            $data["role"] ?? "professor"
        ]);
        return $this->db->lastInsertId();
    }
}
