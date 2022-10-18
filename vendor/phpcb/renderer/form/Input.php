<?php

namespace renderer;

use xmlparser\Node;

/**
 * The form input class
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Input
 * @namespace renderer
 */
class Input
{
	/**
	 * Returns the node instance
	 *
	 * @var $node
	 * @type Node
	 * @memberOf Input
	 * @private
	 */
	private $node = null;

	/**
	 * Returns the renderer instance
	 *
	 * @var $renderer
	 * @type Renderer
	 * @memberOf Input
	 * @private
	 */
	private $renderer = null;

	/**
	 * Returns the Request instance
	 *
	 * @var $request
	 * @type Request
	 */
	private $request = null;

	/**
	 * The constructor
	 *
	 * @param Node $node The input node
	 * @param Renderer $renderer The renderer instance
	 * @param Request $request The request instance
	 * @memberOf Input
	 * @constructor
	 * @method __construct
	 */
	public function __construct($node, $renderer, $request)
	{
		$this->node = $node;
		$this->renderer = $renderer;
		$this->request = $request;
	}

	/**
	 * Gets the value of the input
	 *
	 * @return string|null
	 * @memberOf Input
	 * @method getValue
	 */
	public function getValue() {
		if($name = $this->node->getAttribute('name')) {
			return $this->request->getValue($name);
		}
		return null;
	}

	/**
	 * Sets the value of the input
	 *
	 * @param string $value The value
	 * @memberOf Input
	 * @method setValue
	 */
	public function setValue($value) {
		if($this->node->name == 'textarea') {
			$this->node->setContent($value);
		} else if($this->node->name == 'select') {
			$this->node->setAttribute('data-init',$value);
		} else {
			$this->node->setAttribute('value',$value);
		}
	}
}