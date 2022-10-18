<?php

namespace xmlparser;

require_once 'Node.php';
require_once 'Comment.php';
require_once 'Declaration.php';

/**
 * Parses tokens from the lexer
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Parser
 * @namespace xmlparser
 */
class Parser
{
	/**
	 * Returns the current token
	 *
	 * @var $current_token
	 * @type Token|null
	 * @memberOf Parser
	 */
	public $current_token = null;

	/**
	 * Returns the lexer instance
	 *
	 * @var $lexer
	 * @type Lexer
	 * @memberOf Parser
	 */
	public $lexer;

	/**
	 * Returns the document instance
	 *
	 * @var $document
	 * @type Document
	 * @memberOf Parser
	 * @private
	 */
	private $document;

	/**
	 * Returns if a root tag already exist
	 *
	 * @var $gotRootTag
	 * @type bool
	 * @memberOf Parser
	 * @private
	 */
	private $gotRootTag = false;

	/**
	 * Returns a list of delcarations
	 *
	 * @var $declarations
	 * @type array
	 * @memberOf Parser
	 */
	public $declarations = array();

	/**
	 * Returns a list of doctypes
	 *
	 * @var $doctypes
	 * @type array
	 * @memberOf Parser
	 */
	public $doctypes = array();

	/**
	 * Returns a node tree
	 *
	 * @var $nodes
	 * @type array
	 * @memberOf Parser
	 */
	public $nodes = array();

	/**
	 * Returns a callable object for the call_user_func function
	 *
	 * @var $declarationCallbable
	 * @type callable
	 * @memberOf Parser
	 */
	public $declarationCallbable;

	/**
	 * The constructor
	 *
	 * @param Lexer $lexer The lexer instance
	 * @param Document $document The document instance
	 * @memberOf Parser
	 * @method __construct
	 * @constructor
	 */
	public function __construct($lexer, $document)
	{
		$this->lexer = $lexer;
		$this->document = $document;
		$this->current_token = $this->lexer->get_next_token();
	}

	/**
	 * Wrapper function for the lexer error function
	 *
	 * @param string $msg The error message
	 * @throws \Exception
	 * @memberOf Parser
	 * @method error
	 */
	public function error($msg='') {
		$this->lexer->error($msg);
	}

	/**
	 * Sets the on declaration event
	 *
	 * @param callable $func A callable
	 * @memberOf Parser
	 * @method setOnDeclaration
	 */
	public function setOnDeclaration(callable $func) {
		$this->declarationCallbable = $func;
	}

	/**
	 * Consumes the type and goes to the next token
	 *
	 * @param Type $type The type to eat
	 * @throws \Exception
	 * @memberOf Parser
	 * @method eat
	 */
	public function eat($type, $marker=0) {
		if($this->current_token->type == $type) {
			$this->current_token = $this->lexer->get_next_token();
		} else {
			$this->error('Trying to eat "' . $type . '" on "' . $this->current_token->type . '",' . $marker . '.');
		}
	}

	/**
	 * Checks for a well formed attribute sequence and returns it
	 *
	 * @return array
	 * @throws \Exception
	 * @memberOf Parser
	 * @method attribute
	 */
	public function attribute() {
		$key = $this->current_token->value;
		$value = '';
		$this->eat(Type::ID,2);
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

	/**
	 * Checks for a well formed delcaration
	 *
	 * @throws \Exception
	 * @memberOf Parser
	 * @method declaration
	 */
	public function declaration(&$children, &$parent=null) {

		$name = $this->current_token->value;
		$attributes = array();
		$this->eat(Type::ID,1);

		while ($this->current_token->type != Type::DECLARATION_END) {

			$var = $this->attribute();
			$attributes[$var['key']] = $var['value'];

		}

		$decl = new Declaration($name, $attributes,$this->document);
		$decl->parentNode = $parent;
		$children[] = $decl;
		$this->declarations[] = $decl;

		if(is_callable($this->declarationCallbable)) {
			call_user_func($this->declarationCallbable,
				$decl,
				$this);
		}

		$this->eat(Type::DECLARATION_END);
	}

	/**
	 * Checks for a well formed node and adds it to its children
	 *
	 * @param array $children A list of children nodes
	 * @param Node $parent The parent node
	 * @throws \Exception
	 * @memberOf Parser
	 * @method node
	 * @protected
	 */
	protected function node(&$children, $parent) {
		$this->eat(Type::TAG_START);
		$tagName = $this->current_token->value;
		$this->eat(Type::ID,3);
		$attributes = array();
		while ($this->current_token->type != Type::TAG_END
			&& $this->current_token->type != Type::TAG_SLASH_END) {

			$var = $this->attribute();
			$attributes[$var['key']] = $var['value'];
		}

		if($this->current_token->type == Type::TAG_SLASH_END) {
			$node = new Node($tagName, $attributes, $this->document);
			$node->parentNode = $parent;
			$children[] = $node;
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
					$countStart = count($node->children);
					$this->node($node->children, $node);
					$countEnd = count($node->children);
					$nodesAddedCount = $countEnd - $countStart;
					for($i=0; $i < $nodesAddedCount; $i++) {
						$node->appendContent('{{__node__}}');
					}
					if($this->current_token->type == Type::VALUE) {
						$value = ($this->current_token->value);
						if(strlen(trim($value)) > 0) {
							$node->appendContent($value);
						}
						$this->eat(Type::VALUE);
					}
				}
				if($this->current_token->type == Type::COMMENT_START) {
					$this->eat(Type::COMMENT_START);
					$comment = new Comment($this->current_token->value, $this->document);
					$node->children[] = $comment;
					$node->appendContent('{{__node__}}');
					$this->eat(Type::VALUE);
					$this->eat(Type::COMMENT_END);
				}
				if($this->current_token->type == Type::DECLARATION_START) {
					$this->eat(Type::DECLARATION_START);
					$node->appendContent('{{__node__}}');
					$this->declaration($node->children, $node);
					if($this->current_token->type == Type::VALUE) {
						$node->appendContent($this->current_token->value);
						$this->eat(Type::VALUE);
					}
				}
			}
			$children[$node->id] = $node;
			$this->eat(Type::TAG_SLASH_START);
			$endTagName = $this->current_token->value;
			$this->eat(Type::ID);
			$this->eat(Type::TAG_END);
		}
	}

	/**
	 * Parse the data attributes for doctype declaration
	 *
	 * @return array|void
	 * @throws \Exception
	 * @memberOf Parser
	 * @method doctype_data
	 */
	protected function doctype_data() {
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

	/**
	 * Parse a doctype delaration
	 *
	 * @throws \Exception
	 * @memberOf Parser
	 * @method doctypes
	 * @protected
	 */
	protected function doctypes() {

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

	/**
	 * The main parse method
	 *
	 * @throws \Exception
	 * @memberOf Parser
	 * @method parse
	 */
	public function parse() {

		while (in_array($this->current_token->type, array(
			Type::DECLARATION_START, Type::COMMENT_START, Type::TAG_START, Type::DOCTYPE_START))) {

			$token = $this->current_token;
			if($token->type == Type::DECLARATION_START) {
				$this->eat(Type::DECLARATION_START);
				$this->declaration($this->nodes);
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