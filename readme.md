# phpcb

Let pages have a code behind class. <br>
A shortlink generator and twitter example is included.

Version: preview

### Contains

- css parser
- xml parser
- renderer
- shortlink generator example
- simple twitter clone example
- simple contact management example

#### Requirements

- PHP 7.2 and above
- Apache with mod_rewrite
- Samples requires the sqlite extension enabled on php

#### css parser

Doesnt support media queries

#### xml parser

Only supports valid xml

This doenst work:
```<link rel="stylesheet" href="x">```

This works:
```<link rel="stylesheet" href="x"/>```

#### How to add a code behind class?

In the html page you place for example
```<?codeBehind class="\Controller\Index" ?>```
at the top of the html. <br>
The classes ```get_execute``` will be called before outputing the html.
You can for example manipulate the html before its sent to the browser.

#### How to open a page?

You put a html page ```page.html``` in the ```app/controller/pages``` folder.
The file will be available at ```https://localhost/page.html``` or alternative ```https://localhost/page```

#### Events

You can add a onclick event on nodes

Example:
```
$h4s = $renderer->document->getElementsByTagName('h4');
$h4s[0]->addEventListener('click','test');
```

The method "test" will be called in the code behind class.

Signature:<br>
```
bool test($renderer,Request $request,Response $response);
```
If you ```return false``` in the method theres no redirection back to
the page.

You can add ajax events on nodes

Example:
```
$retweet = $renderer->document->getElementById('retweet' . $row['id']);
$retweet->addEventListener('ajaxClick','retweet');
// ajax usage in the twitter example
```

The method "retweet" will be called in the code behind class.

Signature:<br>
```
mixed retweet($renderer,AjaxRequest $request,AjaxResponse $response);
```