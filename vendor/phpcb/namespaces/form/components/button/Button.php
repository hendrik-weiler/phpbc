<?php

namespace namespaces\form;

use renderer\Component;

class Button extends Component
{
	public function __construct()
	{
		parent::__construct('button');
	}

	public function render()
	{
		$this->setEvent('onclick');
		$this->replaceTagName('button');
		$this->node->setAttribute('type','submit');
		$this->node->setAttribute('class', 'button');
		$this->addJavaScript('button/button.js');
		$this->addStyleSheet('button/button.css');
	}
}