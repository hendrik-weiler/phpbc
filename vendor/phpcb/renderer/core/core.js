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
        } else {
            var attributes = [];
            var attrs = node.attributes;
            for(var i = attrs.length - 1; i >= 0; i--) {
                attributes.push(attrs[i].name + "=" + attrs[i].value);
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
async function __ajaxClickCall(method, path, node) {
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

/**
 * Calls a codeBehind function on click
 *
 * @param method The method name in codeBehind
 * @param path The url path
 * @param node The node
 */
function __clickCall(method, path, node) {
    location.href= path + "?__execute__=" + method + '&' + __getAttributes(node).join('&') + "&redir=" + location.href;
}