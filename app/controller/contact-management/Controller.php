<?php

namespace Controller\contactManagement;

use renderer\CodeBehind;
use renderer\Request;

class Controller extends CodeBehind
{
	protected $sqliteDBPath = APP_PATH . 'contact-management.sqlite';

	private $db;

	protected $user;

	protected $namespace;

	protected function canAccess(Request $request) {
		$sid = \Tools::getCookie('sid');
		if(is_null($sid)) {
			return false;
		}
		$this->initDB();

		$nsName = $request->getUrlSegment(2);
		$result = $this->queryDB('SELECT id,name FROM namespace WHERE name = "' . $this->escapeString($nsName) . '"');
		$row = $result->fetchArray(SQLITE3_ASSOC);

		if($row == false) {
			return false;
		} else {
			$this->namespace = $row;
			$result2 = $this->queryDB('SELECT id,username FROM account WHERE session = "' . $this->escapeString($sid) . '" AND namespace_id = "' . $row['id'] . '"');
			$row2 = $result2->fetchArray(SQLITE3_ASSOC);
			if($row2 == false) {
				return false;
			} else {
				$this->user = $row2;
				return true;
			}
		}
	}

	protected function initDB() {

		if(!file_exists($this->sqliteDBPath)) {
			$db= new \SQLite3($this->sqliteDBPath);
			$db->exec('CREATE TABLE account (id INTEGER PRIMARY KEY NOT NULL,namespace_id INTEGER, username TEXT, password TEXT, session TEXT, created INTEGER, admin INTEGER)');
			$db->exec('CREATE TABLE namespace (id INTEGER PRIMARY KEY NOT NULL, name TEXT, created INTEGER)');
			$db->exec('CREATE TABLE contact (
    				id INTEGER PRIMARY KEY NOT NULL, 
    				namespace_id INTEGER , 
    				company TEXT, 
    				prename TEXT, 
    				name TEXT,
    				street TEXT,
    				zipcode TEXT,
    				city TEXT,
    				country TEXT,
    				notes TEXT,
    				phone TEXT,
    				fax TEXT,
    				delivery_company TEXT,
    				delivery_prename TEXT,
    				delivery_name TEXT,
    				delivery_street TEXT,
    				delivery_zipcode TEXT,
    				delivery_city TEXT,
    				delivery_country TEXT,
    				contact_person TEXT,
    				contact_phone TEXT,
    				created INTEGER,
    				updated INTEGER
            )');
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