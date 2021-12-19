/**
 * Gets attributes from a node
 *
 * @param node The node
 * @param asObject If the attributes should be returned as an object
 * @return mixed
 */
function __getAttributes(node, asObject) {
    if(!asObject) asObject = false;

    if(node.hasAttributes()) {
        if(asObject) {
            var attributes = {};
            var attrs = node.attributes;
            for(var i = attrs.length - 1; i >= 0; i--) {
                attributes[attrs[i].name] = attrs[i].value;
            }
            attributes['value'] = '';
            if(typeof node.value != 'undefined') {
                attributes['value'] = node.value;
            }
        } else {
            var attributes = [];
            var attrs = node.attributes;
            for(var i = attrs.length - 1; i >= 0; i--) {
                attributes.push(attrs[i].name + "=" + attrs[i].value);
            }
            if(typeof node.value != 'undefined') {
                attributes.push('value=' + node.value);
            } else {
                attributes.push('value=');
            }

        }
    }
    return attributes;
}

/**
 * Creates an ajax request
 *
 * @param method The method name in codeBehind
 * @param path The url path
 * @param node The node
 */
async function __ajaxCall(method,path,node) {
    let data = {
        method : method,
        params : __getAttributes(node, true)
    }
    let req = await fetch(path + '?ajaxCall=1', {
            method : 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body : JSON.stringify(data)
        }),
        res = await req.text();

    try {
        res = JSON.parse(res);
        if(typeof window[res.method] == 'function') {
            window[res.method](res.value);
        } else {
            console.warn('Method "' + res.method + '" is not available.');
        }
    } catch (e) {
        console.log(e.message);
    }
}

// init
window.addEventListener('load', function () {
   var  init = document.querySelectorAll('[data-init]'),
        evts = document.querySelectorAll('[phpcb-event]'),
        evtElm,
        eventName,
        funcName,
        i = 0;

    // autofill values of form inputs
    for(i; i < init.length; ++i) {
        if(init[i].dataset.init.length > 0) {
            init[i].value = init[i].dataset.init;
        }
    }

    // set events
    for(i=0; i < evts.length; ++i) {
        evtElm = evts[i];
        eventName = evtElm.getAttribute('phpcb-event');
        funcName = evtElm.getAttribute('phpcb-func');

        switch (eventName) {
            case "click":
                evtElm.addEventListener('click', function(e) {
                   e.preventDefault();
                   location.href = __request_url + "?__execute__=" + this.funcName + '&' + __getAttributes(e.currentTarget).join('&') + "&redir=" + location.href;
                   return false;
                }.bind({funcName:funcName}));
                break;
            case "ajaxClick":
                evtElm.addEventListener('click', async function(e) {
                    e.preventDefault();
                    await __ajaxCall(this.funcName,__request_url,e.currentTarget);
                    return false;
                }.bind({funcName:funcName}));
                break;
            case "ajaxChange":
                evtElm.addEventListener('change', async function(e) {
                    e.preventDefault();
                    await __ajaxCall(this.funcName,__request_url,e.currentTarget);
                    return false;
                }.bind({funcName:funcName}));
                break;
        }
    }
});