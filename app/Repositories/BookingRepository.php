<?php

require_once __DIR__ . '/interfaces/IRepository.php';

class BookingRepository implements IRepository {
    private PDO $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(array $filters = []) {
        $sql = "SELECT b.*, u.name as user_name, r.code as room_code FROM bookings b
                JOIN users u ON u.id = b.user_id
                JOIN rooms r ON r.id = b.room_id
                ORDER BY b.start_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function find(int $id) {
        $stm = $this->db->prepare("SELECT * FROM bookings WHERE id = ?");
        $stm->execute([$id]);
        return $stm->fetch();
    }

    public function create(array $data) {
        
        $stm = $this->db->prepare("INSERT INTO bookings (room_id, user_id, start_at, end_at) VALUES (?,?,?,?)");
        $stm->execute([$data['room_id'], $data['user_id'], $data['start_at'], $data['end_at']]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data) {
        $stm = $this->db->prepare("UPDATE bookings SET start_at=?, end_at=?, room_id=? WHERE id=?");
        return $stm->execute([$data['start_at'], $data['end_at'], $data['room_id'], $id]);
    }

    public function delete(int $id) {
        $stm = $this->db->prepare("DELETE FROM bookings WHERE id=?");
        return $stm->execute([$id]);
    }

    // verifica conflito de horários para uma sala
    public function hasConflict(int $roomId, string $startAt, string $endAt): bool {
        $sql = "SELECT COUNT(*) as cnt FROM bookings
                WHERE room_id = :room_id
                AND NOT (end_at <= :start OR start_at >= :end)";
        $stm = $this->db->prepare($sql);
        $stm->execute(['room_id' => $roomId, 'start' => $startAt, 'end' => $endAt]);
        $r = $stm->fetch();
        return (int)$r['cnt'] > 0;
    }
}
