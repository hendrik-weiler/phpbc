<?php

namespace xmlparser;

/**
 * The type class
 *
 * @author Hendrik Weiler
 * @version 1.0
 */
class Type
{
	const DOCTYPE_START = 'DOCTYPE_START';

	const COMMENT_START = 'COMMENT_START';

	const COMMENT_END = 'COMMENT_END';

	const TAG_START = 'TAG_START';

	const TAG_SLASH_START = 'TAG_SLASH_START';

	const TAG_SLASH_END = 'TAG_SLASH_END';

	const DECLARATION_START = 'DECLARATION_START';

	const DECLARATION_END = 'DECLARATION_END';

	const EQUAL = 'EQUAL';

	const QUOTE = 'QUOTE';

	const SINGLE_QUOTE = 'SINGLE_QUOTE';

	const VALUE = 'VALUE';

	const TAG_END = 'TAG_END';

	const ID = 'ID';

	const EOF = 'EOF';
}