<?php

namespace namespaces\form;

use renderer\Component;

class TextBox extends Component
{
	public function __construct()
	{
		parent::__construct('textbox');
	}

	public function render()
	{
		$this->replaceTagName('input');
		$this->node->setAttribute('type','text');
		$this->node->setAttribute('class', 'textbox');
		$this->addJavaScript('textbox/textbox.js');
		$this->addStyleSheet('textbox/textbox.css');
	}
}