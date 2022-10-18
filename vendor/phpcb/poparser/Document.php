<?php

namespace poparser;

require_once 'Parser.php';

/**
 * The document class for po files
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Document
 * @namespace poparser
 */
class Document
{
	/**
	 * Returns the parser instance
	 *
	 * @var $parser
	 * @type Parser
	 * @memberOf Document
	 * @private
	 */
	private $parser;

	/**
	 * The constructor
	 *
	 * @param string $text The text
	 * @memberOf Document
	 * @constructor
	 * @method __construct
	 */
	public function __construct($text)
	{
		$this->parser = new Parser($text);
		$this->parser->parse();
	}

	/**
	 * Gets a context as map
	 *
	 * @param string $context The context name
	 * @memberOf Document
	 * @method toMapContext
	 */
	public function toMapContext($context) {
		if(isset($this->parser->context[$context])) {
			$map = array();
			foreach($this->parser->context[$context] as $translation) {
				$map[$translation->base] = $translation->translation;
			}
			return $map;
		} else {
			return null;
		}
	}

	/**
	 * Gets all translations as map
	 *
	 * @memberOf Document
	 * @method toMap
	 */
	public function toMap() {
		$map = array();
		foreach($this->parser->translations as $translation) {
			$map[$translation->base] = $translation->translation;
		}
		return $map;
	}
}