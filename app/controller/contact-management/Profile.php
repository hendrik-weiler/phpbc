<?php

namespace Controller\contactManagement;

class Profile extends App
{
	public $form_profile_username;

	public function get_execute($renderer, $request, $response)
	{
		$this->init($renderer, $request, $response);

		$node = $this->document->createFromHTML(
			file_get_contents(APP_PATH . 'pages/contact-management/partials/profile.html')
		);

		$container = $renderer->document->getElementById('contentContainer');
		$container->appendChild($node);

		$renderer->updateFormReferences($request);

		$this->form_profile_username->setValue($this->user['username']);
	}

	private function isPasswordValid($pwd) {
		$result = $this->queryDB('SELECT id FROM account WHERE password = "' . sha1($pwd.SALT) . '"');
		$row = $result->fetchArray(SQLITE3_ASSOC);
		return $row!=false;
	}

	public function post_execute($renderer, $request, $response)
	{
		$this->get_execute($renderer, $request, $response);

		$old_password = $request->getValue('old_password');
		$new_password = $request->getValue('new_password');
		$new_password_repeat = $request->getValue('new_password_repeat');

		if(!$request->checkCRSFToken()) {
			$response->redirect('/contact-management/' . $this->namespace['name'] . '/profile');
		}

		if(empty($new_password) || empty($new_password_repeat)) {
			$error = $renderer->document->getElementById('error3');
			$error->removeAttribute('hidden');
			return;
		}

		if(!empty($old_password) && !$this->isPasswordValid($old_password)) {
			$error = $renderer->document->getElementById('error');
			$error->removeAttribute('hidden');
			return;
		}

		if(!empty($new_password) && !empty($new_password_repeat)
			&& $new_password != $new_password_repeat){
			$error = $renderer->document->getElementById('error2');
			$error->removeAttribute('hidden');
			return;
		}

		if(!empty($old_password) && !empty($new_password) && !empty($new_password_repeat)) {

			$result = $this->execDB('UPDATE account SET password = "' . sha1($new_password.SALT) . '" WHERE password = "' . sha1($old_password.SALT) . '"');

			$error = $renderer->document->getElementById('success');
			$error->removeAttribute('hidden');
		}
	}
}