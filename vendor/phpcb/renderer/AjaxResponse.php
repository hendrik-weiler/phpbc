<?php

namespace renderer;

/**
 * Handles the response for ajax calls
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class AjaxResponse
 * @namespace renderer
 * @extends renderer.Response
 */
class AjaxResponse extends \renderer\Response
{
	/**
	 * Returns the method name to call in the frontend
	 *
	 * @var $methodName
	 * @type string
	 * @memberOf AjaxResponse
	 * @protected
	 */
	protected $methodName;

	/**
	 * Returns the return value of the call
	 *
	 * @var $returnValue
	 * @type mixed
	 * @memberOf AjaxResponse
	 * @protected
	 */
	protected $returnValue;

	/**
	 * The constructor
	 *
	 * @param string $methodName The method name to call
	 * @memberOf AjaxResponse
	 * @constructor
	 * @method __construct
	 */
	public function __construct($methodName)
	{
		$this->methodName = $methodName;
	}

	/**
	 * Sets the content for the return value
	 *
	 * @param mixed $value The return value
	 * @memberOf AjaxResponse
	 * @method setContent
	 */
	public function setContent($value) {
		$this->returnValue = $value;
	}

	/**
	 * Converts the response into a json string
	 *
	 * @return false|string
	 * @memberOf AjaxResponse
	 * @method __toString
	 */
	public function __toString()
	{
		return json_encode(array(
			'method' => 'ajax_' . $this->methodName,
			'value' => $this->returnValue
		));
	}
}