<?php

define('PATH_ESTRUTURA_PADRAO', __DIR__ . '/src/Views/layouts/default/');
define('PATH_ESTRUTURA_AUTH', __DIR__ . '/src/Views/layouts/auth/');
define('PATH_VIEWS', __DIR__ . '/src/Views/');

ini_set('display_errors', '0');
ini_set('log_errors', '1');

date_default_timezone_set('America/Sao_Paulo');

ini_set('session.gc_maxlifetime', (string) (60 * 60 * 24 * 30));
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

require_once __DIR__ . '/autoload.php';

require_once __DIR__ . '/routes/web.php';
