<?php

namespace xmlparser;

/**
 * A comment instance
 *
 * @namespace xmlparser
 * @class Comment
 * @author Hendrik Weiler
 * @version 1.0
 * @extends xmlparser.Node
 */
class Comment extends Node
{
	/**
	 * The constructor
	 *
	 * @param int $content The content of the node
	 * @param Document $document The document instance
	 * @memberOf Comment
	 * @method __construct
	 * @constructor
	 */
	public function __construct($content, $document)
	{
		parent::__construct('comment', array(), $document);
		$this->setContent($content);
	}

	/**
	 * Generates the node as xml
	 *
	 * @memberOf Comment
	 * @method toXML
	 * @return string
	 */
	public function toXML()
	{
		return '<!-- ' . $this->content . ' -->' . PHP_EOL;
	}
}