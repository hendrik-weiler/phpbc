<?php

namespace Controller\contactManagement;

class Contacts extends App
{
	public function get_execute($renderer, $request, $response)
	{
		$this->init($renderer, $request, $response);

		$node = $this->document->createFromHTML(
			file_get_contents(APP_PATH . 'pages/contact-management/partials/contacts.html')
		);

		$container = $renderer->document->getElementById('contentContainer');
		$container->appendChild($node);

		$addButton = $renderer->document->getElementById('add');
		$addButton->setAttribute('href', '/contact-management/' . $this->namespace['name'] . '/contacts/add');

		$entries = $renderer->document->getElementById('entries');

		$result = $this->queryDB('SELECT id,company,prename,name,zipcode,city
       		FROM contact t1 ORDER BY created desc;');
		$counter = 0;
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$counter++;

			$tmpl = $renderer->document->createFromHTML('
				<tr>
					<td>' . $row['id'] . '</td>
					<td>' . $row['company'] . '</td>
					<td>' . $row['prename'] . '</td>
					<td>' . $row['name'] . '</td>
					<td>' . $row['zipcode'] . '</td>
					<td>' . $row['city'] . '</td>
					<td>
						<a href="/contact-management/' . $this->namespace['name'] . '/contacts/' . $row['id'] . '/edit">Open</a> &nbsp;
						<a href="/contact-management/' . $this->namespace['name'] . '/contacts/' . $row['id'] . '/delete">Delete</a>
					</td>
				</tr>
			');
			$entries->appendChild($tmpl);

		}

		if($counter==0) {
			$tmpl = $renderer->document->createFromHTML('
				<tr>
					<td class="no-entries" colspan="7">No entries available.</td>
				</tr>
			');
			$entries->appendChild($tmpl);
		}

	}
}