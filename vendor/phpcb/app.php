<?php

require_once 'xmlparser/Document.php';

use \xmlparser\Document as Document;

define('APP_PATH','../../app/');

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

$test = new \xmlparser\Node('test',array('id'=>'test'),$doc);
$rootNode->appendChild($test);

print '<pre>';
print htmlspecialchars($rootNode->toXML());