<?php
require_once __DIR__ . "/ApiController.php";
require_once __DIR__ . "/../Repositories/UserRepository.php";

class AuthController extends ApiController {

    private UserRepository $repo;

    public function __construct() {
        $this->repo = new UserRepository();
    }

    public function login() {
        $data = $this->getJsonBody();

        if (empty($data["email"]) || empty($data["password"])) {
            Response::error("Email e senha obrigatórios", 400);
        }

        $user = $this->repo->findByEmail($data["email"]);

        if (!$user) {
            Response::error("Usuário não encontrado", 404);
        }

        if (!password_verify($data["password"], $user["password"])) {
            Response::error("Senha incorreta", 401);
        }

        // criar token JWT simples
        $payload = [
            "id" => $user["id"],
            "email" => $user["email"],
            "role" => $user["role"],
            "iat" => time(),
            "exp" => time() + 3600
        ];

        $secret = "MEU_SEGREDO_SUPER_FORTE";

        $token = base64_encode(json_encode($payload)) . "." . base64_encode($secret);

        Response::json([
            "token" => $token,
            "user" => [
                "id" => $user["id"],
                "name" => $user["name"],
                "role" => $user["role"]
            ]
        ]);
    }
}
