<?php

define('PATH_ESTRUTURA_PADRAO', __DIR__ . '/src/Views/layouts/default/');

session_start();

require_once __DIR__ . '/autoload.php';

require_once __DIR__ . '/routes/web.php';