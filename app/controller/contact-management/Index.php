<?php

namespace Controller\contactManagement;

class Index extends Controller
{
	public $form_login_username;

	public $form_login_password;

	public function get_execute($renderer, $request, $response)
	{
		$namespaceName = $request->getUrlSegment(2);
		if(empty($namespaceName)) {
			$this->namespace = false;
		} else {
			$this->initDB();
			$result = $this->queryDB('SELECT id,name FROM namespace WHERE name = "' . $this->escapeString($namespaceName) . '"');
			$row = $result->fetchArray(SQLITE3_ASSOC);
			if($row == false) {
				$response->redirect('/contact-management/');
			} else {
				$this->namespace = $row;
			}
		}
	}

	public function post_execute($renderer, $request, $response)
	{
		$this->get_execute($renderer, $request, $response);

		if(!$request->checkCRSFToken()) {
			$response->redirect('/contact-management/' . $this->namespace['name']);
		}

		$this->initDB();
		$username = $this->escapeString($this->form_login_username->getValue());
		$password = $this->escapeString($this->form_login_password->getValue());
		$password = sha1($password. SALT);
		$result = $this->queryDB('SELECT id,namespace_id FROM account WHERE username = "' . $username . '" AND password = "' . $password . '"');
		$row = $result->fetchArray(SQLITE3_ASSOC);

		if(!$row) {
			$error = $renderer->document->getElementById('error');
			$error->removeAttribute('hidden');
		} else {

			$nsName = '';
			if($this->namespace == false) {
				$result2 = $this->queryDB('SELECT name FROM namespace WHERE id = "' . $row['namespace_id'].'"');
				$row2 = $result2->fetchArray(SQLITE3_ASSOC);
				if($row2==false) {
					$error = $renderer->document->getElementById('error');
					$error->removeAttribute('hidden');
					return;
				} else {
					$nsName = $row2['name'];
				}
			} else {
				$nsName = $this->namespace['name'];
			}

			$sid = \Tools::generateSessionId();
			$this->execDB('UPDATE account SET session = "' . $sid . '" WHERE id=' . $row['id']);
			\Tools::setCookie('sid', $sid, '/contact-management/' . $nsName);
			$response->redirect('/contact-management/' . $nsName . '/contacts');
		}
	}

}