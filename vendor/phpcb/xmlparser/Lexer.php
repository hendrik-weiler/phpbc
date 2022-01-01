<?php

namespace xmlparser;

use function Sodium\add;

require_once 'Type.php';
require_once 'Token.php';

/**
 * This tokenizes a given string
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Lexer
 * @namespace xmlparser
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
	 * Returns the line number
	 *
	 * @var $line
	 * @type int
	 * @memberOf Lexer
	 * @private
	 */
	private $line;

	/**
	 * Returns the line position
	 *
	 * @var $linePos
	 * @type int
	 * @memberOf Lexer
	 * @private
	 */
	private $linePos;

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
	 * Returns the current char
	 *
	 * @var $current_char
	 * @type string
	 * @memberOf Lexer
	 * @private
	 */
	private $current_char;

	/**
	 * Returns if the current pos is after the end tag
	 *
	 * @var $afterTagEnd
	 * @type bool
	 * @memberOf Lexer
	 * @private
	 */
	private $afterTagEnd = false;

	/**
	 * Returns if the comment starts
	 *
	 * @var $commentStart
	 * @type bool
	 * @memberOf Lexer
	 * @private
	 */
	private $commentStart = false;

	/**
	 * Returns if the position is in tag
	 *
	 * @var $inTag
	 * @type bool
	 * @memberOf Lexer
	 * @private
	 */
	private $inTag = false;

	/**
	 * Returns if the position is inside a single quote
	 *
	 * @var $inSingleQuotes
	 * @type bool
	 * @memberOf Lexer
	 * @private
	 */
	private $inSingleQuotes = false;

	/**
	 * Returns if the position is inside a double quote
	 *
	 * @var $inDoubleQuotes
	 * @type bool
	 * @memberOf Lexer
	 * @private
	 */
	private $inDoubleQuotes = false;

	/**
	 * Returns if a root element was already entered
	 *
	 * @var $inRootTag
	 * @type bool
	 * @memberOf Lexer
	 * @private
	 */
	private $inRootTag = false;

	/**
	 * The constructor
	 *
	 * @param string $text The text
	 * @memberOf Lexer
	 * @method __construct
	 */
	public function __construct($text)
	{
		$this->pos = 0;
		$this->line = 1;
		$this->linePos = 1;
		$this->text = $text;
	}

	/**
	 * Peeks 1 or more positions into the text
	 *
	 * @param int $num The peek number
	 * @return string
	 * @memberOf Lexer
	 * @method peek
	 */
	public function peek($num = 1) {
		$pos = $this->pos + $num;
		if($pos > strlen($this->text)) {
			return '';
		}
		return $this->text[$pos];
	}

	/**
	 * Inserts text into the current text at the current position
	 *
	 * @param string $text The text to insert
	 * @memberOf Lexer
	 * @method insertText
	 */
	public function insertText($text) {
		$this->insertTextAtPos($this->pos, $text);
	}

	/**
	 * Insert text into the current text at specified position
	 *
	 * @param int $pos The position to insert
	 * @param string $text The text to insert
	 * @memberOf Lexer
	 * @method insertTextAtPos
	 */
	public function insertTextAtPos($pos, $text) {
		$begin = substr($this->text, 0, $pos+0);
		$end = substr($this->text, $pos+0);
		$this->text = $begin . $text . $end;
	}

	/**
	 * Adds the current position +1
	 *
	 * @memberOf Lexer
	 * @method addPos
	 */
	public function addPos() {
		$this->pos += 1;
		$this->linePos += 1;
	}

	/**
	 * Advance one position forward
	 *
	 * @memberOf Lexer
	 * @method advance
	 */
	public function advance() {
		$this->addPos();
		if($this->pos >= strlen($this->text)) {
			$this->current_char = '';
		} else {
			$this->current_char = $this->text[$this->pos];
		}
	}

	/**
	 * Collect an id
	 *
	 * @return string
	 * @memberOf Lexer
	 * @method _id
	 */
	public function _id() {
		$result = '';

		while (preg_match('#[a-zA-Z0-9\-\_\:]#',$this->current_char)) {

			if($this->pos >= strlen($this->text)) {
				break;
			}

			$result .= $this->current_char;
			$this->advance();

		}

		return $result;
	}

	/**
	 * Collect an attribute value
	 *
	 * @return string
	 * @memberOf Lexer
	 * @method _attribute_value
	 */
	public function _attribute_value() {
		$result = '';
		$breakChar = '"';

		if($this->inSingleQuotes) {
			$breakChar = "'";
		}

		while ($this->current_char != $breakChar) {

			if($this->pos >= strlen($this->text)) {
				break;
			}

			$result .= $this->current_char;
			$this->advance();

		}

		return $result;
	}

	/**
	 * Collect a comment value
	 *
	 * @return string
	 * @memberOf Lexer
	 * @method _comment_value
	 */
	public function _comment_value() {
		$result = '';

		while (true) {

			if($this->pos >= strlen($this->text)) {
				break;
			}

			if($this->current_char == '-'
				&& $this->peek() == '-'
				&& $this->peek(2) == '>') {
				break;
			}

			$result .= $this->current_char;
			$this->advance();

		}

		return $result;
	}

	/**
	 * Collect a content value
	 *
	 * @return string
	 * @memberOf Lexer
	 * @method _content_value
	 */
	public function _content_value() {
		$result = '';

		while ($this->current_char != '<') {

			if($this->pos >= strlen($this->text)) {
				break;
			}

			$result .= $this->current_char;
			$this->advance();

		}

		return $result;
	}

	/**
	 * Throws an error message
	 *
	 * @param string $msg The message
	 * @throws \Exception
	 * @memberOf Lexer
	 * @method error
	 */
	public function error($msg='') {
		$before = '';
		$after = '';
		for($i=$this->pos-20; $i < $this->pos - 1; ++$i) {
			$before .= $this->text[$i];
		}
		for($i=$this->pos; $i < $this->pos + 20; ++$i) {
			if($i >= strlen($this->text)) break;
			$after .= $this->text[$i];
		}
		throw new \Exception($msg . PHP_EOL . 'Parse error at line: ' . $this->line . ', pos: ' .
			$this->linePos . ', char: ' . $this->current_char . ', line : ...' . $before . '(' . $this->current_char . ')' . $after . '...');
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
			return new Token(Type::EOF,null);
		}

		$this->current_char = $this->text[$this->pos];

		if( ($this->current_char == ' '
			|| $this->current_char == "\t")
			&& !$this->afterTagEnd) {
			$this->addPos();
			return $this->get_next_token();
		}

		if($this->current_char == '"' && $this->inTag && !$this->inSingleQuotes) {
			$this->addPos();
			$this->inSingleQuotes = false;
			$this->inDoubleQuotes = !$this->inDoubleQuotes;
			return new Token(Type::QUOTE, null);
		}

		if($this->current_char == "'" && $this->inTag && !$this->inDoubleQuotes) {
			$this->addPos();
			$this->inSingleQuotes = !$this->inSingleQuotes;
			$this->inDoubleQuotes = false;
			return new Token(Type::SINGLE_QUOTE, null);
		}

		if($this->inSingleQuotes || $this->inDoubleQuotes) {
			$value = $this->_attribute_value();
			return new Token(Type::VALUE, $value);
		}

		if($this->current_char == '-'
			&& $this->peek() == '-'
			&& $this->peek(2) == '>') {
			$this->addPos();
			$this->addPos();
			$this->addPos();
			$this->commentStart = false;
			return new Token(Type::COMMENT_END,null);
		}

		if($this->commentStart) {
			$value = $this->_comment_value();
			return new Token(Type::VALUE, $value);
		}

		if($this->current_char == '<') {
			$this->afterTagEnd = false;
			if($this->peek() == '!'
				&& $this->peek(2) == '-'
				&& $this->peek(3) == '-') {
				$this->addPos();
				$this->addPos();
				$this->addPos();
				$this->addPos();
				$this->commentStart = true;
				return new Token(Type::COMMENT_START,null);
			} else if($this->peek() == '!') {
				$this->addPos();
				$this->addPos();
				$this->inTag = true;
				return new Token(Type::DOCTYPE_START, null);
			} else if($this->peek() == '?') {
				$this->addPos();
				$this->addPos();
				$this->inTag = true;
				return new Token(Type::DECLARATION_START, null);
			} else if($this->peek() == '/') {
				$this->addPos();
				$this->addPos();
				return new Token(Type::TAG_SLASH_START, null);
			} else {
				$this->addPos();
				$this->inTag = true;
				$this->inRootTag = true;
				return new Token(Type::TAG_START, null);
			}
		}

		if($this->afterTagEnd) {
			$value = $this->_content_value();
			return new Token(Type::VALUE, $value);
		}

		if($this->current_char == "\n") {
			$this->linePos = 1;
			$this->line += 1;
			$this->addPos();
			return $this->get_next_token();
		}

		if($this->current_char == '/'
			&& $this->peek() == '>') {
			$this->addPos();
			$this->addPos();
			$this->afterTagEnd = true;
			return new Token(Type::TAG_SLASH_END, null);
		}

		if($this->current_char == '>') {
			$this->addPos();
			$this->inTag = false;
			$this->afterTagEnd = true;
			return new Token(Type::TAG_END, null);
		}

		if($this->current_char == '?') {
			if($this->peek() == '>') {
				$this->addPos();
				$this->addPos();
				if($this->inRootTag) {
					$this->afterTagEnd = true;
				}
				return new Token(Type::DECLARATION_END, null);
			}
		}

		if($this->current_char == '=' && $this->inTag) {
			$this->addPos();
			return new Token(Type::EQUAL, null);
		}

		if(preg_match('#[a-zA-Z0-9\-\_\:]#', $this->current_char)) {
			$id = $this->_id();
			return new Token(Type::ID, $id);
		}

		$this->error();
	}
}