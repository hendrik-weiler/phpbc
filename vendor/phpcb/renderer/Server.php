<?php

require_once RENDERER_PATH . './renderer/Renderer.php';

class Server {

	private $path;

	private $routes;

	private $pagePath = '/app/pages/';

	private $controllerPath = '/app/controller/';

	private $cssPath = '/app/css/';

	private $jsPath = '/app/js/';

	public function __construct($path, $routes)
	{
		$this->path = str_replace('..','',$path);
		$this->routes = $routes;
	}

	private function removeGetParams() {
		$path = $this->path;
		$split = explode('?', $path);
		$this->path = $split[0];
	}

	public function includeControllers() {
		$path = getcwd() . $this->controllerPath;
		foreach (glob($path.'*.php') as $file) {
			require_once $file;
		}
	}

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
			return $renderer->render();
		}
		return false;
	}

	public function forbidden() {
		http_response_code(403);
		print 'Forbidden';
	}

	public function serveRessourceIfExist() {
		$ext = pathinfo($this->path, PATHINFO_EXTENSION);
		if(preg_match('#^/@#', $this->path)) {
			$pathRP = str_replace('@', '',$this->path);
			if($ext == 'js') {
				$path = getcwd() . $this->jsPath . $pathRP;
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
		}
		return false;
	}

	public function serve() {
		$this->removeGetParams();
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