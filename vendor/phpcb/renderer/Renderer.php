<?php

namespace renderer;

require_once RENDERER_PATH . './cssparser/Document.php';
require_once RENDERER_PATH . './xmlparser/Document.php';
require_once RENDERER_PATH . './renderer/Request.php';
require_once RENDERER_PATH . './renderer/Response.php';
require_once RENDERER_PATH . './renderer/AjaxResponse.php';
require_once RENDERER_PATH . './renderer/AjaxRequest.php';
require_once RENDERER_PATH . './renderer/CodeBehind.php';

require_once RENDERER_PATH . './renderer/form/Input.php';

use cssparser\Parser as Parser;
use \xmlparser\Document as Document;

/**
 * The renderer class
 *
 * @class Renderer
 * @namespace renderer
 */
class Renderer
{
	/**
	 * Returns a document instance
	 *
	 * @var $document
	 * @type xmlparser.Document
	 * @memberOf Renderer
	 */
	public $document;

	/**
	 * Returns a list of namespaces
	 *
	 * @var $namespaces
	 * @type array
	 * @memberOf Renderer
	 * @private
	 */
	private $namespaces = array();

	/**
	 * Returns a list of component instances
	 *
	 * @var $componentInstances
	 * @type array
	 * @memberOf Renderer
	 * @private
	 */
	private $componentInstances = array();

	/**
	 * Returns a CodeBehind class or null
	 *
	 * @var $codeBehind
	 * @type CodeBehind
	 * @memberOf Renderer
	 */
	public $codeBehind = null;

	/**
	 * Returns a callable for a injection of html in the render process
	 *
	 * @var $injectHTMLCallback
	 * @type callable
	 * @memberOf Renderer
	 * @protected
	 */
	protected $injectHTMLCallback = null;

	/**
	 * Returns a map of variables to replace in the result xml
	 *
	 * @var $injectHTMLCallback
	 * @type callable
	 * @memberOf Renderer
	 * @protected
	 */
	protected $variables = array();

	/**
	 * The constructor
	 *
	 * @param string $text The text to render
	 * @memberOf Renderer
	 * @method __construct
	 * @constructor
	 */
	public function __construct($text)
	{
		$this->document = new Document($text);
	}

	/**
	 * Runs a class
	 *
	 * The identifier is for example:
	 * \namespace\class
	 * \class
	 *
	 * The class must extend the CodeBehind class
	 *
	 * @param string $className The class identifier
	 * @throws \Exception
	 * @memberOf Renderer
	 * @method runClass
	 */
	public static function runClass($className) {
		if(class_exists($className)) {
			$request = new Request();
			$response = new Response();
			$codeBehind = new $className(null);
			if($_SERVER['REQUEST_METHOD'] === 'POST') {
				$codeBehind->post_execute(null,$request,$response);
			} else if($_SERVER['REQUEST_METHOD'] === 'GET') {
				$codeBehind->get_execute(null,$request,$response);
			}
		} else {
			throw new \Exception('Could not find class "' . $className . '" to run.');
		}
	}

	/**
	 * Adds a namespace
	 *
	 * @param RendererNamespace $namespace The namespace class
	 * @memberOf Renderer
	 * @method addNamespace
	 */
	public function addNamespace(RendererNamespace $namespace) {
		$this->namespaces[] = $namespace;
		$namespace->setNamespacePath(RENDERER_PATH . './namespaces');
		$namespace->document = $this->document;
	}

	/**
	 * Checks for declarations
	 *
	 * @throws \Exception
	 * @memberOf Renderer
	 * @method checkDeclarations
	 */
	public function checkDeclarations() {
		foreach ($this->document->getDeclarations() as $declaration) {
			if($declaration->name == 'import') {
				$compName = $declaration->name;
				$loadPath = RENDERER_PATH . './namespaces/' . $compName . '/' . $compName . '.php';
				if(file_exists($loadPath)) {
					require_once $loadPath;
					$namespaceVar = '__' . $compName . 'Namespace__';
					$this->addNamespace($$namespaceVar);
					$namespaceInitFunc = '__' . $compName . 'NamespaceInit__';
					if(function_exists($namespaceInitFunc)) {
						call_user_func($namespaceInitFunc, $this);
					}
				} else {
					throw new \Exception('Could not find namespace "' . $compName . '".');

				}
			}
			if($declaration->name == 'codeBehind') {
				$className = $declaration->getAttribute('class');
				if(class_exists($className)) {
					$this->codeBehind = new $className($this->document);
				} else {
					throw new \Exception('Could not find class "' . $className . '" for code behind.');
				}
			}
		}
	}

	/**
	 * Initializes the components
	 *
	 * @memberOf Renderer
	 * @method initComponents
	 */
	public function initComponents() {
		$tags = $this->document->getTags();
		foreach ($this->namespaces as $namespace) {
			$nname = $namespace->name;
			foreach($namespace->components as $componentDescription) {
				$cname = $componentDescription;
				$split = explode('\\', $cname);
				$cname = strtolower(array_pop($split));
				$mergedName = $nname . ':' . $cname;
				if(isset($tags[$mergedName])) {
					foreach ($tags[$mergedName] as $node) {
						$componentInstance = new $componentDescription();
						$componentInstance->document = $this->document;
						$componentInstance->componentPath = $namespace->componentPath;
						$componentInstance->node = $node;
						$componentInstance->render();
					}
				}
			}
		}
	}

	/**
	 * Set the injectHTML callback
	 *
	 * Signature:
	 * void callback(\xmlparser\Document $document)
	 *
	 * @param callable $func The callback function
	 * @memberOf Renderer
	 * @method injectHTML
	 */
	public function injectHTML(callable $func) {
		$this->injectHTMLCallback = $func;
	}

	/**
	 * Sets a variable for replacement
	 *
	 * @param string $key The key
	 * @param string $value The value
	 * @memberOf Renderer
	 * @method setVariable
	 */
	public function setVariable($key, $value) {
		$this->variables[$key] = $value;
	}

	/**
	 * Replaces global placeholders for the view
	 *
	 * @param string $text The text
	 * @return string
	 * @memberOf Renderer
	 * @method replaceVariables
	 */
	public function replaceVariables($text) {
		$search = array();
		$replace = array();

		foreach($this->variables as $key => $value) {
			$search[] = '{' . $key . '}';
			$replace[] = $value;
		}

		$text = str_replace($search, $replace, $text);
		// special replacement for form variables
		$text = preg_replace('#\{form:[a-zA-Z0-9_]+\}#i','', $text);

		return $text;
	}

	/**
	 * Updates the form references
	 *
	 * @param Request $request The request instance
	 * @memberOf Renderer
	 * @method updateFormReferences
	 */
	public function updateFormReferences($request) {
		$forms = $this->document->getForms();
		foreach ($forms as $formName => $form) {
			foreach ($form as $inputName => $inputElm) {
				$property = 'form_' . $formName . '_' . $inputName;
				if(property_exists($this->codeBehind, $property)) {
					if($inputElm->name == 'input'
						|| $inputElm->name == 'textarea'
						|| $inputElm->name == 'select') {
						$this->codeBehind->{$property} = new Input($inputElm, $this, $request);
					}
				}
			}
		}
	}

	/**
	 * The render process
	 *
	 * @return string|void
	 * @throws \Exception
	 * @memberOf Renderer
	 * @method render
	 */
	public function render() {
		$rootNode = $this->document->parse();
		if(is_callable($this->injectHTMLCallback)) {
			call_user_func($this->injectHTMLCallback, $this->document);
		}
		$this->checkDeclarations();
		$this->initComponents();
		if(isset($_REQUEST['__action__'])) {
			if($_REQUEST['__action__'] == 'langSwitch') {
				if(isset($_REQUEST['redir'])) {
					if(isset($_REQUEST['param'])) {

						$translation_decl = null;
						foreach($this->document->getDeclarations() as $declaration) {
							if($declaration->name == 'translation') {
								$translation_decl = $declaration;
								break;
							}
						}

						if(is_null($translation_decl)) {
							print 'A "translation" declaration needs to be set.';
							exit();
						}

						if(is_null($translation_decl->getAttribute('cookie-name'))) {
							print 'The "translation" declaration needs a "cookie-name" attribute.';
							exit();
						}

						$split = explode('?', $_SERVER['REQUEST_URI']);
						$cookiePath = $split[0];
						if(!is_null($translation_decl->getAttribute('cookie-path'))) {
							$cookiePath = $translation_decl->getAttribute('cookie-path');
						}

						setcookie($translation_decl->getAttribute('cookie-name'), $_REQUEST['param'], time()+(3600*12),$cookiePath);

						header('Location: ' . $_REQUEST['redir']);
						exit();
					} else {
						print '"param" needs to be set.';
						exit();
					}
				} else {
					print '"redir" needs to be set.';
					exit();
				}
			}
		}
		if($this->codeBehind) {
			$request = new Request($this->document);
			$response = new Response();
			$this->updateFormReferences($request);
			if(isset($_REQUEST['__execute__'])) {
				if(method_exists($this->codeBehind, $_REQUEST['__execute__'])) {
					$result = call_user_func_array(array($this->codeBehind,$_REQUEST['__execute__']), array($this, $request, $response));
					if(is_null($result) && isset($_REQUEST['redir'])) {
						header('Location: ' . $_REQUEST['redir']);
						exit();
					}
				} else {
					throw new \Exception('Could not call method "' . $_REQUEST['__execute__'] . '" in code behind class.');
				}
			}
			if($_SERVER['REQUEST_METHOD'] === 'POST') {
				if(isset($_GET['ajaxCall']) && $_GET['ajaxCall']==1) {
					$inputJSON = file_get_contents('php://input');
					try {
						$inputJSON = json_decode($inputJSON,true);
						if(!isset($inputJSON['method'])) {
							print 'invalid call';
						} else {
							if(method_exists($this->codeBehind,$inputJSON['method'])) {
								$request = new AjaxRequest($this->document);
								$request->fillValues($inputJSON['params']);
								$response = new AjaxResponse($inputJSON['method']);
								$result = call_user_func_array(array($this->codeBehind, $inputJSON['method']), array($this, $request, $response));
								if($result instanceof AjaxResponse) {
									print $result;
								} else {
									$response->setContent($result);
									print $response;
								}
								exit();
							} else {
								throw new \Exception('Could not call method "' . $inputJSON['method'] . '" in code behind class.');
							}
						}
					} catch (\Exception $e) {
						print $e->getMessage();
						exit();
					}
				} else {
					$this->codeBehind->post_execute($this,$request,$response);
				}
			} else if($_SERVER['REQUEST_METHOD'] === 'GET') {
				$this->codeBehind->get_execute($this,$request,$response);
			}
		}
		$result = $this->document->generateDoctype('DOCTYPE');
		$result .= $rootNode->toXML();
		return $this->replaceVariables($result);
	}
}