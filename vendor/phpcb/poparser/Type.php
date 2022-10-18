<?php

namespace poparser;

/**
 * The types of the po parser
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Type
 * @namespace poparser
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
	 * @var NEW_LINE
	 */
	const NEW_LINE = 'NEW_LINE';

	/**
	 * @memberOf Type
	 * @var COMMENT
	 */
	const COMMENT = 'COMMENT';

	/**
	 * @memberOf Type
	 * @var COMMENT_EXTRACTED
	 */
	const COMMENT_EXTRACTED = 'COMMENT';

	/**
	 * @memberOf Type
	 * @var COMMENT_REFERENCE
	 */
	const COMMENT_REFERENCE = 'COMMENT_REFERENCE';

	/**
	 * @memberOf Type
	 * @var COMMENT_FLAG
	 */
	const COMMENT_FLAG = 'COMMENT_FLAG';

	/**
	 * @memberOf Type
	 * @var MSGID
	 */
	const MSGID = 'MSGID';

	/**
	 * @memberOf Type
	 * @var MSGSTR
	 */
	const MSGSTR = 'MSGSTR';

	/**
	 * @memberOf Type
	 * @var MSGCONTEXT
	 */
	const MSGCONTEXT = 'MSGCONTEXT';

	/**
	 * @memberOf Type
	 * @var VALUE
	 */
	const VALUE = "VALUE";

	/**
	 * @memberOf Type
	 * @var MULTILINE_START
	 */
	const MULTILINE_START = 'MULTILINE_START';
}