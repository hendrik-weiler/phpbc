<?php

namespace Controller\contactManagement;

class Register extends Controller
{
	public $form_register_namespace;

	public $form_register_username;

	public $form_register_password;

	public $form_register_repeat_password;

	public function namespaceIsUseable($renderer, $request, $response) {
		$name = $request->getValue('value');
		return $this->isNSUseable($name);
	}

	public function usernameIsUseable($renderer, $request, $response) {
		$name = $request->getValue('value');
		return $this->isUsernameUseable($name);
	}

	public function get_execute($renderer, $request, $response)
	{
		$namespaceLabel = $renderer->document->getElementById('namespaceLabel');
		$namespaceLabel->setContent('Choose your name: ' . $_SERVER['HTTP_HOST'] . '/contact-management/$name');

		$namespace = $renderer->document->getElementById('namespace');
		$namespace->addEventListener('ajaxChange', 'namespaceIsUseable');

		$namespace = $renderer->document->getElementById('username');
		$namespace->addEventListener('ajaxChange', 'usernameIsUseable');
	}

	private function isNSUseable($name) {
		$this->initDB();
		$result = $this->queryDB('SELECT id FROM namespace WHERE name = "' . $this->escapeString($name) . '"');
		$row = $result->fetchArray(SQLITE3_ASSOC);
		return $row==false;
	}

	private function isUsernameUseable($name) {
		$this->initDB();
		$result = $this->queryDB('SELECT id FROM account WHERE username = "' . $this->escapeString($name) . '"');
		$row = $result->fetchArray(SQLITE3_ASSOC);
		return $row==false;
	}

	public function post_execute($renderer, $request, $response)
	{
		$this->get_execute($renderer, $request, $response);

		if(!$request->checkCRSFToken()) {
			$response->redirect('/contact-management/' . $this->namespace['name'] . '/register.html');
		}

		$username = $this->form_register_username->getValue();
		$password = $this->form_register_password->getValue();
		$repeat_password = $this->form_register_repeat_password->getValue();
		$namespace = $this->form_register_namespace->getValue();

		if(empty($namespace)) {
			$error = $renderer->document->getElementById('error5');
			$error->removeAttribute('hidden');
			return;
		}

		if(!$this->isNSUseable($namespace)) {
			$error = $renderer->document->getElementById('error6');
			$error->removeAttribute('hidden');
			return;
		}

		if(!$this->isUsernameUseable($username)) {
			$error = $renderer->document->getElementById('error7');
			$error->removeAttribute('hidden');
			return;
		}

		if(empty($username)) {
			$error = $renderer->document->getElementById('error3');
			$error->removeAttribute('hidden');
			return;
		}

		if(empty($password) || empty($repeat_password)) {
			$error = $renderer->document->getElementById('error4');
			$error->removeAttribute('hidden');
			return;
		}

		if($password != $repeat_password) {
			$error = $renderer->document->getElementById('error');
			$error->removeAttribute('hidden');
			return;
		}

		$sid = \Tools::generateSessionId();

		$this->initDB();
		$this->execDB('
			INSERT INTO namespace 
			VALUES(null,"' . $this->escapeString($namespace) . '",' . time() . ')
		');
		$nsId = $this->lastInsertedId();
		$this->execDB('
			INSERT INTO account 
			VALUES(null,' . $nsId . ',"' . $this->escapeString($username) . '","' . sha1($password.SALT) . '"
			,"' . $sid . '",' . time() . ',1)
		');

		\Tools::setCookie('sid', $sid);
		$response->redirect('/contact-management/' . $namespace . '/contacts');
	}
}