<?php

/**
 * This class handles ajax requests
 */
class AjaxRequest extends \renderer\Request
{
	/**
	 * Fills the $_REQUEST array with parameters
	 *
	 * @param array $params A map of parameter
	 */
	public function fillValues($params) {
		foreach($params as $key => $value) {
			$_REQUEST[$key] = $value;
		}
	}
}