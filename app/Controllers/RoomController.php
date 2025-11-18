<?php

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../Repositories/RoomRepository.php';
require_once __DIR__ . '/../Repositories/BookingRepository.php';

class RoomController extends ApiController {
    private RoomRepository $roomRepo;
    private BookingRepository $bookingRepo;

    public function __construct() {
        $this->roomRepo = new RoomRepository();
        $this->bookingRepo = new BookingRepository();
    }

    // GET /api/rooms lista todas salas
    public function index($params = []) {
        $rooms = $this->roomRepo->getAll();
        Response::json($rooms);
    }


    // verificar as disponibilidades depois de um certo tempo
    public function availability($params = []) {
         // leitura pelo query string
         $qs = $_GET;
         if (empty($qs['start']) || empty($qs['end'])) {
             Response::error("Parâmetros 'start' e 'end' obrigatórios", 400);
         }
         try {
             $start = new DateTime($qs['start']);
             $end = new DateTime($qs['end']);
         } catch (Exception $e) {
            Response::error("Formato de data inválido", 400);
         }

         $available = $this->roomRepo->getAvailableBetween($start, $end);
         Response::json(['available' => $available]);
    }
    public function store($params = []) {
    $data = $this->getJsonBody();

    foreach (['code', 'type', 'capacity'] as $field) {
        if (empty($data[$field])) {
            Response::error("Campo {$field} obrigatório", 422);
        }
    }

    $id = $this->roomRepo->create($data);
    Response::json(['id' => $id], 201);
}

}
