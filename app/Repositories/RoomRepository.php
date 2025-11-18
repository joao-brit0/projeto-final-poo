<?php

require_once __DIR__ . '/interfaces/IRepository.php';

class RoomRepository implements IRepository {
    private PDO $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(array $filters = []) {
        $sql = "SELECT * FROM rooms";
        $params = [];
        if (!empty($filters['type'])) {
            $sql .= " WHERE type = ?";
            $params[] = $filters['type'];
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id) {
        $stm = $this->db->prepare("SELECT * FROM rooms WHERE id = ?");
        $stm->execute([$id]);
        return $stm->fetch();
    }

    public function create(array $data) {
        $stm = $this->db->prepare("INSERT INTO rooms (code,type,capacity,location) VALUES (?,?,?,?)");
        $stm->execute([$data['code'],$data['type'],$data['capacity'],$data['location'] ?? null]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data) {
        $stm = $this->db->prepare("UPDATE rooms SET code=?, type=?, capacity=?, location=? WHERE id=?");
        return $stm->execute([$data['code'],$data['type'],$data['capacity'],$data['location'] ?? null, $id]);
    }

    public function delete(int $id) {
        $stm = $this->db->prepare("DELETE FROM rooms WHERE id=?");
        return $stm->execute([$id]);
    }

    // retorna disponibilidades 
    public function getAvailableBetween(DateTime $start, DateTime $end) {
        $sql = "
          SELECT r.* FROM rooms r
          WHERE r.id NOT IN (
            SELECT b.room_id FROM bookings b
            WHERE NOT (b.end_at <= :start OR b.start_at >= :end)
          )
        ";
        $stm = $this->db->prepare($sql);
        $stm->execute(['start' => $start->format('Y-m-d H:i:s'), 'end' => $end->format('Y-m-d H:i:s')]);
        return $stm->fetchAll();
    }
}
