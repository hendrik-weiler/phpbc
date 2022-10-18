<?php

namespace renderer;

use xmlparser\Document;

/**
 * The code behind class
 * This is the base class all controller inherit from
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class CodeBehind
 * @namespace renderer
 */
class CodeBehind
{
	/**
	 * Returns a document instance
	 *
	 * @var $document
	 * @type Document
	 * @memberOf CodeBehind
	 * @protected
	 */
	protected $document;

	/**
	 * The constructor
	 *
	 * @param Document $document The document instance
	 * @memberOf CodeBehind
	 * @method __construct
	 * @constructor
	 */
	public function __construct($document)
	{
		if(!is_null($document)) {
			$this->document = $document;
		}
	}

	/**
	 * Gets called when a get request occurred
	 *
	 * @param Renderer $renderer The renderer instance
	 * @param Request $request The request instance
	 * @param Response $response The response instance
	 * @memberOf CodeBehind
	 * @method get_execute
	 */
	public function get_execute($renderer, Request $request, Response $response) {}

	/**
	 * Gets called when a post request occurred
	 *
	 * @param Renderer $renderer The renderer instance
	 * @param Request $request The request instance
	 * @param Response $response The response instance
	 * @memberOf CodeBehind
	 * @method post_execute
	 */
	public function post_execute($renderer, Request $request, Response $response) {}
}