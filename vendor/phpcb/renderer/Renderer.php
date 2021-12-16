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
			if($declaration['__type__'] == 'import') {
				$compName = $declaration['name'];
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
			if($declaration['__type__'] == 'codeBehind') {
				$className = $declaration['class'];
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
	 * The render process
	 *
	 * @return string|void
	 * @throws \Exception
	 * @memberOf Renderer
	 * @method render
	 */
	public function render() {
		$rootNode = $this->document->parse();
		call_user_func($this->injectHTMLCallback, $this->document);
		$this->checkDeclarations();
		$this->initComponents();
		if($this->codeBehind) {
			$request = new Request();
			$response = new Response();
			$forms = $this->document->getForms();
			foreach ($forms as $formName => $form) {
				foreach ($form as $inputName => $inputElm) {
					$property = 'form_' . $formName . '_' . $inputName;
					if(property_exists($this->codeBehind, $property)) {
						if($inputElm->name == 'input'
							|| $inputElm->name == 'textarea') {
							$this->codeBehind->{$property} = new Input($inputElm, $this, $request);
						}
					}
				}
			}
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
								$request = new AjaxRequest();
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
		return $result;
	}
}