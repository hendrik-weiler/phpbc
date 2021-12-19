<?php

namespace xmlparser;

require_once 'Lexer.php';
require_once 'Parser.php';

/**
 * Creates a document from input
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Document
 */
class Document
{
	/**
	 * Returns the parser instance
	 *
	 * @memberOf Document
	 * @var $parser
	 * @type Parser
	 * @private
	 */
	private $parser;

	/**
	 * Returns the lexer instance
	 *
	 * @memberOf Document
	 * @var $lexer
	 * @type Lexer
	 * @private
	 */
	private $lexer;

	/**
	 * Returns the root node of the document
	 *
	 * @var $rootNode
	 * @type Node
	 * @memberOf Document
	 */
	public $rootNode;

	/**
	 * Returns a list of declarations
	 *
	 * @var $declarations
	 * @type array
	 * @memberOf Document
	 * @private
	 */
	private $declarations;

	/**
	 * Returns a list of doctypes
	 *
	 * @var $doctypes
	 * @type array
	 * @memberOf Document
	 * @private
	 */
	private $doctypes;

	/**
	 * Returns a id,node map
	 *
	 * @var $ids
	 * @type array
	 * @memberOf Document
	 * @private
	 */
	private $ids = array();

	/**
	 * Returns a map of tagName,Node[]
	 *
	 * @var $tags
	 * @type array
	 * @memberOf Document
	 * @private
	 */
	private $tags = array();

	/**
	 * Returns a map of formName,array
	 *
	 * @var $forms
	 * @type array
	 * @memberOf Document
	 */
	public $forms = array();

	/**
	 * @param $text string The xml to parse
	 *
	 * @constructor
	 * @memberOf Document
	 * @method __construct
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
	 * @memberOf Document
	 * @method createFromHTML
	 */
	public function createFromHTML($html) {
		$res = new Document($html);
		$rootNode = $res->parse();
		return $rootNode;
	}

	/**
	 * Gets called when a declaration is in the text
	 *
	 * @param Declaration $declaration The declaration
	 * @param Parser $parser The parser instance
	 * @memberOf Document
	 * @method declarationCall
	 */
	public function declarationCall(Declaration $declaration, $parser) {

		if($declaration->name == 'crsf-token') {
			$token = time() . '-' . uniqid();
			setcookie('crsf-token', $token, time()+(3600*12),$_SERVER['REQUEST_URI']);
			$parser->lexer->insertText('<input type="hidden" value="' . $token . '" name="crsf-token" />');
		}
		if($declaration->name == 'include') {
			if(!is_null($declaration->getAttribute('page'))) {
				$pagePath = APP_PATH . 'pages/' . $declaration->getAttribute('page');
				if(file_exists($pagePath)) {
					$html = file_get_contents($pagePath);
					foreach ($declaration->getAttributes() as $key => $value) {
						$html = str_replace('{' . $key . '}',$value, $html);
					}
					$parser->lexer->insertText($html);
					$declaration->parentNode->removeChild($declaration);
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
	 *
	 * @method printTokens
	 * @memberOf Document
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
	 * @param Node $node The node
	 * @method indexNodes
	 * @memberOf Document
	 */
	public function indexNodes(&$node) {
		$node->document = $this;
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
			$child->document = $this;
			$this->indexNodes($child);
		}
	}

	/**
	 * Reindex all nodes
	 *
	 * @memberOf Document
	 * @method reIndexNodes
	 */
	public function reIndexNodes() {
		$this->ids = array();
		$this->tags = array();
		$this->forms = array();
		$this->indexNodes($this->rootNode);
	}

	/**
	 * Gets node elements by tag name
	 *
	 * @param $tagName The tag name
	 * @return array
	 * @memberOf Document
	 * @method getElementsByTagName
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
	 * @memberOf Document
	 * @method getElementById
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
	 * @memberOf Document
	 * @method createElement
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
	 * @memberOf Document
	 * @method getDoctypes
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
	 * @memberOf Document
	 * @method getDeclarations
	 */
	public function getDeclarations() {
		return $this->parser->declarations;
	}

	/**
	 * Gets the forms of the document
	 *
	 * @return array
	 * @memberOf Document
	 * @method getForms
	 */
	public function getForms() {
		return $this->forms;
	}

	/**
	 * Gets the tags
	 *
	 * @return array
	 * @memberOf Document
	 * @method getTags
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * Generates a string from a doctype definition
	 *
	 * @param $name string The doctype name
	 * @return string
	 * @memberOf Document
	 * @method generateDoctype
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
	 * @memberOf Document
	 * @method parse
	 */
	public function parse() {
		$this->parser->parse();
		$this->declarations = $this->parser->declarations;
		$this->doctypes = $this->parser->doctypes;
		if(count($this->parser->nodes) > 0) {
			foreach($this->parser->nodes as $node) {
				if(!is_a($node,'xmlparser\Declaration')) {
					$this->rootNode = $node;
					break;
				}
			}
		}
		$this->indexNodes($this->rootNode);
		return $this->rootNode;
	}
}