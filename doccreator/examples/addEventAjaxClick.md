You add the ajaxClick event on the node
````
// in your codeBehind class set the event on a node
$like = $renderer->document->getElementById('like');
$like->addEventListener('ajaxClick','like');
// add the method in the codeBehind class
...
public function like($renderer,AjaxRequest $request,AjaxResponse $response) {
    // do stuff
    // you can return the data for example an array
    // or you can return the $response
    // with $response->setContent($data) you can add the data
}
...
// The response of the ajaxClick event is for example
// {"method":"ajax_like","value":{"post_id":"11","count":1}}
// the ajax_like function will be tried to call in javascript with parameter of the value
...
function ajax_like(data) {
    // do stuff
    // data = {"post_id":"11","count":1} --> parsed with JSON.parse
}
...
````