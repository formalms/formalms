var findKey = function (tree, id) {
    for (var key in tree) {
        var item = tree[key].children
        if (key == id) {
            return item;
        } else {
            item_ = findKey(item, id)
            if ((typeof item == 'object') && item_) {
                return item_;
            }
        }
    }

    return false;
}

var findParentKey = function (tree, id, parent) {

    for (var key in tree) {
        var item = tree[key].children
        if (key == id) {
            return parent;
        } else {
            item_ = findParentKey(item, id, key)
            if ((typeof item == 'object') && item_) {
                return item_;
            }
        }
    }

    return false;
}

function Node(nodeWindow, id, is_folder) {
    this.$view = jQuery('<div/>', {
        class: 'node',
    })

    if (is_folder) {

        this.$view.html(nodeWindow.folder_box)

        this.$view.on('click', function (event) {
            nodeWindow.open(id)

        })

    } else {

        this.$view.html(nodeWindow.element_box)

    }

}

Node.prototype.view = function () {

    return this.$view

}

function NodeWindow(element, url, folder_box, element_box, check_folder, fill_box_callback, success) {

    this.treeCache = {}

    this.treeCache[0] = {
        data: {},
        children: {}
    }

    this.folder_box = folder_box

    this.element_box = element_box

    this.check_folder = check_folder

    this.fill_box_callback = fill_box_callback

    this.success = success || function(){}

    this.$view = $(element)

    this.$view.html('')

    this.url = url

    this.open(0)

}

NodeWindow.prototype.view = function () {

    return this.$view

}

NodeWindow.prototype.open = function (id) {

    var view = this.$view
    var self = this

    var cache = findKey(this.treeCache, id)
    if (!jQuery.isEmptyObject(cache)) {

        this.reset(id, function () {

            for (var key in cache) {
                if (cache[key].data) {
                    
                    var is_folder = self.check_folder(cache[key].data)
    
                    var node = new Node(self, key, is_folder)
    
                    self.fill_box_callback(is_folder, node.view(), cache[key].data)
    
                    self.$view.append(node.view())
                }

            }

        })

        return
    }

    $.ajax({
        url: this.url,
        data: {
            'id': id
        }
    })
        .done(function (data) {

            self.reset(id, function () {

                var treeCache = findKey(self.treeCache, id)
                for (var key in data) {
                    var id_elem = data[key].id
                    if (treeCache) {
                        treeCache[id_elem] = {
                            data: data[key],
                            children: {}
                        }
                    }

                    var is_folder = self.check_folder(data.find(e => e.id === id_elem))

                    var node = new Node(self, id_elem, is_folder)

                    self.fill_box_callback(is_folder, node.view(), data.find(e => e.id === id_elem))

                    self.$view.append(node.view())

                }


            })

        });
}

NodeWindow.prototype.reset = function (id, callback) {

    var self = this

    var parenKey = findParentKey(self.treeCache, id)

    var backButton = null;

    if (parenKey) {

        backButton = $("<button>Back</button>")
            .on('click', function (event) {

                self.open(parenKey)

            })
    }

    this.$view.fadeOut("fast", function () {

        self.$view.html(backButton)
        callback()
        self.success()
        self.view().fadeIn("fast")

    })

}

$.fn.TreeWindow = function (options) {

    var settings = $.extend({
        ajax: false,
        folder_box: false,
        element_box: false,
        check_folder: false,
        fill_box_callback: false,
        success: false
    }, options);

    if (settings.ajax && settings.folder_box && settings.element_box && settings.fill_box_callback && settings.check_folder) {

        this.window_ = new NodeWindow(this, settings.ajax, settings.folder_box, settings.element_box, settings.check_folder, settings.fill_box_callback, settings.success)

    }


};