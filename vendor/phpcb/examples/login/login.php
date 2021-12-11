<?php

define('RENDERER_PATH','../../');
require_once RENDERER_PATH . './renderer/Renderer.php';

use renderer\Renderer as Renderer;

print '<pre>';
$text = file_get_contents('login.html');
//$lexer = new \xmlparser\Lexer($text);
/*
$token = $lexer->get_next_token();
var_dump($token);
while($token->type != 'EOF') {
	$token = $lexer->get_next_token();
	var_dump($token);
}
*/
$parser = new \xmlparser\Document($text);
$parser->parse();
//$render = new Renderer($text);
//print ($render->render());