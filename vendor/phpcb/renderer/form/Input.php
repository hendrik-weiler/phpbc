<?php

class Input
{
	private $node = null;

	private $renderer = null;

	private $request = null;

	public function __construct($node, $renderer, $request)
	{
		$this->node = $node;
		$this->renderer = $renderer;
		$this->request = $request;
	}

	public function getValue() {
		if($name = $this->node->getAttribute('name')) {
			return $this->request->getValue($name);
		}
		return null;
	}

	public function setValue($value) {
		$this->node->setAttribute('value',$value);
	}
}