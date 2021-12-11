<?php

namespace renderer;

use xmlparser\Document;
use xmlparser\Node;

class Component
{
	public $name;

	public $document;

	public $componentPath;

	public $node;

	public function __construct($name)
	{
		$this->name = $name;
	}

	protected function getHeadElement() {
		$head = $this->document->getElementsByTagName('head');
		if(count($head) > 0) {
			return $head[0];
		} else {
			throw new \Exception('The head tag couldnt be found.');
		}
	}

	protected function setEvent($name) {
		if($onclick = $this->node->getAttribute($name)) {
			$attributes = array();
			foreach($this->node->getAttributes() as $key => $value) {
				if($key == $name) continue;
				$attributes[] = $key . '=' . $value;
			}
			$this->node->setAttribute('onclick','javascript:location.href="?__execute__=' . $onclick . '&' . implode('&', $attributes) . '"');
		}
	}

	protected function isJavaScriptIncluded($path) {
		$tags = $this->document->getTags();
		if(isset($tags['script'])) {
			foreach ($tags['script'] as $tag) {
				$src = $tag->getAttribute('src');
				if($src && $src == $this->componentPath . $path) {
					return true;
				}
			}
			return false;
		}
		return false;
	}

	protected function isStylesheetIncluded($path) {
		$tags = $this->document->getTags();
		if(isset($tags['link'])) {
			foreach ($tags['link'] as $tag) {
				$src = $tag->getAttribute('href');
				if($src && $src == $this->componentPath . $path) {
					return true;
				}
			}
			return false;
		}
		return false;
	}

	public function replaceTagName($tagName) {
		$this->node->name = $tagName;
	}

	public function addStyleSheet($path) {
		if($this->isStylesheetIncluded($path)) return;
		if(file_exists($this->componentPath . $path)) {
			$head = $this->getHeadElement();
			$node = new Node('link', array(
				'rel' => 'stylesheet',
				'href' => $this->componentPath .$path
			), $this->document);
			$head->appendChild($node);
		} else {
			throw new \Exception('Cant find stylesheet file from path "' . $path . '"');
		}
	}

	public function addJavaScript($path) {
		if($this->isJavaScriptIncluded($path)) return;
		if(file_exists($this->componentPath . $path)) {
			$head = $this->getHeadElement();
			$node = new Node('script', array(
				'src' => $this->componentPath .$path
			), $this->document);
			$head->appendChild($node);
		} else {
			throw new \Exception('Cant find javascript file from path "' . $path . '"');
		}
	}

	public function loadTemplate($path) {
		if(file_exists($path)) {
			$html = file_get_contents($path);
			$document = new Document($html);
			$document->parse();
			return $document;
		} else {
			throw new \Exception('Cant find template file from path "' . $path . '"');
		}
		return null;
	}

	public function render() {

	}
}