RESTClient = {};

RESTClient.errors = 0;

RESTClient.call = function(url, method, body, headers, async) {
    RESTClient.debug(url, method);
    if(headers) RESTClient.debug(headers, "headers");
    if(body) RESTClient.debug(body, "body");
    RESTClient.debug(async, "asynchronous");

    if(!async || !navigator.sendBeacon) {
        let xhr = new XMLHttpRequest();
        let status;
        let response;
        xhr.onreadystatechange = function() {
            if(xhr.readyState == 4) {
                status = xhr.status;
                let contentType = xhr.getResponseHeader("Content-Type");
                if(contentType.indexOf("application/json") >= 0) {
                    response = xhr.responseText ? JSON.parse(xhr.responseText) : "";
                } else {
                    response = xhr.responseText;
                }
                RESTClient.debug(status, "status");
                RESTClient.debug(response, "response");
            }
        }; 
        xhr.open(method, url, async);
        if(headers) {
            Object.keys(headers).forEach(function(header) {
                xhr.setRequestHeader(header, headers[header]);
            });
        }
        xhr.setRequestHeader("Accept", "application/json");
        try {
            xhr.send(JSON.stringify(body));
        } catch(e) {
            RESTClient.errors++;
            RESTClient.debug("Error communicating with the server");
            switch(RESTClient.errors) {
                case 1:
                    RESTClient.debug("Second attempt");
                    return RESTClient.call(url, method, body, headers, async);
                case 2:
                    window.alert(i18n.translate("check-network-connection-alert"));
                    RESTClient.debug("Last attempt (after user OK)");
                    return RESTClient.call(url, method, body, headers, async);
                case 3:
                    RESTClient.errors = 0;
                    throw e;
            }
        }
        return { status: status, response: response };
    } else {
        navigator.sendBeacon(url, new Blob([JSON.stringify(body)], { type: 'application/json' }));
        return null;
    }
}

RESTClient.log = function(log, label) {
    if(label !== undefined) {
        console.log("RESTClient", "-", label + ":", log);
    } else {
        console.log("RESTClient", "-", log);
    }
}

RESTClient.debug = function(debug, label) {
    if(label !== undefined) {
        console.debug("RESTClient", "-", label + ":", debug);
    } else {
        console.debug("RESTClient", "-", debug);
    }
}

RESTClient.get = function(url, headers, async) {
    let response = RESTClient.call(url, "GET", null, headers, async);
    return response;
}

RESTClient.head = function(url, headers, async) {
    let response = RESTClient.call(url, "HEAD", null, headers, async);
    return response;
}

RESTClient.delete = function(url, headers, async) {
    let response = RESTClient.call(url, "DELETE", null, headers, async);
    return response;
}

RESTClient.post = function(url, data, headers, async) {
    let response = RESTClient.call(url, "POST", data, headers, async);
    return response;
}

RESTClient.put = function(url, data, headers, async) {
    let response = RESTClient.call(url, "PUT", data, headers, async);
    return response;
}
