<?php

namespace renderer;

use xmlparser\Document;

/**
 * The renderer namespace class
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class RendererNamespace
 * @namespace renderer
 */
class RendererNamespace
{
	/**
	 * Returns the name of the namespace
	 *
	 * @var $name
	 * @type string
	 * @memberOf RendererNamespace
	 */
	public $name;

	/**
	 * Returns a map of components
	 *
	 * @var $components
	 * @type array
	 * @memberOf RendererNamespace
	 */
	public $components = array();

	/**
	 * Returns a Document instance
	 *
	 * @var $document
	 * @type xmlparser.Document
	 * @memberOf RendererNamespace
	 */
	public $document;

	/**
	 * Returns the component path
	 *
	 * @var $componentPath
	 * @type string
	 * @memberOf RendererNamespace
	 */
	public $componentPath;

	/**
	 * The constructor
	 *
	 * @param string $name The namespace name
	 * @memberOf RendererNamespace
	 * @method __construct
	 * @constructor
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * Sets the namespace path
	 *
	 * @param string $path The path
	 * @memberOf RendererNamespace
	 * @method setNamespacePath
	 */
	public function setNamespacePath($path) {
		$this->componentPath = $path . '/' . $this->name . '/components/';
	}

	/**
	 * Adds a component
	 *
	 * @param string $component The component class identifier
	 * @memberOf RendererNamespace
	 * @method addComponent
	 */
	public function addComponent($component) {
		$this->components[$component] = $component;
	}
}