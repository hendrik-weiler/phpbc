<?php

namespace xmlparser;

/**
 * The node class
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Node
 * @namespace xmlparser
 */
class Node
{
	/**
	 * Returns the unique id of the node
	 *
	 * @type string
	 * @var $id
	 * @memberOf Node
	 */
	public $id;

	/**
	 * Returns the tag name
	 *
	 * @var $name
	 * @type string
	 * @memberOf Node
	 */
	public $name = '';

	/**
	 * Returns the parent node
	 *
	 * @var $parentNode
	 * @type Node|null
	 * @memberOf Node
	 */
	public $parentNode;

	/**
	 * Returns a map of child nodes
	 *
	 * @var $children
	 * @type array
	 * @memberOf Node
	 */
	public $children = array();

	/**
	 * Returns a map of attributes
	 *
	 * @var $attributes
	 * @type array
	 * @memberOf Node
	 */
	private $attributes = array();

	/**
	 * Returns the content of the node
	 *
	 * @var $content
	 * @type string
	 * @memberOf Node
	 */
	protected $content = '';

	/**
	 * Returns the instance of the document
	 *
	 * @var $document
	 * @type Document
	 * @memberOf Node
	 */
	protected $document;

	/**
	 * The constructor
	 *
	 * @param string $name The tag name
	 * @param array $attributes A map of attributes
	 * @param Document $document A document instance
	 * @param Node $parent A parent node
	 * @memberOf Node
	 * @method __construct
	 */
	public function __construct($name, $attributes, $document, $parent=null)
	{
		$this->id = uniqid();
		$this->name = $name;
		$this->document = $document;
		$this->attributes = $attributes;
		$this->parentNode = $parent;
	}

	/**
	 * Sets the content
	 *
	 * @param string $text The text
	 * @memberOf Node
	 * @method setContent
	 */
	public function setContent($text) {
		$this->content = ($text);
	}

	/**
	 * Gets the content
	 *
	 * @return string
	 * @memberOf Node
	 * @method getContent
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Adds text to the content
	 *
	 * @param string $text The text
	 * @memberOf Node
	 * @method appendContent
	 */
	public function appendContent($text) {
		$this->content .= ($text);
	}

	/**
	 * Adds an event to the node
	 *
	 * Available:
	 * - click
	 * - ajaxClick
	 *
	 * @param string $name The name of event without 'on'
	 * @param string $funcName The name of the function in the codebehind class
	 * @memberOf Node
	 * @method addEventListener
	 */
	public function addEventListener($name, $funcName) {
		$attributes = array();
		foreach($this->attributes as $key => $value) {
			if($key == $name) continue;
			$attributes[] = $key . '=' . urlencode($value);
		}
		switch ($name) {
			case 'click':
				if($this->name == 'a') {
					$this->attributes['href'] = '{request_url}';
				}
				$this->setAttribute('onclick',"javascript:event.preventDefault();__clickCall('" . $funcName . "','{request_url}', this);return false");
				break;
			case 'ajaxClick':
				$this->setAttribute('onclick',"javascript:event.preventDefault();__ajaxClickCall('" . $funcName . "','{request_url}', this);return false;");
				break;
		}
	}

	/**
	 * Generates an attributes string
	 *
	 * @return string
	 * @memberOf Node
	 * @method generateAttributes
	 * @protected
	 */
	protected function generateAttributes() {
		$result = array();
		foreach($this->attributes as $key => $value) {
			if(preg_match('#\"#', $value)) {
				$result[] = $key . '=\'' . $value . '\'';
			} else {
				$result[] = $key . '="' . $value . '"';
			}
		}
		return implode(' ', $result);
	}

	/**
	 * Gets an attribute
	 *
	 * @param string $name The name of the attribute
	 * @return string|null
	 * @memberOf Node
	 * @method getAttribute
	 */
	public function getAttribute($name) {
		if(isset($this->attributes[$name])) {
			return $this->attributes[$name];
		}
		return null;
	}

	/**
	 * Gets all attributes as map
	 *
	 * @return array
	 * @memberOf Node
	 * @method getAttributes
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * Sets an attribute
	 *
	 * @param string $name The name
	 * @param string $value The value
	 * @memberOf Node
	 * @method setAttribute
	 */
	public function setAttribute($name,$value) {
		$this->attributes[$name] = $value;
		return true;
	}

	/**
	 * Removes an attribute
	 *
	 * @param string $name The name
	 * @return bool
	 * @memberOf Node
	 * @method removeAttribute
	 */
	public function removeAttribute($name) {
		if(isset($this->attributes[$name])) {
			unset($this->attributes[$name]);
			return true;
		}
		return false;
	}

	/**
	 * Adds a node to the children
	 *
	 * @param Node $node The node to append
	 * @memberOf Node
	 * @method appendChild
	 */
	public function appendChild(&$node) {
		if($node instanceof Node) {
			$node->parentNode = $this;
			$this->children[$node->id] = $node;
			$this->document->reIndexNodes();
		}
	}

	/**
	 * Removes a node from the children
	 *
	 * @param Node $node The node
	 * @memberOf Node
	 * @method removeChild
	 */
	public function removeChild(&$node) {
		if(isset($this->children[$node->id])) {
			unset($this->children[$node->id]);
		}
	}

	/**
	 * Gets the css document on a style tag
	 *
	 * @return \cssparser\Document
	 * @throws \Exception
	 * @memberOf Node
	 * @method getCSS
	 */
	public function getCSS() {
		if($this->name == 'style') {
			$doc = new \cssparser\Document($this->content);
			$doc->parse();
			return $doc;
		} else {
			throw new \Exception('You can only get the css from style elements');
		}
	}

	/**
	 * Converts the node with children to xml
	 *
	 * @return string
	 * @memberOf Node
	 * @method toXML
	 */
	public function toXML() {
		$result = '<' . $this->name;
		if(count($this->attributes) > 0) {
			$result .= ' ';
		}
		$result .= $this->generateAttributes();
		if(count($this->children) == 0 && strlen($this->content) == 0
			&& $this->name != 'script'
			&& $this->name != 'textarea') {
			$result .= ' />'. PHP_EOL;
		} else {
			$result .= '>';
			if(strlen($this->content) > 0) {
				$split = explode('{{__node__}}', $this->content);
				$children = array();
				foreach($this->children as $child) {
					$children[] = $child;
				}
				foreach ($split as $index => $text) {
					$result .= $text;
					if(isset($children[$index])) {
						$result .= $children[$index]->toXML();
					}
				}
			} else {
				$result .= PHP_EOL;
				foreach ($this->children as $childNode) {
					$result .= $childNode->toXML();
				}
			}
			$result .= '</' . $this->name . '>';
		}
		return $result;
	}
}