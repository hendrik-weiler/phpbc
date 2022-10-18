<?php

namespace poparser;

/**
 * A single translation
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Translation
 * @namespace poparser
 */
class Translation
{
	/**
	 * Returns the list of comments
	 *
	 * @var $comment
	 * @type array
	 * @memberOf Translation
	 */
	public $comment = array();

	/**
	 * Returns the list of flag comments
	 *
	 * @var $comment_flags
	 * @type array
	 * @memberOf Translation
	 */
	public $comment_flags = array();

	/**
	 * Returns the list of reference comments
	 *
	 * @var $comment_references
	 * @type array
	 * @memberOf Translation
	 */
	public $comment_references = array();

	/**
	 * Returns the list of extracted comments
	 *
	 * @var $comment_extracted
	 * @type array
	 * @memberOf Translation
	 */
	public $comment_extracted = array();

	/**
	 * Returns the context name
	 *
	 * @var $context
	 * @type string
	 * @memberOf Translation
	 */
	public $context = '';

	/**
	 * Returns base form of the translation
	 *
	 * @var $base
	 * @type string
	 * @memberOf Translation
	 */
	public $base = '';

	/**
	 * Returns translated form of the translation
	 *
	 * @var $translation
	 * @type string
	 * @memberOf Translation
	 */
	public $translation = '';
}