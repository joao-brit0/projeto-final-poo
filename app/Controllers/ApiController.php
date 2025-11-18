<?php

require_once __DIR__ . '/../Core/Response.php';

class ApiController {
    protected function getJsonBody(): array {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        return is_array($data) ? $data : $_POST;
    }
}
