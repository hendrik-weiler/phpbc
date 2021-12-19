<?php

namespace Controller\shortlink;

class Index extends Controller
{
	public $form_generate_url;

	public function get_execute($renderer, $request, $response)
	{
		$this->initDB();
	}

	public function post_execute($renderer, $request, $response)
	{
		if(!$request->checkCRSFToken()) {
			$response->redirect('/shortlink');
			return;
		}

		if($this->form_generate_url->getValue() == '') {
			$error = $renderer->document->getElementById('error');
			$error->removeAttribute('hidden');
		} else {
			if (filter_var($this->form_generate_url->getValue(), FILTER_VALIDATE_URL) !== false) {
				$this->initDB();

				$linkGroupUid = uniqid();
				$this->execDB('INSERT INTO linkGroup VALUES (null,"' . $linkGroupUid . '")');
				$linkGroup_id = $this->lastInsertedId();

				$url = $this->escapeString($this->form_generate_url->getValue());
				$this->execDB('INSERT INTO link VALUES (null,' . $linkGroup_id . ',"' . uniqid() . '","' . $url . '",0,' . time() . ')');
				$response->redirect('/shortlink/links/' . $linkGroupUid);
			} else {
				$error = $renderer->document->getElementById('error2');
				$error->removeAttribute('hidden');
			}
		}
	}
}