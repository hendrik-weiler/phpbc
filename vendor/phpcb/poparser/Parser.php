<?php

namespace poparser;

require_once 'Lexer.php';
require_once 'Translation.php';

/**
 * The parser class
 *
 * Features:
 * - simple one line msgid, msgstr
 * - multiline msgid, msgstr
 * - collects comments, comments references, comments flags, comments extracted in a translation
 * - contexts
 * Missing features:
 * - plural definitions
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Parser
 * @namespace poparser
 */
class Parser
{
	/**
	 * Returns the lexer instance
	 *
	 * @var $lexer
	 * @type Lexer
	 * @memberOf Parser
	 */
	public $lexer;

	/**
	 * Returns the current token
	 *
	 * @var $current_token
	 * @type Token
	 * @memberOf Parser
	 */
	public $current_token;

	/**
	 * Returns the list of translation
	 *
	 * @var $translations
	 * @type array
	 * @memberOf Parser
	 */
	public $translations = array();

	/**
	 * Returns the map of contexes
	 *
	 * @var $context
	 * @type array
	 * @memberOf Parser
	 */
	public $context = array();

	/**
	 * The constructor
	 *
	 * @param string $text The text to parse
	 * @memberOf Parser
	 * @method __construct
	 * @constructor
	 */
	public function __construct($text)
	{
		$this->lexer = new Lexer($text);
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
		if($type == $this->current_token->type) {
			$this->current_token = $this->lexer->get_next_token();
		} else {
			$this->lexer->error();
		}
	}

	/**
	 * Fetches from value tokens a multiline string
	 *
	 * @memberOf Parser
	 * @method fetchMultiline
	 */
	public function fetchMultiline() {
		$result = '';

		while (in_array($this->current_token->type, array(Type::VALUE, Type::NEW_LINE))) {

			if($this->current_token->type == Type::VALUE) {
				$result .= $this->current_token->value;
				$this->eat(Type::VALUE);
			} else {
				$this->eat(Type::NEW_LINE);
			}

		}

		return $result;
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

		$translation = new Translation();

		while (in_array($this->current_token->type, array(
			Type::COMMENT,
			Type::COMMENT_EXTRACTED,
			Type::COMMENT_FLAG,
			Type::COMMENT_REFERENCE,
			Type::MSGID,
			Type::MSGCONTEXT,
			Type::NEW_LINE))) {

			if($this->current_token->type == Type::COMMENT) {
				$translation->comment[] = $this->current_token->value;
				$this->eat(Type::COMMENT);
				$this->eat(Type::NEW_LINE);
			}
			if($this->current_token->type == Type::COMMENT_EXTRACTED) {
				$translation->comment_extracted[] = $this->current_token->value;
				$this->eat(Type::COMMENT_EXTRACTED);
				$this->eat(Type::NEW_LINE);
			}
			if($this->current_token->type == Type::COMMENT_FLAG) {
				$translation->comment_flags[] = $this->current_token->value;
				$this->eat(Type::COMMENT_FLAG);
				$this->eat(Type::NEW_LINE);
			}
			if($this->current_token->type == Type::COMMENT_REFERENCE) {
				$translation->comment_references[] = $this->current_token->value;
				$this->eat(Type::COMMENT_REFERENCE);
				$this->eat(Type::NEW_LINE);
			}
			if($this->current_token->type == Type::MSGCONTEXT) {
				$this->eat(Type::MSGCONTEXT);
				$translation->context = $this->current_token->value;
				if(!isset($this->context[$translation->context])) {
					$this->context[$translation->context] = array();
				}
				$this->context[$translation->context][] = $translation;
				$this->eat(Type::VALUE);
				$this->eat(Type::NEW_LINE);
			}
			if($this->current_token->type == Type::MSGID) {
				$this->eat(Type::MSGID);
				if($this->current_token->type == Type::MULTILINE_START) {
					$this->eat(Type::MULTILINE_START);
					$this->eat(Type::NEW_LINE);
					$translation->base = $this->fetchMultiline();
					$this->eat(Type::MSGSTR);
					$this->eat(Type::MULTILINE_START);
					$this->eat(Type::NEW_LINE);
					$translation->translation = $this->fetchMultiline();
					$this->translations[] = $translation;
					$translation = new Translation();
				} else {
					$translation->base = $this->current_token->value;
					$this->eat(Type::VALUE);
					$this->eat(Type::NEW_LINE);
					$this->eat(Type::MSGSTR);
					$translation->translation = $this->current_token->value;
					$this->eat(Type::VALUE);
					$this->translations[] = $translation;
					$translation = new Translation();
					if($this->current_token->type == Type::NEW_LINE) {
						$this->eat(Type::NEW_LINE);
					}
					if($this->current_token->type == Type::EOF) {
						$this->eat(Type::EOF);
					}
				}
			}
			if($this->current_token->type == Type::NEW_LINE) {
				$this->eat(Type::NEW_LINE);
			}

		}

		return $this->translations;
	}
}