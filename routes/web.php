<?php

use \Controllers\UsuarioController;

//PATH REQUISITADO
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

//GET
if($_SERVER['REQUEST_METHOD'] == 'GET'){
    switch($path){
        case '/':
            UsuarioController::getRaiz();
            break;
        case '/login':
            echo UsuarioController::getLogin();
            break;
        case '/home':
            echo UsuarioController::getHome();
            break;
        case '/logout':
            UsuarioController::logout();
            break;
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    switch($path){
        case '/login':
            echo UsuarioController::login();
            break;
    }
}