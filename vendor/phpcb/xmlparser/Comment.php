<?php

namespace xmlparser;

/**
 * A comment instance
 *
 * @author Hendrik Weiler
 * @version 1.0
 */
class Comment extends Node
{
	/**
	 * @param $content int The content of the node
	 */
	public function __construct($content)
	{
		parent::__construct('comment', array());
		$this->setContent($content);
	}

	/**
	 * Generates the node as xml
	 *
	 * @return string
	 */
	public function toXML()
	{
		return '<!-- ' . $this->content . ' -->' . PHP_EOL;
	}
}