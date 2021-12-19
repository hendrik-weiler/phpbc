<?php

namespace Controller\contactManagement\Contacts;

use Controller\contactManagement\App;
use renderer\Request;
use renderer\Response;

class Edit extends App
{
	public $form_form_company;

	public $form_form_prename;

	public $form_form_name;

	public $form_form_street;

	public $form_form_zipcode;

	public $form_form_city;

	public $form_form_country;

	public $form_form_phone;

	public $form_form_fax;

	public $form_form_contact_person;

	public $form_form_contact_person_phone;

	public $form_form_notes;

	public function loadAndSetData($request, $response) {
		$this->initDB();
		$id = $request->getUrlSegment(4);
		$result = $this->queryDB('SELECT * FROM contact WHERE id = "' .  $this->escapeString($id) . '"');
		$row = $result->fetchArray(SQLITE3_ASSOC);
		if($row==false) {
			$response->redirect('/contact-management/' . $this->namespace['name'] . '/contacts');
		} else {
			$this->form_form_company->setValue($row['company']);
			$this->form_form_prename->setValue($row['prename']);
			$this->form_form_name->setValue($row['name']);
			$this->form_form_street->setValue($row['street']);
			$this->form_form_zipcode->setValue($row['zipcode']);
			$this->form_form_city->setValue($row['city']);
			$this->form_form_country->setValue($row['country']);
			$this->form_form_phone->setValue($row['phone']);
			$this->form_form_fax->setValue($row['fax']);
			$this->form_form_notes->setValue($row['notes']);
			$this->form_form_contact_person->setValue($row['contact_person']);
			$this->form_form_contact_person_phone->setValue($row['contact_phone']);
		}
	}

	public function get_execute($renderer,Request $request,Response $response)
	{
		$this->init($renderer, $request, $response);

		$node = $this->document->createFromHTML(
			file_get_contents(APP_PATH . 'pages/contact-management/partials/contacts/edit.html')
		);

		$container = $renderer->document->getElementById('contentContainer');
		$container->appendChild($node);

		$renderer->updateFormReferences($request);

		$this->loadAndSetData($request, $response);
	}

	public function post_execute($renderer, Request $request, Response $response)
	{
		$this->init($renderer, $request, $response);

		$id = $request->getUrlSegment(4);

		if(!$request->checkCRSFToken()) {
			$response->redirect('/contact-management/' . $this->namespace['name'] . '/contacts/' . $id . '/edit');
			return;
		}

		$this->get_execute($renderer, $request, $response);

		if($request->isFormValid($renderer, 'form')) {
			$result = $this->queryDB('UPDATE contact  
				SET company = "' . $this->escapeString($request->getValue('company')) . '",
					prename = "' . $this->escapeString($request->getValue('prename')) . '",
					name = "' . $this->escapeString($request->getValue('name')) . '",
					street = "' . $this->escapeString($request->getValue('street')) . '",
					city = "' . $this->escapeString($request->getValue('city')) . '",
					zipcode = "' . $this->escapeString($request->getValue('zipcode')) . '",
					country = "' . $this->escapeString($request->getValue('country')) . '",
					phone = "' . $this->escapeString($request->getValue('phone')) . '",
					notes = "' . $this->escapeString($request->getValue('notes')) . '",
					fax = "' . $this->escapeString($request->getValue('fax')) . '",
					contact_person = "' . $this->escapeString($request->getValue('contact_person')) . '",
					contact_phone = "' . $this->escapeString($request->getValue('contact_person_phone')) . '"
				WHERE id = "' . $this->escapeString($id) . '"');

			$this->loadAndSetData($request, $response);

			$success = $renderer->document->getElementById('success');
			$success->removeAttribute('hidden');
		} else {

			$error = $renderer->document->getElementById('error');
			$error->removeAttribute('hidden');
		}
	}
}