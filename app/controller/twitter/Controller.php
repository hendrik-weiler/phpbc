<?php

namespace Controller\twitter;

use renderer\CodeBehind;

class Controller extends CodeBehind
{
	protected $sqliteDBPath = APP_PATH . 'twitter.sqlite';

	private $db;

	protected $user;

	protected function canAccess() {
		$sid = \Tools::getCookie('sid');
		if(is_null($sid)) {
			return false;
		}
		$this->initDB();
		$result = $this->queryDB('SELECT id,username FROM account WHERE session = "' . $this->escapeString($sid) . '"');
		$row = $result->fetchArray(SQLITE3_ASSOC);
		if($row == false) {
			return false;
		} else {
			$this->user = $row;
			return true;
		}
	}

	protected function getCurrentUser() {


	}

	protected function initDB() {

		if(!file_exists($this->sqliteDBPath)) {
			$db= new \SQLite3($this->sqliteDBPath);
			$db->exec('CREATE TABLE account (id INTEGER PRIMARY KEY NOT NULL, username TEXT, password TEXT, session TEXT, created INTEGER)');
			$db->exec('CREATE TABLE post (id INTEGER PRIMARY KEY NOT NULL, account_id INTEGER , message_text TEXT, retweet_id INTEGER, created INTEGER)');
			$db->exec('CREATE TABLE post_likes (id INTEGER PRIMARY KEY NOT NULL, post_id INTEGER , account_id INTEGER)');
			$db->exec('CREATE TABLE post_retweets (id INTEGER PRIMARY KEY NOT NULL, post_id INTEGER , account_id INTEGER)');
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