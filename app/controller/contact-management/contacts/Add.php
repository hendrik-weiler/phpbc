<?php

namespace Controller\contactManagement\Contacts;

use Controller\contactManagement\App;

class Add extends App
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

	public function get_execute($renderer, $request, $response)
	{
		$this->init($renderer, $request, $response);

		$node = $this->document->createFromHTML(
			file_get_contents(APP_PATH . 'pages/contact-management/partials/contacts/add.html')
		);

		$container = $renderer->document->getElementById('contentContainer');
		$container->appendChild($node);

		$renderer->updateFormReferences($request);
	}

	public function post_execute($renderer, $request, $response)
	{
		$this->get_execute($renderer, $request, $response);

		$company = $this->form_form_company->getValue();
		$prename = $this->form_form_prename->getValue();
		$name = $this->form_form_name->getValue();
		$street = $this->form_form_street->getValue();
		$zipcode = $this->form_form_zipcode->getValue();
		$city = $this->form_form_city->getValue();
		$country = $this->form_form_country->getValue();
		$phone = $this->form_form_phone->getValue();
		$fax = $this->form_form_fax->getValue();
		$notes = $this->form_form_notes->getValue();
		$contact_person = $this->form_form_contact_person->getValue();
		$contact_person_phone = $this->form_form_contact_person_phone->getValue();

		if(!$request->checkCRSFToken()) {
			$response->redirect('/contact-management/' . $this->namespace['name'] . '/contacts/add');
			return;
		}

		if($request->isFormValid($renderer, 'form')) {
			$request->emptyForm($renderer, 'form');

			$this->initDB();
			$res = $this->execDB('INSERT INTO contact VALUES 
				(null,' . $this->namespace['id'] . ',"' . $this->escapeString($company) . '",
					"' . $this->escapeString($prename) . '", "' . $this->escapeString($name) . '",
					"' . $this->escapeString($street) . '", "' . $this->escapeString($zipcode) . '",
					"' . $this->escapeString($city) . '", "' . $this->escapeString($country) . '",
					"' . $this->escapeString($notes) . '", "' . $this->escapeString($phone) . '",
					"' . $this->escapeString($fax) . '", "","","","","","","",
					"' . $this->escapeString($contact_person) . '", "' . $this->escapeString($contact_person_phone) . '",
					' . time() . ', ' . time() . ')');

			$success = $renderer->document->getElementById('success');
			$success->removeAttribute('hidden');
		} else {
			$error = $renderer->document->getElementById('error');
			$error->removeAttribute('hidden');
		}
	}
}