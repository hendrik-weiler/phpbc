<?php

namespace renderer;

use xmlparser\Document;
use xmlparser\Node;

/**
 * The component class
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Component
 * @namespace renderer
 */
class Component
{
	/**
	 * Returns the component name
	 *
	 * @var $name
	 * @type string
	 * @memberOf Component
	 */
	public $name;

	/**
	 * Returns the document instance
	 *
	 * @var $document
	 * @type xmlparser.Document
	 * @memberOf Component
	 */
	public $document;

	/**
	 * Returns the component path
	 *
	 * @var $componentPath
	 * @type string
	 * @memberOf Component
	 */
	public $componentPath;

	/**
	 * Returns the root node of the component
	 *
	 * @var $node
	 * @type Node
	 * @memberOf Component
	 */
	public $node;

	/**
	 * The constructor
	 *
	 * @param string $name The name of the component
	 * @memberOf Component
	 * @method __construct
	 * @constructor
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * Gets the head element of the document
	 *
	 * @return mixed
	 * @throws \Exception
	 * @memberOf Component
	 * @protected
	 * @method getHeadElement
	 */
	protected function getHeadElement() {
		$head = $this->document->getElementsByTagName('head');
		if(count($head) > 0) {
			return $head[0];
		} else {
			throw new \Exception('The head tag couldnt be found.');
		}
	}

	/**
	 * Is a javascript file included
	 *
	 * @param string $path The path to include
	 * @return bool
	 * @memberOf Component
	 * @method isJavaScriptIncluded
	 * @protected
	 */
	protected function isJavaScriptIncluded($path) {
		$tags = $this->document->getTags();
		if(isset($tags['script'])) {
			foreach ($tags['script'] as $tag) {
				$src = $tag->getAttribute('src');
				if($src && $src == $this->componentPath . $path) {
					return true;
				}
			}
			return false;
		}
		return false;
	}

	/**
	 * Is a stylesheet file included
	 *
	 * @param string $path The stylesheet path
	 * @return bool
	 * @memberOf Component
	 * @method isStylesheetIncluded
	 * @protected
	 */
	protected function isStylesheetIncluded($path) {
		$tags = $this->document->getTags();
		if(isset($tags['link'])) {
			foreach ($tags['link'] as $tag) {
				$src = $tag->getAttribute('href');
				if($src && $src == $this->componentPath . $path) {
					return true;
				}
			}
			return false;
		}
		return false;
	}

	/**
	 * Changes the tag name of the root node
	 *
	 * @param string $tagName The new tag name
	 * @memberOf Component
	 * @method replaceTagName
	 */
	public function replaceTagName($tagName) {
		$this->node->name = $tagName;
	}

	/**
	 * Adds a stylesheet to the document
	 *
	 * @param string $path The path
	 * @throws \Exception
	 * @memberOf Component
	 * @method addStyleSheet
	 */
	public function addStyleSheet($path) {
		if($this->isStylesheetIncluded($path)) return;
		if(file_exists($this->componentPath . $path)) {
			$head = $this->getHeadElement();
			$node = new Node('link', array(
				'rel' => 'stylesheet',
				'href' => $this->componentPath .$path
			), $this->document);
			$head->appendChild($node);
		} else {
			throw new \Exception('Cant find stylesheet file from path "' . $path . '"');
		}
	}

	/**
	 * Adds a javascript file to the document
	 *
	 * @param string $path The path
	 * @throws \Exception
	 * @memberOf Component
	 * @method addJavaScript
	 */
	public function addJavaScript($path) {
		if($this->isJavaScriptIncluded($path)) return;
		if(file_exists($this->componentPath . $path)) {
			$head = $this->getHeadElement();
			$node = new Node('script', array(
				'src' => $this->componentPath .$path
			), $this->document);
			$head->appendChild($node);
		} else {
			throw new \Exception('Cant find javascript file from path "' . $path . '"');
		}
	}
}