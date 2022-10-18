<?php

namespace renderer;

use xmlparser\Document;

/**
 * The request class
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Request
 * @namespace renderer
 */
class Request
{
	/**
	 * Returns the current language
	 *
	 * @var $currentLanguage
	 * @type string
	 * @memberOf Request
	 */
	public $currentLanguage = '';

	/**
	 * Initializes the request class
	 *
	 * @param Document $document The document instance
	 * @constructor
	 * @memberOf Request
	 * @method __construct
	 */
	public function __construct(Document $document)
	{
		$translation_decl = null;
		foreach ($document->getDeclarations() as $declaration) {
			if($declaration->name == 'translation') {
				$translation_decl = $declaration;
				break;
			}
		}

		if(!is_null($translation_decl)) {
			$defLang = $translation_decl->getAttribute('default-lang');
			$cookieName = $translation_decl->getAttribute('cookie-name');
			if(!is_null($defLang)
				&& !isset($_COOKIE[$cookieName])) {
				$this->currentLanguage = $defLang;
			}
			if(!is_null($cookieName)
				&& isset($_COOKIE[$cookieName])) {
				$this->currentLanguage = $_COOKIE[$cookieName];
			}

		}
	}

	/**
	 * Gets a value from post/get request
	 *
	 * @param string $name The name
	 * @return mixed|null
	 * @memberOf Request
	 * @method getValue
	 */
	public function getValue($name) {
		if(isset($_REQUEST[$name])) {
			return $_REQUEST[$name];
		}
		return null;
	}

	/**
	 * Gets all the url segments
	 *
	 * @return false|string[]
	 * @memberOf Request
	 * @method getUrlSegments
	 */
	public function getUrlSegments() {
		return explode('/',$_SERVER['REQUEST_URI']);
	}

	/**
	 * Gets a specific url segment
	 *
	 * @param int $index The index
	 * @return mixed|string
	 * @memberOf Request
	 * @method getUrlSegment
	 */
	public function getUrlSegment($index) {
		$segments = $this->getUrlSegments();
		$result = '';
		if(isset($segments[$index])) {
			$result = $segments[$index];
		}
		return $result;
	}

	/**
	 * Validates a form
	 *
	 * Only support required fields at the moment
	 *
	 * @param Renderer $renderer The renderer instance
	 * @param string $form_name The name of the form
	 * @return bool
	 * @memberOf Request
	 * @method isFormValid
	 */
	public function isFormValid(Renderer $renderer, $form_name) {
		$forms = $renderer->document->getForms();
		if(isset($forms[$form_name])) {
			foreach ($forms[$form_name] as $element) {
				$name = $element->getAttribute('name');
				$value = static::getValue($name);
				$isRequired = $element->getAttribute('required');
				if(!is_null($isRequired) && empty($value)) {
					return false;
				}
			}
			return true;
		} else {
			throw new \Exception('Form "' . $form_name . '" not found.');
		}
	}

	/**
	 * Empties a forms input fields
	 *
	 * @param Renderer $renderer The renderer instance
	 * @param string $form_name The name of the form
	 * @return bool
	 * @memberOf Request
	 * @method emptyForm
	 */
	public function emptyForm(Renderer $renderer, $form_name) {
		$forms = $renderer->document->getForms();
		if(isset($forms[$form_name])) {
			foreach ($forms[$form_name] as $element) {
				$name = $element->getAttribute('name');
				if(isset($_REQUEST[$name])) {
					$_REQUEST[$name] = '';
					$renderer->setVariable('form:' . $name, '');
				}
			}
			return true;
		} else {
			throw new \Exception('Form "' . $form_name . '" not found.');
		}
	}

	/**
	 * Checks the crsf token
	 *
	 * @return bool
	 * @memberOf Request
	 * @method checkCRSFToken
	 */
	public function checkCRSFToken() {
		if(isset($_COOKIE['crsf-token'])) {
			if(isset($_REQUEST['crsf-token'])) {
				return $_COOKIE['crsf-token'] == $_REQUEST['crsf-token'];
			}
			return false;
		}
		return false;
	}
}