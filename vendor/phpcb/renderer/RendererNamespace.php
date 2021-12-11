<?php

namespace renderer;

class RendererNamespace
{
	public $name;

	public $components = array();

	public $document;

	public $componentPath;

	public function __construct($name)
	{
		$this->name = $name;
	}

	public function setNamespacePath($path) {
		$this->componentPath = $path . '/' . $this->name . '/components/';
	}

	public function addComponent($component) {
		$this->components[$component] = $component;
	}
}