<?php

define('RENDERER_PATH','../../');
require_once RENDERER_PATH . './renderer/Renderer.php';

use renderer\Renderer as Renderer;

print '<pre>';
$text = file_get_contents('login.html');
/*
$text = "<div id='test'>1<br/><meta/><br/>2<br/><br/>3<br/><br/>4</div>";
$lexer = new \xmlparser\Lexer($text);
$token = $lexer->get_next_token();
var_dump($token);
while($token->type != 'EOF') {
	$token = $lexer->get_next_token();
	var_dump($token);
}
*/

$parser = new \xmlparser\Document($text);
$node = $parser->parse();

$div = $parser->getElementById('test');
foreach($div->children as $child) {
	var_dump($child->name);
}
var_dump($div->getContent());

print $node->toXML();


//$render = new Renderer($text);
//print ($render->render());