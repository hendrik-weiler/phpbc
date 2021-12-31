<?php

namespace poparser;

require_once 'Token.php';
require_once 'Type.php';

/**
 * The lexer class
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Lexer
 * @namespace poparser
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
	 * Returns the text
	 *
	 * @var $text
	 * @type string
	 * @memberOf Lexer
	 * @private
	 */
	private $text;

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
	 * Returns the current line
	 *
	 * @var $line
	 * @type int
	 * @memberOf Lexer
	 * @private
	 */
	private $line;

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
		$this->line = 1;
		$this->linePos = 1;
	}

	public function advance() {
		$this->add();
		$this->current_char = $this->text[$this->pos];
	}

	/**
	 * Gets the next x characters and returns the summary as string
	 *
	 * @param int $charCount The count to look ahead
	 * @memberOf Lexer
	 * @method advanceReturn
	 */
	public function advanceReturn($charCount) {
		$result = '';
		for($i=0; $i < $charCount; ++$i) {

			if($this->pos + $i > strlen($this->text)) {
				return $result;
			}

			$result .= $this->text[$this->pos + $i];

		}
		return $result;
	}

	/**
	 * Adds the current position +x
	 *
	 * @param int $plusPos (optional) The amount to add
	 * @memberOf Lexer
	 * @method add
	 */
	public function add($plusPos=1) {
		$this->pos += $plusPos;
		$this->linePos += $plusPos;
	}

	/**
	 * Throws an error
	 *
	 * @throws \Exception
	 * @memberOf Lexer
	 * @method error
	 */
	public function error() {
		throw new \Exception("Parser error at line: " . $this->line . ", Pos: " . $this->linePos . ', Char: ' . $this->current_char);
	}

	/**
	 * Fetches a comment
	 *
	 * @memberOf Lexer
	 * @method fetchComment
	 */
	public function fetchComment() {
		$result = '';
		while ($this->current_char != "\n") {
			$result .= $this->current_char;
			$this->advance();
		}
		return trim($result);
	}

	/**
	 * Fetches a single value in quotes
	 *
	 * @memberOf Lexer
	 * @method fetchSingleValue
	 */
	public function fetchSingleValue() {
		$result = '';
		$this->advance();
		while (true) {
			$result .= $this->current_char;
			$this->advance();

			if($this->current_char == '"'
				&& $this->text[$this->pos-1] != '\\') {
				$this->advance();
				break;
			}

		}
		return trim($result);
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

		if($this->current_char == PHP_EOL) {
			$this->add();
			$this->line += 1;
			$this->linePos = 1;
			return new Token(Type::NEW_LINE,null);
		}

		if($this->current_char == ' ') {
			$this->add();
			return $this->get_next_token();
		}

		if($this->current_char == '#') {
			$this->advance();
			if($this->current_char == '.') {
				$this->advance();
				return new Token(Type::COMMENT_EXTRACTED,$this->fetchComment());
			} else if($this->current_char == ':') {
				$this->advance();
				return new Token(Type::COMMENT_REFERENCE,$this->fetchComment());
			} else if($this->current_char == ',') {
				$this->advance();
				return new Token(Type::COMMENT_FLAG,$this->fetchComment());
			} else {
				return new Token(Type::COMMENT,$this->fetchComment());
			}
		}

		if($this->current_char == '"') {
			// multi line
			if($this->advanceReturn(2) == '""') {
				$this->add(2);
				return new Token(Type::MULTILINE_START, null);
			} else {
				return new Token(Type::VALUE, $this->fetchSingleValue());
			}
		}

		if($this->current_char == 'm'
			&& $this->advanceReturn(5) == 'msgid') {
			$this->add(5);
			return new Token(Type::MSGID, null);
		}

		if($this->current_char == 'm'
			&& $this->advanceReturn(6) == 'msgstr') {
			$this->add(6);
			return new Token(Type::MSGSTR, null);
		}

		if($this->current_char == 'm'
			&& $this->advanceReturn(7) == 'msgctxt') {
			$this->add(7);
			return new Token(Type::MSGCONTEXT, null);
		}


		$this->error();
	}
}