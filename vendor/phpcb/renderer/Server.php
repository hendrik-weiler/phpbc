<?php

namespace renderer;

require_once RENDERER_PATH . './renderer/Renderer.php';

/**
 * The server class
 *
 * @author Hendrik Weiler
 * @version 1.0
 * @class Server
 * @namespace renderer
 */
class Server {

	/**
	 * Returns the unmodified path
	 *
	 * @var $originalPath
	 * @type string
	 * @memberOf Server
	 * @private
	 */
	private $originalPath;

	/**
	 * Returns the request path
	 *
	 * @var $path
	 * @type string
	 * @memberOf Server
	 * @private
	 */
	private $path;

	/**
	 * Returns the routes array
	 *
	 * @var $routes
	 * @type array
	 * @memberOf Server
	 * @private
	 */
	private $routes;

	/**
	 * Returns the path to the pages folder
	 *
	 * @var $pagePath
	 * @type string
	 * @memberOf Server
	 * @private
	 */
	private $pagePath = '/app/pages/';

	/**
	 * Returns the path to the controller folder
	 *
	 * @var $controllerPath
	 * @type string
	 * @memberOf Server
	 * @private
	 */
	private $controllerPath = '/app/controller/';

	/**
	 * Returns the path to the classes folder
	 *
	 * @var $classesPath
	 * @type string
	 * @memberOf Server
	 * @private
	 */
	private $classesPath = '/app/classes/';

	/**
	 * Returns the path to the css folder
	 *
	 * @var $cssPath
	 * @type string
	 * @memberOf Server
	 * @private
	 */
	private $cssPath = '/app/css/';

	/**
	 * Returns the path to the js folder
	 *
	 * @var $jsPath
	 * @type string
	 * @memberOf Server
	 * @private
	 */
	private $jsPath = '/app/js/';

	/**
	 * Returns the path to the renderer folder
	 *
	 * @var $rendererPath
	 * @type string
	 * @memberOf Server
	 * @private
	 */
	private $rendererPath = '/vendor/phpcb/renderer/';

	/**
	 * Returns the path to the img folder
	 *
	 * @var $imgPath
	 * @type string
	 * @memberOf Server
	 * @private
	 */
	private $imgPath = '/app/img/';

	/**
	 * The constructor
	 *
	 * @param string $path The request path
	 * @param array $routes The routes array
	 * @memberOf Server
	 * @method __construct
	 * @constructor
	 */
	public function __construct($path, $routes)
	{
		$this->path = str_replace('..','',$path);
		$this->originalPath = $this->path;
		$this->routes = $routes;
	}

	/**
	 * Removes the get parameters from the path
	 *
	 * @memberOf Server
	 * @method removeGetParams
	 * @private
	 */
	private function removeGetParams() {
		$path = $this->path;
		$split = explode('?', $path);
		$this->path = $split[0];
	}

	/**
	 * Recursively search for a pattern and returns the files
	 *
	 * @param string $pattern The glob pattern
	 * @param int $flags The flags
	 * @return array|false
	 * @memberOf Server
	 * @method rglob
	 * @private
	 */
	private function rglob($pattern, $flags = 0) {
		$files = glob($pattern, $flags);
		foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
			$files = array_merge($files, $this->rglob($dir.'/'.basename($pattern), $flags));
		}
		return $files;
	}

	/**
	 * Includes the classes files
	 *
	 * @memberOf Server
	 * @method includeClasses
	 */
	public function includeClasses() {
		$path = getcwd() . $this->classesPath;
		$files = $this->rglob($path.'/*.php');
		foreach ($files as $file) {
			require_once $file;
		}
	}

	/**
	 * Includes the controller files
	 *
	 * @memberOf Server
	 * @method includeControllers
	 */
	public function includeControllers() {
		$path = getcwd() . $this->controllerPath;
		$files = $this->rglob($path.'/*.php');
		foreach ($files as $file) {
			require_once $file;
		}
	}

	/**
	 * Serves the page if it exist or return false
	 *
	 * @return false|string
	 * @throws Exception
	 * @memberOf Server
	 * @method servePageIfExist
	 */
	public function servePageIfExist() {
		// check for routes
		foreach ($this->routes as $match => $path) {
			if(preg_match('#' . $match . '#', $this->path)) {
				$this->path = $path;
				if(preg_match('#^:#',$this->path)) {
					$rpl = str_replace(':','',$this->path);
					\renderer\Renderer::runClass($rpl);
					exit();
				}
				break;
			}
		}
		// if there was not route found search for the html page
		$path = getcwd() . $this->pagePath .$this->path;
		if($this->path[strlen($this->path)-1] == '/') {
			$path .= 'index';
		}
		if(is_dir($path)) {
			$path .= '/index';
		}
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		if(empty($ext)) $path .= '.html';
		if(file_exists($path)) {
			$text = file_get_contents($path);
			$renderer = new \renderer\Renderer($text);
			$this->addVariables($renderer);
			$renderer->injectHTML(function($document) {
				$head = $document->getElementsByTagName('head');
				if(count($head) > 0) {
					$script = $document->createElement('script');
					$translationVar = 'var __translations = {}';
					if(!empty($document->translations)) {
						$translationVar = 'var __translations = ' . json_encode($document->translations);
					}
					$script->setContent('var __request_url = "{request_url}";' . $translationVar);
					$head[0]->appendChild($script);
					$script = $document->createElement('script');
					$script->setAttribute('src', '/@/core/core.js');
					$head[0]->appendChild($script);
				}
			});
			return $renderer->render();
		}
		return false;
	}

	/**
	 * Adds global variables to the renderer
	 *
	 * @memberOf Server
	 * @param Renderer $renderer The renderer instance
	 * @method addVariables
	 */
	public function addVariables(Renderer $renderer) {

		$renderer->setVariable('request_url', $this->originalPath);

		foreach ($_REQUEST as $key => $value) {
			$renderer->setVariable('form:' . $key, $value);
		}
	}

	/**
	 * Sends a forbidden message
	 *
	 * @memberOf Server
	 * @method forbidden
	 */
	public function forbidden() {
		http_response_code(403);
		print 'Forbidden';
	}

	/**
	 * Serves ressources(css,js) if exist
	 *
	 * @return false|string
	 * @memberOf Server
	 * @method serveRessourceIfExist
	 */
	public function serveRessourceIfExist() {
		$ext = pathinfo($this->path, PATHINFO_EXTENSION);
		if(preg_match('#^/@#', $this->path)) {
			$pathRP = str_replace('@', '',$this->path);
			if($ext == 'js') {
				if(preg_match('#/@/core/#', $this->path)) {
					$path = getcwd() . $this->rendererPath . $pathRP;
				} else {
					$path = getcwd() . $this->jsPath . $pathRP;
				}
				if(file_exists($path)) {
					header('Content-Type: text/javascript');
					$text = file_get_contents($path);
					return $text;
				} else {
					return false;
				}
			}
			if($ext == 'css') {
				$path = getcwd() . $this->cssPath . $pathRP;
				if(file_exists($path)) {
					header('Content-Type: text/css');
					$text = file_get_contents($path);
					return $text;
				} else {
					return false;
				}
			}
			if($ext == 'svg'
				|| $ext == 'png'
				|| $ext == 'gif'
				|| $ext == 'jpg'
				|| $ext == 'jpeg') {
				$path = getcwd() . $this->imgPath . $pathRP;
				if(file_exists($path)) {
					$mimeType = 'image/' . $ext;
					if($ext == 'svg') {
						$mimeType = 'image/svg+xml';
					}
					header('Content-Type: ' . $mimeType);
					$text = file_get_contents($path);
					return $text;
				} else {
					return false;
				}
			}
		}
		return false;
	}

	/**
	 * Serves a file
	 *
	 * @throws Exception
	 * @memberOf Server
	 * @method serve
	 */
	public function serve() {
		$this->removeGetParams();
		$this->includeClasses();
		$this->includeControllers();
		$fullPath = getcwd() . $this->path;
		if($content = $this->serveRessourceIfExist()) {
			print $content;
		} else if($content = $this->servePageIfExist()) {
			print $content;
		} else {
			if(file_exists($fullPath)) {
				if(is_dir($fullPath)) {
					$this->forbidden();
				} else {
					$folderSplit = explode('/', $this->path);
					if($folderSplit[1] == 'vendor' || $folderSplit[1] == 'app') {
						$this->forbidden();
					} else {
						$data = file_get_contents($fullPath);
						print $data;
					}
				}
			} else {
				$this->forbidden();
			}
		}

	}

}