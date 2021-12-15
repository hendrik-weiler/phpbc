<?php

namespace xmlparser;

require_once 'Lexer.php';
require_once 'Parser.php';

/**
 * Creates a document from input
 *
 * @author Hendrik Weiler
 * @version 1.0
 */
class Document
{
	/**
	 * Returns the parser instance
	 *
	 * @var Parser
	 */
	private $parser;

	/**
	 * Returns the lexer instance
	 *
	 * @var Lexer
	 */
	private $lexer;

	/**
	 * Returns the root node of the document
	 *
	 * @var Node
	 */
	public $rootNode;

	/**
	 * Returns a list of declarations
	 *
	 * @var array
	 */
	private $declarations;

	/**
	 * Returns a list of doctypes
	 *
	 * @var array
	 */
	private $doctypes;

	/**
	 * Returns a id,node map
	 *
	 * @var array
	 */
	private $ids = array();

	/**
	 * Returns a map of tagName,Node[]
	 *
	 * @var array
	 */
	private $tags = array();

	/**
	 * Returns a map of formName,array
	 *
	 * @var array
	 */
	private $forms = array();

	/**
	 * @param $text string The xml to parse
	 */
	public function __construct($text)
	{
		$this->lexer = new Lexer($text);
		$this->parser = new Parser($this->lexer, $this);
		$this->parser->setOnDeclaration(array($this,'declarationCall'));
	}

	/**
	 * Creates nodes from a html string and returns it
	 *
	 * @param string $html The html
	 * @return mixed|Node
	 */
	public function createFromHTML($html) {
		$res = new Document($html);
		$rootNode = $res->parse();
		return $rootNode;
	}

	public function declarationCall($name, $attributes, $parser) {
		if($name == 'include') {
			if(isset($attributes['page'])) {
				$pagePath = APP_PATH . 'pages/' . $attributes['page'];
				if(file_exists($pagePath)) {
					$html = file_get_contents($pagePath);
					foreach ($attributes as $key => $value) {
						$html = str_replace('{' . $key . '}',$value, $html);
					}
					$parser->lexer->insertText($html);
				} else {
					$parser->error('Could not find page in "' . $pagePath . '"');
				}
			} else {
				$parser->error('The include delcaration needs a page attribute.');
			}
		}
	}

	/**
	 * Prints all tokens from the lexer
	 */
	public function printTokens() {
		var_dump($this->parser->current_token);
		$token = $this->lexer->get_next_token();
		var_dump($token);
		while ($token->type != 'EOF') {
			$token = $this->lexer->get_next_token();
			var_dump($token);
		}
	}

	/**
	 * Indexes from a node
	 *
	 * @param $node Node The node
	 */
	public function indexNodes($node) {
		if($id = $node->getAttribute('id')) {
			$this->ids[$id] = $node;
		}
		$tagName = $node->name;
		if(!isset($this->tags[$tagName])) {
			$this->tags[$tagName] = array();
		}
		if($tagName == 'form') {
			if($formName = $node->getAttribute('name')) {
				$this->forms[$formName] = array();
			}
		}
		if( ($tagName == 'input'
			|| $tagName == 'select'
			|| $tagName == 'textarea') && !empty($this->forms) ) {
			while ($formNode = $node->parentNode) {

				if(is_null($formNode)) {
					break;
				}

				if($formNode->name == 'form') {
					if($formName = $formNode->getAttribute('name')) {
						if($nodeName = $node->getAttribute('name')) {
							$this->forms[$formName][$nodeName] = $node;
							break;
						}
					}
				}
			}
		}
		$this->tags[$tagName][] = $node;
		foreach($node->children as $child) {
			$this->indexNodes($child);
		}
	}

	/**
	 * Reindex all nodes
	 */
	public function reIndexNodes() {
		$this->ids = array();
		$this->tags = array();
		$this->indexNodes($this->rootNode);
	}

	/**
	 * Gets node elements by tag name
	 *
	 * @param $tagName The tag name
	 * @return array
	 */
	public function getElementsByTagName($tagName) {
		if(isset($this->tags[$tagName])) {
			return $this->tags[$tagName];
		}
		return array();
	}

	/**
	 * Get a single element from an id
	 *
	 * @param $id string The id
	 * @return mixed|null
	 */
	public function getElementById($id) {
		if(isset($this->ids[$id])) {
			return $this->ids[$id];
		}
		return null;
	}

	/**
	 * Creates an element and returns it
	 *
	 * @param $tagName string The tag name
	 * @return Node
	 */
	public function createElement($tagName) {
		return new Node($tagName, array(), $this);
	}

	/**
	 * Gets the doctype definitions
	 *
	 * Example:
	 * <!DOCTYPE
	 *
	 * @return array
	 */
	public function getDoctypes() {
		return $this->parser->doctypes;
	}

	/**
	 * Gets the declarations
	 *
	 * Example:
	 * <?xml ?>
	 *
	 * @return array
	 */
	public function getDeclarations() {
		return $this->parser->declarations;
	}

	/**
	 * Gets the forms of the document
	 *
	 * @return array
	 */
	public function getForms() {
		return $this->forms;
	}

	/**
	 * Gets the tags
	 *
	 * @return array
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * Generates a string from a doctype definition
	 *
	 * @param $name string The doctype name
	 * @return string
	 */
	public function generateDoctype($name) {
		$result = '';
		foreach($this->doctypes as $doctype) {
			if($doctype['name'] == $name) {
				$result = '<!' . $name . ' ';
				foreach($doctype['data'] as $data) {
					if($data['type'] == Type::ID) {
						$result .= $data['value'];
					}
					if($data['value'] == Type::VALUE) {
						$result .= $data['value'];
					}
				}
				$result .= '>' . PHP_EOL;
				break;
			}
		}
		return $result;
	}

	/**
	 * Parses the document
	 *
	 * @return mixed|Node
	 */
	public function parse() {
		$this->parser->parse();
		$this->declarations = $this->parser->declarations;
		$this->doctypes = $this->parser->doctypes;
		if(count($this->parser->nodes) > 0) {
			foreach($this->parser->nodes as $node) {
				$this->rootNode = $node;
				break;
			}
		}
		$this->indexNodes($this->rootNode);
		return $this->rootNode;
	}
}