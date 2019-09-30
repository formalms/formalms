function PaginatedList(options) {

    this.offset = 0;
    this.limit  = 0;

    this.index = 0;

    this.complete = false;

    this.ajax       = { };
    this.loading    = false;

    this.onSuccess  = [];
    this.onFailure  = [];
    this.onLoaded   = [];
    
    this.onNextSuccess  = [];
    this.onNextFailure  = [];
    this.onNextLoaded   = [];

    if(options.ajax !== undefined) {
        this.ajax = options.ajax;
    }
    if(this.ajax.data === undefined) {
        this.ajax.data = { };
    }
    if(options.pageSize !== undefined) {
        this.setPageSize(options.pageSize);
    }
    if(options.onSuccess !== undefined) {
        this.addOnSuccess(options.onSuccess.callback, options.onSuccess.context);
    }
    if(options.onFailure !== undefined) {
        this.addOnFailure(options.onFailure.callback, options.onFailure.context);
    }
    if(options.onLoaded !== undefined) {
        this.addOnLoaded(options.onLoaded.callback, options.onLoaded.context);
    }    
};

(function($) {

PaginatedList.prototype.getPageIndex = function() {

    return this.index;
};

PaginatedList.prototype.getPageSize = function() {

    return this.limit;
};

PaginatedList.prototype.isComplete = function() {

    return this.complete;
};

PaginatedList.prototype.isLoading = function() {

    return this.loading;
};

PaginatedList.prototype.addOnSuccess = function(callback, context) {

    var _callback = { callback: callback, context: context };
    this.onSuccess.push(_callback);
}

PaginatedList.prototype.addOnFailure = function(callback, context) {

    var _callback = { callback: callback, context: context };
    this.onFailure.push(_callback);
}

PaginatedList.prototype.addOnLoaded = function(callback, context) {

    var _callback = { callback: callback, context: context };
    this.onLoaded.push(_callback);
}

PaginatedList.prototype.addOnNextSuccess = function(callback, context) {

    var _callback = { callback: callback, context: context };
    this.onNextSuccess.push(_callback);
}

PaginatedList.prototype.addOnNextFailure = function(callback, context) {

    var _callback = { callback: callback, context: context };
    this.onNextFailure.push(_callback);
}

PaginatedList.prototype.addOnNextLoaded = function(callback, context) {

    var _callback = { callback: callback, context: context };
    this.onNextLoaded.push(_callback);
}

PaginatedList.prototype.setPageIndex = function(index) {

    this.index = index;
};

PaginatedList.prototype.setPageSize = function(size) {

    this.limit = size;
}

PaginatedList.prototype.next = function(count) {

    if(count === undefined) {
        count = 1;
    }

    this.page(this.index + count);
};

PaginatedList.prototype.previous = function(count) {

    if(count === undefined) {
        count = 1;
    }

    this.page(this.index - count);
};

PaginatedList.prototype.page = function(index) {

    if(!this.isLoading()) {
        this.index = index;
        var offset = this.index * this.limit;
        this._byOffset(offset);
    } else {
        this.addOnNextLoaded(function(context) { context.page(index) }, this);
    }
}

PaginatedList.prototype._byOffset = function(offset) {
    
    if(!this.isLoading()) {
        this.offset = offset;
        this._call();
    } else {
        this.addOnNextLoaded(function(context) { context._byOffset(offset) }, this);
    }
}

PaginatedList.prototype._call = function() {

    this.loading = true;

    jQuery.ajax({
        url: this.ajax.url
      , method: "POST"
      , data: {
            offset: this.offset
          , limit: this.limit
        }
      , context: this
      , success: function(response) {

            var list = this._getItems(response);
            this._successCallback(list);
        }
      , error: function(response) {

            this._failureCallback();
        }
      , complete: function(response) {

            this._loadedCallback();
        }
    });

    this.onNextSuccess = [];
    this.onNextFailure = [];
    this.onNextLoaded = [];
};

PaginatedList.prototype._getItems = function(response) {

    if(response.success) {
        if(response.list.length < this.limit) {
            this.complete = true;
        } else {
            this.complete = false;
        }
        return response.list;
    } else {
        if(response.message) {
            console.log(response.message);
        }
        this._failureCallback();
        return [];
    }
};

PaginatedList.prototype._loadedCallback = function() {
    
    this.loading = false;

    $(this.onLoaded).each(function(index, _callback) {
        var callback = _callback.callback;
        var context = _callback.context;
        callback(context);
    });

    $(this.onNextLoaded).each(function(index, _callback) {
        var callback = _callback.callback;
        var context = _callback.context;
        callback(context);
    });
};

PaginatedList.prototype._successCallback = function(list) {

    $(this.onSuccess).each(function(index, _callback) {
        var callback = _callback.callback;
        var context = _callback.context;
        callback(context, list);
    });

    $(this.onNextSuccess).each(function(index, _callback) {
        var callback = _callback.callback;
        var context = _callback.context;
        callback(context, list);
    });
};

PaginatedList.prototype._failureCallback = function() {

    $(this.onFailure).each(function(index, _callback) {
        var callback = _callback.callback;
        var context = _callback.context;
        callback(context);
    });
    
    $(this.onNextFailure).each(function(index, _callback) {
        var callback = _callback.callback;
        var context = _callback.context;
        callback(context);
    });
};

})(jQuery);
