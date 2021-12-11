<?php

define('RENDERER_PATH','vendor/phpcb/');
define('APP_PATH','app/');
require_once RENDERER_PATH . 'renderer/Server.php';
require_once 'app/config.php';
require_once 'app/routes.php';

$server = new Server($_SERVER['REQUEST_URI'], $routes);
$server->serve();