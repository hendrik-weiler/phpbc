You add the click event on the node
````
// in your codeBehind class set the event on a node
$like = $renderer->document->getElementById('like');
$like->addEventListener('click','like');
// add the method in the codeBehind class
...
public function like($renderer,Request $request,Response $response) {
    // do stuff
    // if you return false there will be no redirection back to the page
}
...
````