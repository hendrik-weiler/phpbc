<?php

namespace cssparser;

require_once 'Parser.php';

class Document
{
	private $parser;

	private $lexer;

	private $text;

	public $definitions;

	public function __construct($text='')
	{
		$this->lexer = new Lexer($text);
		$this->parser = new Parser($this->lexer);
	}

	public function generateFunc(&$css, &$values) {

		foreach ($values as $funcName => $args) {
			$css .= $funcName . '(';
			foreach($args as $arg) {
				if(is_array($arg)) {
					$this->generateFunc($css, $arg);
				} else {
					if(preg_match('#^!@#', $arg)) {
						$css .= '"' . substr($arg, 2) . '" ';
					} else {
						$css .= $arg . ' ';
					}
				}
			}
			$css .= ') ';
		}

	}

	public function set($selector, $prop, $value) {
		if(!isset($this->definitions[$selector])) {
			$this->definitions[$selector] = array();
		}
		$split = explode(' ',$value);
		$this->definitions[$selector][$prop] = $split;
	}

	public function toCSS() {
		$css = '';
		foreach ($this->definitions as $selector => $defs) {
			$css .= $selector . '{' . PHP_EOL;
			foreach($defs as $name => $value) {
				$css .= $name . ':';
				foreach ($value as $varValue) {
					if(is_array($varValue)) {
						$this->generateFunc($css, $varValue);
						$css .= ';' . PHP_EOL;
					} else {
						$css .= $varValue . ' ';
					}
				}
				$css .= ';';
			}
			$css .= PHP_EOL . '}' . PHP_EOL;
		}
		return $css;
	}

	public function parse() {
		$this->definitions = $this->parser->parse();
		return $this->definitions;
	}
}