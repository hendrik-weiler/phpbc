<?php

$routes = array(
	// shortlink generator
	'/shortlink/r/[a-zA-Z0-9]+$' => ':\Controller\shortlink\Redir',
	'/shortlink/links/[a-zA-Z0-9]+$' => 'shortlink/links.html',
	// contact management
	'/contact-management/[a-zA-Z0-9\-]+/contacts/[0-9]+/edit$' => 'contact-management/contacts_edit.html',
	'/contact-management/[a-zA-Z0-9\-]+/contacts/[0-9]+/delete$' => 'contact-management/contacts_delete.html',
	'/contact-management/[a-zA-Z0-9\-]+/contacts/add$' => 'contact-management/contacts_add.html',
	'/contact-management/[a-zA-Z0-9\-]+/contacts$' => 'contact-management/contacts.html',
	'/contact-management/[a-zA-Z0-9\-]+/profile$' => 'contact-management/profile.html',
	'/contact-management/[a-zA-Z0-9\-]+(/)?$' => 'contact-management/index.html',
	'/contact-management(/)?$' => 'contact-management/index.html',
);