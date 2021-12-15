<?php

namespace Controller\shortlink;

use renderer\CodeBehind;

class Controller extends CodeBehind
{
	protected $sqliteDBPath = APP_PATH . 'shortlink.sqlite';

	private $db;

	protected function initDB() {

		if(!file_exists($this->sqliteDBPath)) {
			$db= new \SQLite3($this->sqliteDBPath);
			$db->exec('CREATE TABLE link (id INTEGER PRIMARY KEY NOT NULL, lgroup_id INTEGER ,uid TEXT, link TEXT, counter INTEGER ,created INTEGER)');
			$db->exec('CREATE TABLE linkGroup (id INTEGER PRIMARY KEY NOT NULL, uid TEXT)');
			$this->db = $db;
		} else {
			$this->db = new \SQLite3($this->sqliteDBPath);
		}
	}

	protected function execDB($query) {
		$this->db->exec($query);
	}

	protected function queryDB($query) {
		return $this->db->query($query);
	}

	protected function escapeString($string) {
		return $this->db->escapeString($string);
	}

	protected function lastInsertedId() {
		return $this->db->lastInsertRowID();
	}
}