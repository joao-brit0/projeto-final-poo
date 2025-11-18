<?php

class Room {
    private int $id;
    private string $code;
    private string $type;
    private int $capacity;
    private ?string $location;

    public function __construct(string $code, string $type, int $capacity, ?string $location = null, int $id = 0) {
        $this->code = $code;
        $this->type = $type;
        $this->capacity = $capacity;
        $this->location = $location;
        $this->id = $id;
    }

    // getters / setters (encapsulamento)
    public function getId(): int { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getType(): string { return $this->type; }
    public function getCapacity(): int { return $this->capacity; }
    public function getLocation(): ?string { return $this->location; }

    public function setCode(string $c): void { $this->code = $c; }
    public function setType(string $t): void { $this->type = $t; }
    public function setCapacity(int $n): void { $this->capacity = $n; }
    public function setLocation(?string $loc): void { $this->location = $loc; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'capacity' => $this->capacity,
            'location' => $this->location
        ];
    }
}
