<?php

namespace Controller\contactManagement\Contacts;

use Controller\contactManagement\App;
use renderer\Request;
use renderer\Response;

class Delete extends App
{
	public function delete($renderer, Request $request,Response $response) {
		if(!$this->canAccess($request)) {
			$response->redirect('/contact-management/' . $this->namespace['name']);
		}
		$id = $request->getUrlSegment(4);
		$this->initDB();
		$res = $this->execDB('DELETE FROM contact WHERE id = "' . $this->escapeString($id) . '"');
		$response->redirect('/contact-management/' . $this->namespace['name'] . '/contacts');
		return false;
	}

	public function get_execute($renderer, $request, $response)
	{
		$this->init($renderer, $request, $response);

		$node = $this->document->createFromHTML(
			file_get_contents(APP_PATH . 'pages/contact-management/partials/contacts/delete.html')
		);

		$container = $renderer->document->getElementById('contentContainer');
		$container->appendChild($node);

		$yes = $renderer->document->getElementById('yes');
		$yes->addEventListener('click' , 'delete');
	}
}