<?php

namespace xmlparser;

/**
 * The declaration class
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Delcaration
 * @namespace xmlparser
 */
class Declaration extends Node
{
	/**
	 * The constructor
	 *
	 * @param string $name The name
	 * @param array $attributes The attributes
	 * @param Document $document The document instance
	 * @memberOf Declaration
	 * @method __construct
	 * @constructor
	 */
	public function __construct($name, $attributes, $document)
	{
		parent::__construct($name, $attributes, $document);
	}

	public function toXML()
	{
		return '';
	}
}