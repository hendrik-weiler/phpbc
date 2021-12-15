<?php

namespace Controller\twitter;

class Register extends Controller
{
	public $form_register_username;

	public $form_register_password;

	public $form_register_repeat_password;

	public function get_execute($renderer, $request, $response)
	{

	}

	public function post_execute($renderer, $request, $response)
	{
		$username = $this->form_register_username->getValue();
		$password = $this->form_register_password->getValue();
		$repeat_password = $this->form_register_repeat_password->getValue();

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
			INSERT INTO account 
			VALUES(null,"' . $this->escapeString($username) . '","' . sha1($password.SALT) . '"
			,"' . $sid . '",' . time() . ')
		');

		\Tools::setCookie('sid', $sid);
		$response->redirect('/twitter/app');
	}
}