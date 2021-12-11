<?php

namespace xmlparser;

require_once 'Node.php';
require_once 'Comment.php';

class Parser
{
	public $current_token = null;

	public $lexer;

	private $document;

	private $gotRootTag = false;

	public $declarations = array();

	public $doctypes = array();

	public $nodes = array();

	public $declarationCallbable;

	public function __construct($lexer, $document)
	{
		$this->lexer = $lexer;
		$this->document = $document;
		$this->current_token = $this->lexer->get_next_token();
	}

	public function error($msg='') {
		$this->lexer->error($msg);
	}

	public function setOnDeclaration(callable $func) {
		$this->declarationCallbable = $func;
	}

	public function eat($type) {
		if($this->current_token->type == $type) {
			$this->current_token = $this->lexer->get_next_token();
		} else {
			$this->error();
		}
	}

	public function attribute() {
		$key = $this->current_token->value;
		$value = $key;
		$this->eat(Type::ID);
		if($this->current_token->type == Type::EQUAL) {
			$this->eat(Type::EQUAL);
			if($this->current_token->type == Type::SINGLE_QUOTE) {
				$this->eat(Type::SINGLE_QUOTE);
			} else {
				$this->eat(Type::QUOTE);
			}
			if($this->current_token->type == Type::SINGLE_QUOTE) {
				$this->eat(Type::SINGLE_QUOTE);
			} else if($this->current_token->type == Type::QUOTE) {
				$this->eat(Type::QUOTE);
			} else {
				$value = $this->current_token->value;
				$this->eat(Type::VALUE);
				if($this->current_token->type == Type::SINGLE_QUOTE) {
					$this->eat(Type::SINGLE_QUOTE);
				} else {
					$this->eat(Type::QUOTE);
				}
			}
		}

		return array(
			'key' => $key,
			'value' => $value
		);
	}

	public function declaration() {

		$name = $this->current_token->value;
		$index = count($this->declarations);
		$this->declarations[] = array('__type__' => $name);
		$this->eat(Type::ID);

		while ($this->current_token->type != Type::DECLARATION_END) {

			$var = $this->attribute();
			$this->declarations[$index][$var['key']] = $var['value'];

		}

		if(is_callable($this->declarationCallbable)) {
			call_user_func($this->declarationCallbable,
				$name,
				$this->declarations[$index],
				$this);
		}

		$this->eat(Type::DECLARATION_END);

	}

	public function node(&$children, $parent) {
		$this->eat(Type::TAG_START);
		$tagName = $this->current_token->value;
		$this->eat(Type::ID);
		$attributes = array();
		while ($this->current_token->type != Type::TAG_END
			&& $this->current_token->type != Type::TAG_SLASH_END) {

			$var = $this->attribute();
			$attributes[$var['key']] = $var['value'];
		}

		if($this->current_token->type == Type::TAG_SLASH_END) {
			$node = new Node($tagName, $attributes, $this->document);
			$node->parentNode = $parent;
			$children[$node->id] = $node;
			$this->eat(Type::TAG_SLASH_END);
		} else {
			$this->eat(Type::TAG_END);
			$node = new Node($tagName, $attributes, $this->document);
			$node->parentNode = $parent;
			$node->setContent(ltrim($this->current_token->value));
			if($this->current_token->type == Type::VALUE) {
				$this->eat(Type::VALUE);
			}

			while (in_array($this->current_token->type, array(Type::TAG_START, Type::COMMENT_START, Type::DECLARATION_START))) {
				if($this->current_token->type == Type::TAG_START) {
					$this->node($node->children, $node);
					if($this->current_token->type == Type::VALUE) {
						$value = ($this->current_token->value);
						if(strlen(trim($value)) > 0) {
							$node->appendContent('{{__node__}}' . $value);
						}
						$this->eat(Type::VALUE);
					}
				}
				if($this->current_token->type == Type::COMMENT_START) {
					$this->eat(Type::COMMENT_START);
					$comment = new Comment($this->current_token->value);
					$node->children[$comment->id] = $comment;
					$this->eat(Type::VALUE);
					$this->eat(Type::COMMENT_END);
				}
				if($this->current_token->type == Type::DECLARATION_START) {
					$this->eat(Type::DECLARATION_START);
					$this->declaration();
				}
			}
			$children[$node->id] = $node;
			$this->eat(Type::TAG_SLASH_START);
			$endTagName = $this->current_token->value;
			$this->eat(Type::ID);
			$this->eat(Type::TAG_END);
		}
	}

	public function doctype_data() {
		if($this->current_token->type == Type::QUOTE) {
			$this->eat(Type::QUOTE);
			$value = $this->current_token->value;
			$type = Type::VALUE;
			$this->eat(Type::VALUE);
			$this->eat(Type::QUOTE);
			return $value;
		} else if($this->current_token->type == Type::ID) {
			$value = $this->current_token->value;
			$type = Type::ID;
			$this->eat(Type::ID);
			return array(
				'value' => $value,
				'type' => $type
			);
		}
	}

	public function doctypes() {

		$name = $this->current_token->value;
		$index = count($this->doctypes);
		$this->doctypes[] = array('name' => $name, 'data' => array());
		$this->eat(Type::ID);

		while ($this->current_token->type != Type::TAG_END) {

			$data = $this->doctype_data();
			$this->doctypes[$index]['data'][] = $data;

		}

		$this->eat(Type::TAG_END);
		if($this->current_token->type == Type::VALUE) {
			$this->eat(Type::VALUE);
		}
	}

	public function parse() {

		while (in_array($this->current_token->type, array(
			Type::DECLARATION_START, Type::COMMENT_START, Type::TAG_START, Type::DOCTYPE_START))) {

			$token = $this->current_token;
			if($token->type == Type::DECLARATION_START) {
				$this->eat(Type::DECLARATION_START);
				$this->declaration();
			}
			if($token->type == Type::DOCTYPE_START) {
				$this->eat(Type::DOCTYPE_START);
				$this->doctypes();
			}
			if($token->type == Type::TAG_START && $this->gotRootTag) {
				$this->error("Only one root tag is allowed.");
			}
			if($token->type == Type::TAG_START && !$this->gotRootTag) {
				$this->node($this->nodes, null);
				if($this->current_token->type == Type::VALUE) {
					$this->eat(Type::VALUE);
				}
				$this->gotRootTag = true;
			}
			if($token->type == Type::COMMENT_START) {
				$this->eat(Type::COMMENT_START);
				$comment = new Comment($this->current_token->value);
				$this->nodes[$comment->id] = $comment;
				if($this->current_token->type == Type::VALUE) {
					$this->eat(Type::VALUE);
				}
				$this->eat(Type::COMMENT_END);
			}
		}

	}
}