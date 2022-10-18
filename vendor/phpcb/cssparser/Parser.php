<?php

namespace cssparser;

require_once 'Lexer.php';

/**
 * The parser class
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Parser
 * @namespace cssparser
 */
class Parser
{
	/**
	 * Returns the current token
	 *
	 * @var $current_token
	 * @type Token
	 * @memberOf Parser
	 * @private
	 */
	private $current_token;

	/**
	 * Returns the lexer instance
	 *
	 * @var $lexer
	 * @type Lexer
	 * @memberOf Parser
	 * @private
	 */
	private $lexer;

	/**
	 * Returns the css definitions
	 *
	 * @var $definitions
	 * @type array
	 * @memberOf Parser
	 */
	public $definitions = array();

	/**
	 * The constructor
	 *
	 * @param Lexer $lexer The lexer instance
	 * @memberOf Parser
	 * @method __construct
	 * @constructor
	 */
	public function __construct($lexer)
	{
		$this->lexer = $lexer;
		$this->current_token = $this->lexer->get_next_token();
	}

	/**
	 * Consumes the type and goes to the next token
	 *
	 * @param Type $type The type to eat
	 * @throws \Exception
	 * @memberOf Parser
	 * @method eat
	 */
	public function eat($type) {
		if($this->current_token->type == $type) {
			$this->current_token = $this->lexer->get_next_token();
		} else {
			$this->lexer->error();
		}
	}

	/**
	 * Collects the values inside parenthesis
	 *
	 * @param array $values A list of values
	 * @return array
	 * @throws \Exception
	 * @memberOf Parser
	 * @method var_values
	 * @protected
	 */
	protected function var_values(&$values) {
		$this->eat(Type::LPAREN);
		$values = $this->attribute_values($values);
		$this->eat(Type::RPAREN);
		return $values;
	}

	/**
	 * Collects the attribute values
	 *
	 * @param array $values The values
	 * @return array
	 * @throws \Exception
	 * @memberOf Parser
	 * @method attribute_values
	 * @protected
	 */
	protected function attribute_values(&$values) {
		while (in_array($this->current_token->type,
			array(Type::VALUE, Type::COMMA, Type::QUOTE))) {

			if($this->current_token->type == Type::COMMA) {
				$this->eat(Type::COMMA);
			} else {
				$value = '';
				if($this->current_token->type == Type::QUOTE) {
					$value .= '!@';
					$this->eat(Type::QUOTE);
				}
				$value .= $this->current_token->value;
				$this->eat(Type::VALUE);
				if($this->current_token->type == Type::QUOTE) {
					$this->eat(Type::QUOTE);
				}
				if($this->current_token->type == Type::LPAREN) {
					$index = count($values);
					$values[$index] = array($value => array());
					$values[$index][$value] = $this->var_values($values[$index][$value]);
				} else {
					$values[] = $value;
				}
			}

		}
		return $values;
	}

	/**
	 * Collects all attributes of a selector
	 *
	 * @param string $selector The css selector
	 * @throws \Exception
	 * @memberOf Parser
	 * @method attributes
	 * @protected
	 */
	protected function attributes($selector) {

		$this->definitions[$selector] = array();
		while (in_array($this->current_token->type, array(Type::KEY, Type::COMMENT_START))) {


			if($this->current_token->type == Type::COMMENT_START) {
				$this->eat(Type::COMMENT_START);
				$values[] = 'COMMENT: ' . $this->current_token->value;
				$this->eat(Type::VALUE);
				$this->eat(Type::COMMENT_END);
			} else {

				$key = $this->current_token->value;
				$this->eat(Type::KEY);
				$this->eat(Type::COLON);

				$tempValues = array();
				$values = $this->attribute_values($tempValues);

				if($this->current_token->type != Type::RBRACKET) {
					$this->eat(Type::SEMI);
				}

				$this->definitions[$selector][$key] = $values;

			}
		}

	}

	/**
	 * Parses the input
	 *
	 * @return array
	 * @throws \Exception
	 * @memberOf Parser
	 * @method parse
	 */
	public function parse() {

		while (in_array($this->current_token->type, array(Type::ID, Type::COMMENT_START))) {

			if($this->current_token->type == Type::COMMENT_START) {
				$this->eat(Type::COMMENT_START);
				$values[] = 'COMMENT: ' . $this->current_token->value;
				$this->eat(Type::VALUE);
				$this->eat(Type::COMMENT_END);
			} else {
				$selector = trim($this->current_token->value);
				$this->eat(Type::ID);
				while (in_array($this->current_token->type, array(Type::ID))) {
					$selector .= trim($this->current_token->value);
					$this->eat(Type::ID);
				}
				$this->eat(Type::LBRACKET);
				$this->attributes($selector);
				$this->eat(Type::RBRACKET);
				}
		}

		return $this->definitions;
	}
}