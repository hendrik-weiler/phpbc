<?php

namespace renderer;

class Request
{
	public function getValue($name) {
		if(isset($_REQUEST[$name])) {
			return $_REQUEST[$name];
		}
		return null;
	}

	public function getUrlSegments() {
		return explode('/',$_SERVER['REQUEST_URI']);
	}

	public function getUrlSegment($index) {
		$segments = $this->getUrlSegments();
		$result = '';
		if(isset($segments[$index])) {
			$result = $segments[$index];
		}
		return $result;
	}
}