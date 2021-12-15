<?php

require_once RENDERER_PATH . './renderer/Renderer.php';

/**
 * The server class
 *
 * @author Hendrik Weiler
 * @version 1.0
 */
class Server {

	/**
	 * Returns the unmodified path
	 *
	 * @var $originalPath
	 * @type string
	 */
	private $originalPath;

	/**
	 * Returns the request path
	 *
	 * @var $path
	 * @type string
	 */
	private $path;

	/**
	 * Returns the routes array
	 *
	 * @var $routes
	 * @type array
	 */
	private $routes;

	/**
	 * Returns the path to the pages folder
	 *
	 * @var $pagePath
	 * @type string
	 */
	private $pagePath = '/app/pages/';

	/**
	 * Returns the path to the controller folder
	 *
	 * @var $controllerPath
	 * @type string
	 */
	private $controllerPath = '/app/controller/';

	/**
	 * Returns the path to the classes folder
	 *
	 * @var $classesPath
	 * @type string
	 */
	private $classesPath = '/app/classes/';

	/**
	 * Returns the path to the css folder
	 *
	 * @var $cssPath
	 * @type string
	 */
	private $cssPath = '/app/css/';

	/**
	 * Returns the path to the js folder
	 *
	 * @var $jsPath
	 * @type string
	 */
	private $jsPath = '/app/js/';

	/**
	 * Returns the path to the renderer folder
	 *
	 * @var $rendererPath
	 * @type string
	 */
	private $rendererPath = '/vendor/phpcb/renderer/';

	/**
	 * Returns the path to the img folder
	 *
	 * @var $imgPath
	 * @type string
	 */
	private $imgPath = '/app/img/';

	/**
	 * The constructor
	 *
	 * @param string $path The request path
	 * @param array $routes The routes array
	 */
	public function __construct($path, $routes)
	{
		$this->path = str_replace('..','',$path);
		$this->originalPath = $this->path;
		$this->routes = $routes;
	}

	/**
	 * Removes the get parameters from the path
	 */
	private function removeGetParams() {
		$path = $this->path;
		$split = explode('?', $path);
		$this->path = $split[0];
	}

	/**
	 * Includes the classes files
	 */
	public function includeClasses() {
		$path = getcwd() . $this->classesPath;
		$files = array_merge(glob($path.'/**/*.php'),glob($path.'/*.php'));
		foreach ($files as $file) {
			require_once $file;
		}
	}

	/**
	 * Includes the controller files
	 */
	public function includeControllers() {
		$path = getcwd() . $this->controllerPath;
		$files = array_merge(glob($path.'/**/*.php'),glob($path.'/*.php'));
		foreach ($files as $file) {
			require_once $file;
		}
	}

	/**
	 * Serves the page if it exist or return false
	 *
	 * @return false|string
	 * @throws Exception
	 */
	public function servePageIfExist() {
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
			$renderer->injectHTML(function($document) {
				$head = $document->getElementsByTagName('head');
				if(count($head) > 0) {
					$script = $document->createElement('script');
					$script->setAttribute('src', '/@/core/core.js');
					$head[0]->appendChild($script);
				}
			});
			return $this->replacePlaceholders($renderer->render());
		}
		return false;
	}

	/**
	 * Replaces global placeholders for the view
	 *
	 * @param string $text The text
	 * @return string
	 */
	public function replacePlaceholders($text) {
		$search = array(
			'{request_url}'
		);
		$replace = array(
			$this->originalPath
		);

		foreach ($_REQUEST as $key => $value) {
			$search[] = '{form:' . $key. '}';
		}

		foreach ($_REQUEST as $key => $value) {
			$replace[] = $value;
		}

		$text = str_replace($search, $replace, $text);
		$text = preg_replace('#\{form:[a-zA-Z0-9_]+\}#i','', $text);

		return $text;
	}

	/**
	 * Sends a forbidden message
	 */
	public function forbidden() {
		http_response_code(403);
		print 'Forbidden';
	}

	/**
	 * Serves ressources(css,js) if exist
	 *
	 * @return false|string
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
	 */
	public function serve() {
		$this->removeGetParams();
		$this->includeClasses();
		$this->includeControllers();
		$fullPath = getcwd() . $this->path;
		// check for routes
		foreach ($this->routes as $match => $path) {
			if(preg_match('#' . $match . '#', $this->path)) {
				$this->path = $path;
				if(preg_match('#^:#',$this->path)) {
					$rpl = str_replace(':','',$this->path);
					\renderer\Renderer::runClass($rpl);
					return;
				}
				break;
			}
		}
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