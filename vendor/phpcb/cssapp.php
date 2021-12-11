<?php

require_once 'cssparser/Document.php';

$css = file_get_contents('style.css');

print '<pre>';
$parser = new \cssparser\Document($css);
$definitions = $parser->parse();
var_dump($definitions);
print '<pre>';
print $parser->toCSS();
/*
$token = $lexer->get_next_token();
print '<pre>';
var_dump($token);
while ($token->type != \cssparser\Type::EOF) {
	$token = $lexer->get_next_token();
	var_dump($token);
}
*/