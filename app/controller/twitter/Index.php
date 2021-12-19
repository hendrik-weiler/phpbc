<?php

namespace Controller\twitter;

class Index extends Controller
{
	public $form_login_username;

	public $form_login_password;

	public function get_execute($renderer, $request, $response)
	{
		$this->initDB();
	}

	public function post_execute($renderer, $request, $response)
	{
		if(!$request->checkCRSFToken()) {
			$response->redirect('/twitter');
			return;
		}

		$this->initDB();
		$username = $this->escapeString($this->form_login_username->getValue());
		$password = $this->escapeString($this->form_login_password->getValue());
		$password = sha1($password. SALT);
		$result = $this->queryDB('SELECT id FROM account WHERE username = "' . $username . '" AND password = "' . $password . '"');
		$row = $result->fetchArray(SQLITE3_ASSOC);

		if(!$row) {
			$error = $renderer->document->getElementById('error');
			$error->removeAttribute('hidden');
		} else {
			$sid = \Tools::generateSessionId();
			$this->execDB('UPDATE account SET session = "' . $sid . '" WHERE id=' . $row['id']);
			\Tools::setCookie('sid', $sid);
			$response->redirect('/twitter/app');
		}
	}

}