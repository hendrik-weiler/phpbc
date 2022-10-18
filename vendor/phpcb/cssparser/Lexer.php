<?php

namespace cssparser;

require_once 'Token.php';
require_once 'Type.php';

/**
 * The lexer class
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Lexer
 * @namespace cssparser
 */
class Lexer
{
	/**
	 * Returns the current position
	 *
	 * @var $pos
	 * @type int
	 * @memberOf Lexer
	 * @private
	 */
	private $pos;

	/**
	 * Returns the current line
	 *
	 * @var $line
	 * @type int
	 * @memberOf Lexer
	 * @private
	 */
	private $line;

	/**
	 * Returns the current line position
	 *
	 * @var $linePos
	 * @type int
	 * @memberOf Lexer
	 * @private
	 */
	private $linePos;

	/**
	 * Returns the current char
	 *
	 * @var $current_char
	 * @type string
	 * @memberOf Lexer
	 * @private
	 */
	private $current_char;

	/**
	 * Returns the text
	 *
	 * @var $text
	 * @type string
	 * @memberOf Lexer
	 * @private
	 */
	private $text;

	/**
	 * Returns if the position is in a value
	 *
	 * @var $inValue
	 * @type bool
	 * @memberOf Lexer
	 * @private
	 */
	private $inValue = false;

	/**
	 * Returns if the position is in brackets
	 *
	 * @var $inBrackets
	 * @type bool
	 * @memberOf Lexer
	 * @private
	 */
	private $inBrackets = false;

	/**
	 * Returns the css selector regex
	 *
	 * @var $cssSelectorRX
	 * @type string
	 * @private
	 * @memberOf Lexer
	 */
	private $cssSelectorRX = '#[\.\#a-zA-Z0-9\-\:\-\% \[\]\"\=\,]#';

	/**
	 * Returns the css variable selector regex
	 *
	 * @var $cssVarDefinitionRX
	 * @type string
	 * @memberOf Lexer
	 * @private
	 */
	private $cssVarDefinitionRX = '#[\.\#a-zA-Z0-9\-\-\% ]#';

	/**
	 * Returns the css quotes regex
	 *
	 * @var $cssQuotesRX
	 * @type string
	 * @memberOf Lexer
	 * @private
	 */
	private $cssQuotesRX = '#[^ ]*#';

	/**
	 * Returns if the position is in a comment
	 *
	 * @var $inComment
	 * @type bool
	 * @memberOf Lexer
	 * @private
	 */
	private $inComment = false;

	/**
	 * Returns if the position is in quotes
	 *
	 * @var $inQuotes
	 * @type bool
	 * @memberOf Lexer
	 * @private
	 */
	private $inQuotes = false;

	/**
	 * The constructor
	 *
	 * @param string $text The text
	 * @memberOf Lexer
	 * @constructor
	 * @method __construct
	 */
	public function __construct($text)
	{
		$this->text = $text;
		$this->pos = 0;
		$this->linePos = 0;
		$this->line = 1;
	}

	/**
	 * Throws an error
	 *
	 * @throws \Exception
	 * @memberOf Lexer
	 * @method error
	 */
	public function error() {
		throw new \Exception(
			'Parse error at line: ' . $this->line . ', pos: '
			. $this->linePos . ', char: ' . $this->current_char);
	}

	/**
	 * Adds the current position +1
	 *
	 * @memberOf Lexer
	 * @method addPos
	 * @protected
	 */
	protected function addPos() {
		$this->pos += 1;
		$this->linePos += 1;
	}

	/**
	 * Subtracts the current position -1
	 *
	 * @memberOf Lexer
	 * @protected
	 * @method minusPos
	 */
	protected function minusPos() {
		$this->pos -= 1;
		$this->linePos -= 1;
	}

	/**
	 * Advance one position forward
	 *
	 * @memberOf Lexer
	 * @protected
	 * @method advance
	 */
	protected function advance() {
		$this->addPos();
		$this->current_char = $this->text[$this->pos];
	}

	/**
	 * Peeks 1 or more positions into the text
	 *
	 * @param int $num The peek number
	 * @return string
	 * @memberOf Lexer
	 * @method peek
	 * @protected
	 */
	protected function peek($number=1) {
		if($this->pos+$number >= strlen($this->text)) {
			return '';
		}
		return $this->text[$this->pos + $number];
	}

	/**
	 * Collect an id
	 *
	 * @return string
	 * @memberOf Lexer
	 * @method _id
	 * @protected
	 */
	protected function _id() {
		$result = '';
		$rx = $this->cssSelectorRX;
		if($this->inValue && $this->inQuotes) {
			$rx = $this->cssQuotesRX;
		} else if($this->inBrackets && !$this->inValue) {
			$rx = $this->cssVarDefinitionRX;
		}
		while (true) {

			if($this->inQuotes && $this->current_char == '"') {
				break;
			}

			$result .= $this->current_char;
			$this->advance();

			if($this->inBrackets
					&& $this->inValue
					&& !$this->inQuotes
					&& $this->current_char == ' ') {
				break;
			}

			if(!preg_match($rx,$this->current_char)) {
				break;
			}

		}

		return $result;
	}

	/**
	 * Collect a comment
	 *
	 * @return string
	 * @memberOf Lexer
	 * @method _comment
	 * @protected
	 */
	protected function _comment() {
		$result = '';

		while (true) {

			$result .= $this->current_char;
			$this->advance();

			if($this->current_char == '*'
				&& $this->peek() == '/') {
				$this->minusPos();
				break;
			}

		}

		return $result;
	}

	/**
	 * Get the next token in the line
	 *
	 * @return Token
	 * @throws \Exception
	 * @memberOf Lexer
	 * @method get_next_token
	 */
	public function get_next_token() {

		if($this->pos >= strlen($this->text)) {
			return new Token(Type::EOF, null);
		}

		$this->current_char = $this->text[$this->pos];

		$rx = $this->cssSelectorRX;
		if($this->inValue && $this->inQuotes) {
			$rx = $this->cssQuotesRX;
		} else if($this->inBrackets) {
			$rx = $this->cssVarDefinitionRX;
		}

		if($this->current_char == "\n") {
			$this->addPos();
			$this->linePos = 1;
			$this->line += 1;
			return $this->get_next_token();
		}

		if($this->current_char == ' '
			|| $this->current_char == "\t") {
			$this->addPos();
			return $this->get_next_token();
		}

		if($this->inComment) {
			$result = $this->_comment();
			$this->inComment = false;
			return new Token(Type::VALUE, $result);
		}

		if($this->current_char == '{') {
			$this->addPos();
			$this->inBrackets = true;
			return new Token(Type::LBRACKET, null);
		}

		if($this->current_char == '}') {
			$this->addPos();
			$this->inBrackets = false;
			$this->inValue = false;
			return new Token(Type::RBRACKET, null);
		}

		if($this->current_char == '"' && $this->inBrackets && $this->inValue) {
			$this->addPos();
			$this->inQuotes = !$this->inQuotes;
			return new Token(Type::QUOTE, null);
		}

		if(preg_match($rx,$this->current_char) && !$this->inComment) {
			$result = $this->_id();
			if($this->inValue) {
				$type = Type::VALUE;
			} else if(!$this->inBrackets) {
				$type = Type::ID;
			} else {
				$type = Type::KEY;
			}
			return new Token($type, $result);
		}

		if($this->current_char == ':') {
			$this->addPos();
			$this->inValue = true;
			return new Token(Type::COLON, null);
		}

		if($this->current_char == ';') {
			$this->addPos();
			$this->inValue = false;
			return new Token(Type::SEMI, null);
		}

		if($this->current_char == '(') {
			$this->addPos();
			return new Token(Type::LPAREN, null);
		}

		if($this->current_char == ')') {
			$this->addPos();
			return new Token(Type::RPAREN, null);
		}

		if($this->current_char == ',') {
			$this->addPos();
			return new Token(Type::COMMA, null);
		}

		if($this->current_char == '/'
			&& $this->peek() == '*') {
			$this->addPos();
			$this->addPos();
			$this->inComment = true;
			return new Token(Type::COMMENT_START, null);
		}

		if($this->current_char == '*'
			&& $this->peek() == '/') {
			$this->addPos();
			$this->addPos();
			$this->inComment = false;
			return new Token(Type::COMMENT_END, null);
		}

		$this->error();

	}
}