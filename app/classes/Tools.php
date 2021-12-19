<?php

class Tools
{
	public static function generateSessionId() {
		return time() . '-' . uniqid();
	}

	public static function removeCookie($name) {
		setcookie($name, null, -1,'/');
	}

	public static function setCookie($name, $value) {
		setcookie($name, $value, time()+(3600*12),$_SERVER['REQUEST_URI']);
	}

	public static function getCookie($name) {
		if(isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		}
		return null;
	}
}