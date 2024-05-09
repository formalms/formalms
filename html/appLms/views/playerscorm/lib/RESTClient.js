RESTClient = {};

RESTClient.call = function(url, method, body) {
    let xhr = new XMLHttpRequest();
    xhr.open(method, url, false);
    xhr.send(JSON.stringify(body));
    let status = xhr.status;
    let response;
    switch(xhr.getResponseHeader("Content-Type")) {
        case "application/json":
            response = xhr.responseText ? JSON.parse(xhr.responseText) : "";
            break;
        default:
            response = xhr.responseText;
            break;
    }
    return { status: status, response: response };
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

RESTClient.get = function(url) {
    RESTClient.debug(url, "GET");
    let response = RESTClient.call(url, "GET", null);
    RESTClient.debug(response, "response");
    return response;
}

RESTClient.head = function(url) {
    RESTClient.debug(url, "HEAD");
    let response = RESTClient.call(url, "HEAD", null);
    RESTClient.debug(response, "response");
    return response;
}

RESTClient.delete = function(url) {
    RESTClient.debug(url, "DELETE");
    let response = RESTClient.call(url, "DELETE", null);
    RESTClient.debug(response, "response");
    return response;
}

RESTClient.post = function(url, data) {
    RESTClient.debug(url, "POST");
    RESTClient.debug(data, "data");
    let response = RESTClient.call(url, "POST", data);
    RESTClient.debug(response, "response");
    return response;
}

RESTClient.put = function(url, data) {
    RESTClient.debug(url, "PUT");
    RESTClient.debug(data, "data");
    let response = RESTClient.call(url, "PUT", data);
    RESTClient.debug(response, "response");
    return response;
}
