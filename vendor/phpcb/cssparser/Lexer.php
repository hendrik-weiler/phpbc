<?php

namespace cssparser;

require_once 'Token.php';
require_once 'Type.php';

class Lexer
{
	private $pos;

	private $line;

	private $linePos;

	private $current_char;

	private $text;

	private $inValue = false;

	private $inBrackets = false;

	private $cssSelectorRX = '#[\.\#a-zA-Z0-9\-\:\-\% \[\]\"\=\,]#';

	private $cssVarDefinitionRX = '#[\.\#a-zA-Z0-9\-\-\% ]#';

	private $cssQuotesRX = '#[^ ]*#';

	private $inComment = false;

	private $inQuotes = false;

	public function __construct($text)
	{
		$this->text = $text;
		$this->pos = 0;
		$this->linePos = 0;
		$this->line = 1;
	}

	public function error() {
		throw new \Exception(
			'Parse error at line: ' . $this->line . ', pos: '
			. $this->linePos . ', char: ' . $this->current_char);
	}

	public function addPos() {
		$this->pos += 1;
		$this->linePos += 1;
	}

	public function minusPos() {
		$this->pos -= 1;
		$this->linePos -= 1;
	}

	public function advance() {
		$this->addPos();
		$this->current_char = $this->text[$this->pos];
	}

	public function peek($number=1) {
		if($this->pos+$number >= strlen($this->text)) {
			return '';
		}
		return $this->text[$this->pos + $number];
	}

	public function _id() {
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

	public function _comment() {
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