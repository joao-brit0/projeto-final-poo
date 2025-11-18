<?php

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../Repositories/BookingRepository.php';
require_once __DIR__ . '/../Repositories/RoomRepository.php';

class BookingController extends ApiController
{
    private BookingRepository $repo;
    private RoomRepository $roomRepo;

    public function __construct()
    {
        $this->repo = new BookingRepository();
        $this->roomRepo = new RoomRepository();
    }

    // GET /api/bookings
    public function index($params = [])
    {
        $list = $this->repo->getAll();
        Response::json($list);
    }

    public function store($params = []) {
    
    // usuário autenticado vindo do Router
    $user = $_REQUEST["_user"];

    $data = $this->getJsonBody();

    // garante que o agendamento seja sempe do usuário logado
    $data["user_id"] = $user["id"];

    foreach (['room_id','start_at','end_at'] as $f) {
        if (empty($data[$f])) Response::error("Campo {$f} obrigatório", 422);
    }

    try {
        $start = new DateTime(str_replace("T", " ", $data['start_at']));
        $end = new DateTime(str_replace("T", " ", $data['end_at']));
    } catch (Exception $e) {
        Response::error("Formato de data inválido", 422);
    }

    if ($end <= $start) Response::error("End deve ser maior que start", 422);

    $roomId = (int)$data['room_id'];

    if ($this->repo->hasConflict($roomId, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'))) {
        Response::error("Sala já está ocupada neste intervalo", 409);
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    try {
        $id = $this->repo->create([
            'room_id' => $roomId,
            'user_id' => $user["id"],          
            'start_at' => $start->format('Y-m-d H:i:s'),
            'end_at' => $end->format('Y-m-d H:i:s')
        ]);

        $db->commit();
        Response::json(['id' => $id], 201);
    } catch (Exception $e) {
        $db->rollBack();
        Response::error("Erro ao criar agendamento", 500);
    }
}

    // Função delete, não cheguei a terminar
    public function delete($params)
    {
        $id = (int)$params['id'];
        $b = $this->repo->find($id);
        if (!$b) Response::error("Agendamento não encontrado", 404);

        $this->repo->delete($id);
        Response::json(['ok' => true]);
    }
}
