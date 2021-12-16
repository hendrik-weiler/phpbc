<?php

namespace xmlparser;

/**
 * The type class
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Type
 * @namespace xmlparser
 */
class Type
{
	/**
	 * @memberOf Type
	 * @var DOCTYPE_START
	 */
	const DOCTYPE_START = 'DOCTYPE_START';

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
	 * @var TAG_START
	 */
	const TAG_START = 'TAG_START';

	/**
	 * @memberOf Type
	 * @var TAG_SLASH_START
	 */
	const TAG_SLASH_START = 'TAG_SLASH_START';

	/**
	 * @memberOf Type
	 * @var TAG_SLASH_END
	 */
	const TAG_SLASH_END = 'TAG_SLASH_END';

	/**
	 * @memberOf Type
	 * @var DECLARATION_START
	 */
	const DECLARATION_START = 'DECLARATION_START';

	/**
	 * @memberOf Type
	 * @var DECLARATION_END
	 */
	const DECLARATION_END = 'DECLARATION_END';

	/**
	 * @memberOf Type
	 * @var EQUAL
	 */
	const EQUAL = 'EQUAL';

	/**
	 * @memberOf Type
	 * @var QUOTE
	 */
	const QUOTE = 'QUOTE';

	/**
	 * @memberOf Type
	 * @var SINGLE_QUOTE
	 */
	const SINGLE_QUOTE = 'SINGLE_QUOTE';

	/**
	 * @memberOf Type
	 * @var VALUE
	 */
	const VALUE = 'VALUE';

	/**
	 * @memberOf Type
	 * @var TAG_END
	 */
	const TAG_END = 'TAG_END';

	/**
	 * @memberOf Type
	 * @var ID
	 */
	const ID = 'ID';

	/**
	 * @memberOf Type
	 * @var EOF
	 */
	const EOF = 'EOF';
}