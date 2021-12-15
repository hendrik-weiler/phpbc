<?php

/**
 * Handles the response for ajax calls
 *
 * @author Hendrik Weiler
 */
class AjaxResponse extends \renderer\Response
{
	/**
	 * Returns the method name to call in the frontend
	 *
	 * @var $methodName
	 * @type string
	 */
	protected $methodName;

	/**
	 * Returns the return value of the call
	 *
	 * @var $returnValue
	 * @type mixed
	 */
	protected $returnValue;

	public function __construct($methodName)
	{
		$this->methodName = $methodName;
	}

	/**
	 * Sets the content for the return value
	 *
	 * @param mixed $value The return value
	 */
	public function setContent($value) {
		$this->returnValue = $value;
	}

	public function __toString()
	{
		return json_encode(array(
			'method' => 'ajax_' . $this->methodName,
			'value' => $this->returnValue
		));
	}
}