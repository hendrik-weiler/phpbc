<?php

namespace renderer;

/**
 * This class handles ajax requests
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class AjaxRequest
 * @namespace renderer
 * @extends renderer.Request
 */
class AjaxRequest extends \renderer\Request
{
	/**
	 * Fills the $_REQUEST array with parameters
	 *
	 * @param array $params A map of parameter
	 * @memberOf AjaxRequest
	 * @method fillValues
	 */
	public function fillValues($params) {
		foreach($params as $key => $value) {
			$_REQUEST[$key] = $value;
		}
	}
}