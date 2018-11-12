<?php

require __DIR__ . '/../utils.php';
require __DIR__ . '/../core/DB.php';
require __DIR__ . '/../app/Methods.php';

session_start();
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if(isset($_GET['action'])) {
    if(method_exists('App\Methods', $_GET['action'])) {
        $_POST = json_decode(file_get_contents('php://input'), true);
        call_user_func(array(new \App\Methods(), $_GET['action']));
    }
}

error(['text' => 'Метод не найден']);