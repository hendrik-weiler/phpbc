<?php

$routes = array(
	// shortlink generator
	'/shortlink/r/[a-zA-Z0-9]+' => ':\Controller\shortlink\Redir',
	'/shortlink/links/[a-zA-Z0-9]+' => 'shortlink/links.html'
);