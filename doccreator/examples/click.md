You set a handler for the element
````
<div data-htmldec>
    @Click(handler=clickHandler)
    <div>Click me</div>
</div>
<script>
// notice that name of the function was given
decHandler(function clickHandler() {
    console.log('I was clicked');
});
</script>
````