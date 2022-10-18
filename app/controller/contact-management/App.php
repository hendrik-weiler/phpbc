<?php

namespace Controller\contactManagement;

use renderer\AjaxRequest;
use renderer\AjaxResponse;
use renderer\Renderer;
use renderer\Request;
use renderer\Response;

require_once 'Controller.php';

class App extends Controller
{

	public function logout($renderer, $request, $response) {
		if(!$this->canAccess($request)) {
			$response->redirect('/contact-management/' . $this->namespace['name']);
		}

		$this->initDB();
		$res = $this->execDB('UPDATE account SET session = "' . \Tools::generateSessionId() . '" WHERE id = ' . $this->escapeString($this->user['id']));
		\Tools::removeCookie('sid');
		$response->redirect('/contact-management/' . $this->namespace['name']);
	}

	protected function init(Renderer $renderer,Request $request,Response $response) {
		$this->initDB();
		if(!$this->canAccess($request)) {
			$response->redirect('/contact-management/'. $this->namespace['name']);
		}

		$logout = $renderer->document->getElementById('logout');
		$logout->addEventListener('click' ,'logout');

		$page = $request->getUrlSegment(3);
		$renderer->setVariable('contacts_active','');
		$renderer->setVariable('profile_active','');
		if($page == 'contacts') {
			$renderer->setVariable('contacts_active','active');
		}
		if($page == 'profile') {
			$renderer->setVariable('profile_active','active');
		}

		$renderer->setVariable('nsName', $this->namespace['name']);
	}
}