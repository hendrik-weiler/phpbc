<?php

namespace cssparser;

class Token
{
	public $type;

	public $value;

	public function __construct($type, $value)
	{
		$this->type = $type;
		$this->value = $value;
	}
}