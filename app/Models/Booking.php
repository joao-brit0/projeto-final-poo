<?php

class Booking {
    private int $id;
    private int $roomId;
    private int $userId;
    private DateTime $startAt;
    private DateTime $endAt;

    public function __construct(int $roomId, int $userId, DateTime $startAt, DateTime $endAt, int $id = 0) {
        $this->roomId = $roomId;
        $this->userId = $userId;
        $this->startAt = $startAt;
        $this->endAt = $endAt;
        $this->id = $id;
    }

    public function getId(): int { return $this->id; }
    public function getRoomId(): int { return $this->roomId; }
    public function getUserId(): int { return $this->userId; }
    public function getStartAt(): DateTime { return $this->startAt; }
    public function getEndAt(): DateTime { return $this->endAt; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'room_id' => $this->roomId,
            'user_id' => $this->userId,
            'start_at' => $this->startAt->format('Y-m-d H:i:s'),
            'end_at' => $this->endAt->format('Y-m-d H:i:s'),
        ];
    }
}
