<?php

require_once 'parser/Document.php';

use \xmlparser\Document as Document;

$text = file_get_contents('test.xml');
var_dump(htmlspecialchars($text));
print '<pre>';

$doc = new Document($text);
/*
$doc->printTokens();
exit();
*/
$rootNode = $doc->parse();

//var_dump($parser->declarations, $parser->nodes);

$rootNode->appendChild(new \xmlparser\Node('test',array('id'=>'test'),$doc));

print '<pre>';
print htmlspecialchars($rootNode->toXML());

var_dump($doc->getDoctypes());

$nodes =$doc->getElementsByTagName('head');
$nodes[0]->parentNode->removeChild($nodes[0]);
print htmlspecialchars($rootNode->toXML());