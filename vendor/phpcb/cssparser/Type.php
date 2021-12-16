<?php

namespace cssparser;

/**
 * The type class
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Type
 * @namespace cssparser
 */
class Type
{
	/**
	 * @memberOf Type
	 * @var EOF
	 */
	const EOF = 'EOF';

	/**
	 * @memberOf Type
	 * @var ID
	 */
	const ID = 'ID';

	/**
	 * @memberOf Type
	 * @var VALUE
	 */
	const VALUE = 'VALUE';

	/**
	 * @memberOf Type
	 * @var LBRACKET
	 */
	const LBRACKET = 'LBRACKET';

	/**
	 * @memberOf Type
	 * @var RBRACKET
	 */
	const RBRACKET = 'RBRACKET';

	/**
	 * @memberOf Type
	 * @var COLON
	 */
	const COLON = 'COLON';

	/**
	 * @memberOf Type
	 * @var SEMI
	 */
	const SEMI = 'SEMI';

	/**
	 * @memberOf Type
	 * @var LPAREN
	 */
	const LPAREN = 'LPAREN';

	/**
	 * @memberOf Type
	 * @var RPAREN
	 */
	const RPAREN = 'RPAREN';

	/**
	 * @memberOf Type
	 * @var COMMA
	 */
	const COMMA = 'COMMA';

	/**
	 * @memberOf Type
	 * @var KEY
	 */
	const KEY = 'KEY';

	/**
	 * @memberOf Type
	 * @var COMMENT_START
	 */
	const COMMENT_START = 'COMMENT_START';

	/**
	 * @memberOf Type
	 * @var COMMENT_END
	 */
	const COMMENT_END = 'COMMENT_END';

	/**
	 * @memberOf Type
	 * @var QUOTE
	 */
	const QUOTE = 'QUOTE';
}