<?php

namespace renderer;

/**
 * The response class
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Response
 * @namespace renderer
 */
class Response
{
	/**
	 * Redirects to a specific path
	 *
	 * @param string $path The path
	 * @memberOf Response
	 * @method redirect
	 */
	public function redirect($path) {
		header('Location: ' . $path);
	}
}