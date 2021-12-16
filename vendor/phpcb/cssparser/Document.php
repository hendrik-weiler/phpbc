<?php

namespace cssparser;

require_once 'Parser.php';

/**
 * The document class for css
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Document
 * @namespace cssparser
 */
class Document
{
	/**
	 * Returns a parser instance
	 *
	 * @var $parser
	 * @type Parser
	 * @memberOf Document
	 * @private
	 */
	private $parser;

	/**
	 * Returns a lexer instance
	 *
	 * @var $lexer
	 * @type Lexer
	 * @memberOf Document
	 * @private
	 */
	private $lexer;

	/**
	 * Returns the css definitions
	 *
	 * @var $definitions
	 * @type array
	 * @memberOf Document
	 */
	public $definitions;

	/**
	 * The constructor
	 *
	 * @param string $text The text to parse
	 * @memberOf Document
	 * @method __construct
	 * @constructor
	 */
	public function __construct($text='')
	{
		$this->lexer = new Lexer($text);
		$this->parser = new Parser($this->lexer);
	}

	/**
	 * Generates a function string
	 *
	 * @param string $css The css string
	 * @param array $values The values
	 * @memberOf Document
	 * @method generateFunc
	 * @protected
	 */
	protected function generateFunc(&$css, &$values) {

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

	/**
	 * Sets a css property on a selector
	 *
	 * @param string $selector The selector
	 * @param string $prop The property name
	 * @param string $value The property value
	 * @memberOf Document
	 * @method set
	 */
	public function set($selector, $prop, $value) {
		if(!isset($this->definitions[$selector])) {
			$this->definitions[$selector] = array();
		}
		$split = explode(' ',$value);
		$this->definitions[$selector][$prop] = $split;
	}

	/**
	 * Generates a css string from the parsed definitions
	 *
	 * @return string
	 * @memberOf Document
	 * @method toCSS
	 */
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

	/**
	 * Parses the css text
	 *
	 * @return array
	 * @throws \Exception
	 * @memberOf Document
	 * @method parse
	 */
	public function parse() {
		$this->definitions = $this->parser->parse();
		return $this->definitions;
	}
}