<?php

namespace renderer;

/**
 * The request class
 *
 * @author Hendrik Weiler
 * @version 1.0
 */
class Request
{
	/**
	 * Gets a value from post/get request
	 *
	 * @param string $name The name
	 * @return mixed|null
	 */
	public function getValue($name) {
		if(isset($_REQUEST[$name])) {
			return $_REQUEST[$name];
		}
		return null;
	}

	/**
	 * Gets all the url segments
	 *
	 * @return false|string[]
	 */
	public function getUrlSegments() {
		return explode('/',$_SERVER['REQUEST_URI']);
	}

	/**
	 * Gets a specific url segment
	 *
	 * @param int $index The index
	 * @return mixed|string
	 */
	public function getUrlSegment($index) {
		$segments = $this->getUrlSegments();
		$result = '';
		if(isset($segments[$index])) {
			$result = $segments[$index];
		}
		return $result;
	}
}