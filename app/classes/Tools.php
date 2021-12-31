<?php

class Tools
{
	public static function generateSessionId() {
		return time() . '-' . uniqid();
	}

	public static function removeCookie($name) {
		setcookie($name, null, -1,'/');
	}

	public static function setCookie($name, $value, $path=null) {
		$uri = $_SERVER['REQUEST_URI'];
		if(preg_match('#index$#',$uri)) {
			$uri = preg_replace('#index$#','', $uri);
		}
		if(!is_null($path)) $uri = $path;
		setcookie($name, $value, time()+(3600*12),$uri);
	}

	public static function getCookie($name) {
		if(isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		}
		return null;
	}
}