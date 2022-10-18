<?php

namespace namespaces\form;

class Form extends \renderer\Component
{
	public function __construct()
	{
		parent::__construct('form');
	}

	public function render()
	{
		$this->replaceTagName('form');
		$this->node->setAttribute('class', 'form');
		$this->node->setAttribute('action','');
		$this->addJavaScript('form/form.js');
		$this->addStyleSheet('form/form.css');
	}
}