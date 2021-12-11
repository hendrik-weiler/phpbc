<?php

namespace renderer;

class Response
{
	public function redirect($path) {
		header('Location: ' . $path);
	}
}