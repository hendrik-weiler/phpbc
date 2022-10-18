<?php

define('RENDERER_PATH','../../');
require_once RENDERER_PATH . './renderer/Renderer.php';

require_once 'controller/App.php';

use renderer\Renderer as Renderer;

$text = file_get_contents('app.html');
$render = new Renderer($text);
print ($render->render());