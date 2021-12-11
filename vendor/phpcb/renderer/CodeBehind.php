<?php

namespace renderer;

class CodeBehind
{
	protected $document;

	public function __construct($document)
	{
		if(!is_null($document)) {
			$this->document = $document;
			$this->assignVariables();
		}
	}

	protected function assignVariables() {
		$inputs = $this->document->getElementsByTagName('input');
		$selects = $this->document->getElementsByTagName('select');
		$textareas = $this->document->getElementsByTagName('textarea');
		foreach ($inputs as $input) {
			if($name = $input->getAttribute('name')) {
				$this->{$name} = $input->getAttribute('value');
			}
		}
		foreach ($selects as $select) {
			if($name = $select->getAttribute('name')) {
				$this->{$name} = $select->getAttribute('value');
			}
		}
		foreach ($textareas as $textarea) {
			if($name = $textarea->getAttribute('name')) {
				$this->{$name} = $textarea->getContent();
			}
		}
	}

	public function get_execute($renderer,$request,$response) {}

	public function post_execute($renderer,$request,$response) {}
}