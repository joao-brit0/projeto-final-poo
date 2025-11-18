<?php

class Router {
    private array $routes = [];
    private string $basePath = '';

    public function __construct(string $basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }

    public function get(string $path, $action): void { $this->add('GET', $path, $action); }
    public function post(string $path, $action): void { $this->add('POST', $path, $action); }
    public function put(string $path, $action): void { $this->add('PUT', $path, $action); }
    public function delete(string $path, $action): void { $this->add('DELETE', $path, $action); }
    public function options(string $path, $action): void { $this->add('OPTIONS', $path, $action); }

    private function add(string $method, string $path, $action): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->normalize($path),
            'action' => $action
        ];
    }

    private function normalize(string $path): string {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : $path;
    }

    private function getRequestPath(): string {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

        if ($this->basePath !== '' && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
            if ($uri === '') $uri = '/';
        }

        return rtrim($uri, '/') === '' ? '/' : rtrim($uri, '/');
    }

    private function match(string $requestMethod, string $requestPath) {
        foreach ($this->routes as $r) {
            if ($r['method'] !== $requestMethod && $r['method'] !== 'OPTIONS') continue;

            $pattern = preg_replace('#\\{[^\\}]+\\}#', '([^/]+)', $r['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $requestPath, $matches)) {
                array_shift($matches);

                preg_match_all('#\\{([^\\}]+)\\}#', $r['path'], $keys);
                $params = [];

                if (!empty($keys[1])) {
                    foreach ($keys[1] as $i => $name) {
                        $params[$name] = $matches[$i] ?? null;
                    }
                }

                return ['action' => $r['action'], 'params' => $params];
            }
        }
        return null;
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = $this->getRequestPath();

        $protectedRoutes = [
            '/api/rooms'    => ['POST'],
            '/api/bookings' => ['POST'],
        ];

        if (isset($protectedRoutes[$path]) && in_array($method, $protectedRoutes[$path])) {
            $user = $this->validateToken();

            if ($path === '/api/rooms' && $user['role'] !== 'admin') {
                Response::error('Apenas admin pode cadastrar salas', 403);
            }

            $_REQUEST['_user'] = $user;
        }

        if ($method === 'OPTIONS') {
            Response::json(['ok' => true], 200);
        }

        $match = $this->match($method, $path);
        if (!$match) {
            Response::error("Rota não encontrada: {$path}", 404);
        }

        $action = $match['action'];
        $params = $match['params'];

        if (is_string($action) && strpos($action, '@') !== false) {
            [$controllerName, $method] = explode('@', $action, 2);
            $controllerFile = __DIR__ . '/../Controllers/' . $controllerName . '.php';

            if (!file_exists($controllerFile)) {
                Response::error("Controller {$controllerName} não existe", 500);
            }

            require_once $controllerFile;

            if (!class_exists($controllerName)) {
                Response::error("Classe {$controllerName} não encontrada", 500);
            }

            $controller = new $controllerName();

            if (!method_exists($controller, $method)) {
                Response::error("Método {$method} não encontrado", 500);
            }

            $controller->{$method}($params);
            return;
        }

        if (is_callable($action)) {
            call_user_func($action, $params);
            return;
        }

        Response::error('Ação inválida.', 500);
    }

    private function validateToken() {
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        Response::error('Token não enviado', 401);
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);

    // capturar SOMENTE o último ponto
    $lastDot = strrpos($token, '.');

    if ($lastDot === false) {
        Response::error('Token inválido', 401);
    }

    $payloadEncoded = substr($token, 0, $lastDot);
    $secretEncoded  = substr($token, $lastDot + 1);

    $payload = json_decode(base64_decode($payloadEncoded), true);
    $secret  = base64_decode($secretEncoded);

    if (!$payload) {
        Response::error('Token malformado', 401);
    }

    if ($secret !== 'MEU_SEGREDO_SUPER_FORTE') {
        Response::error('Assinatura inválida', 403);
    }

    if (time() > $payload['exp']) {
        Response::error('Token expirado', 401);
    }

    return $payload;
}

}
