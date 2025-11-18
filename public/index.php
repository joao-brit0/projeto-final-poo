<?php

require_once __DIR__ . '/../app/config.php';
$config = require __DIR__ . '/../app/config.php';

// inicializa DB
require_once __DIR__ . '/../app/Core/Database.php';
Database::init($config['db']);

require_once __DIR__ . '/../app/Core/Response.php';
require_once __DIR__ . '/../app/Core/Router.php';

$basePath = $config['base_path'] ?? '';
$router = new Router($basePath);

$router->get('/api/rooms', 'RoomController@index');
$router->get('/api/rooms/{id}', 'RoomController@show');
$router->get('/api/rooms/availability', 'RoomController@availability');

$router->post('/api/rooms', 'RoomController@store');

$router->post('/api/login', 'AuthController@login');

$router->get('/api/bookings', 'BookingController@index');
$router->post('/api/bookings', 'BookingController@store');
$router->delete('/api/bookings/{id}', 'BookingController@delete');


$router->get('/', function() {
    Response::json(['ok' => true, 'app' => 'RoomBooking API']);
});

$router->dispatch();
