<?php

namespace Controller;

use renderer\CodeBehind;

class Redir extends Controller
{
	public function get_execute($renderer, $request, $response)
	{
		$uid = $request->getUrlSegment(2);
		$this->initDB();
		$result = $this->queryDB('SELECT id,link,counter FROM link WHERE uid = "' . $uid . '"');
		$row = $result->fetchArray(SQLITE3_ASSOC);
		if($row != false) {
			$counter = intval($row['counter']);
			++$counter;
			$this->execDB('UPDATE link SET counter = "'.$counter.'" WHERE id = "' . $row['id'] . '"');
			header('Location: ' . $row['link']);
		} else {
			print 'Invalid link';
		}
	}
}