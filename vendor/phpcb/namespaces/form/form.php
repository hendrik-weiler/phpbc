<?php

require_once RENDERER_PATH . './renderer/RendererNamespace.php';
require_once RENDERER_PATH . './renderer/Component.php';

require_once RENDERER_PATH . './namespaces/form/components/form/Form.php';
require_once RENDERER_PATH . './namespaces/form/components/textbox/TextBox.php';
require_once RENDERER_PATH . './namespaces/form/components/button/Button.php';

$__formNamespace__ = new \renderer\RendererNamespace('form');
$__formNamespace__->addComponent('\namespaces\form\Form');
$__formNamespace__->addComponent('\namespaces\form\TextBox');
$__formNamespace__->addComponent('\namespaces\form\Button');

function __formNamespaceInit__($renderer) {
	// add js, css to the dom
}