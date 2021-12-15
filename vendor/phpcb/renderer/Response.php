<?php

namespace renderer;

/**
 * The response class
 *
 * @author Hendrik Weiler
 * @version 1.0
 */
class Response
{
	/**
	 * Redirects to a specific path
	 *
	 * @param string $path The path
	 */
	public function redirect($path) {
		header('Location: ' . $path);
	}
}