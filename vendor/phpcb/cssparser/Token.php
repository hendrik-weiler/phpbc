<?php

namespace cssparser;

/**
 * The token class
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @namespace cssparser
 * @class Token
 */
class Token
{
	/**
	 * Returns the tokens type
	 *
	 * @var $type
	 * @type Type
	 * @memberOf Token
	 */
	public $type;

	/**
	 * Returns the tokens value
	 *
	 * @var $value
	 * @type string|null
	 * @memberOf Token
	 */
	public $value;

	/**
	 * The constructor
	 *
	 * @param Type $type The type
	 * @param string $value The value
	 * @constructor
	 * @memberOf Token
	 * @method __construct
	 */
	public function __construct($type, $value)
	{
		$this->type = $type;
		$this->value = $value;
	}
}