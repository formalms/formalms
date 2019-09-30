/**
 * DynamicContent
 * @since 2.0.0
 */
;(function($, window, document, undefined) {

    DynamicContent = function(scope) {

        this.owl = scope;

        if(this.owl.options.dynamicContent) {
            var options = this.owl.options.dynamicContent;
            if(options.ajax && options.box && options.fill_box_callback) {
                this.ajax       = { url: options.ajax };
                this.box        = $(options.box);
                this.fillBox    = options.fill_box_callback;
                this.success    = options.success || function() { };
                this.error      = options.error || function() { };
                this.loaded     = options.loaded || function() { };
                this.pageMultiplier = options.page_multiplier || 3;
                this.init();
            } else {
                throw "Arguments missing.";
            }
        }
    };

    DynamicContent.prototype.destroy = function() { };

    DynamicContent.prototype.init = function() {
        
        this.resizing = false;

        this.owl.$element
            .on("initialized.owl.carousel", function(e) {

                var pageSize = e.page.size * this.pageMultiplier;
                this.list = new PaginatedList({
                    ajax:       this.ajax
                  , pageSize:   pageSize
                  , onSuccess:  { callback: this.appendItems, context: this }
                });
                this.list.addOnSuccess(this.success, this);
                this.list.addOnFailure(this.error, this);
                this.list.addOnLoaded(this.loaded, this);
                this.list.page(0);

            }.bind(this))
            .on("changed.owl.carousel", function(e) {

                if(this.list && e.page.size && !this.resizing) {
                    if(e.page.index / this.pageMultiplier >= this.list.getPageIndex()) {
                        this.list.next();
                    }
                }

            }.bind(this))
            .on("resize.owl.carousel", function(e) {
                
                this.resizing = true;

            }.bind(this))
            .on("resized.owl.carousel", function(e) {
                
                for(var i = 0; i < e.item.count; i++) {
                    this.owl.$element.trigger('remove.owl.carousel', i);
                }
                var pageSize = e.page.size * this.pageMultiplier;
                this.list.setPageSize(pageSize);
                this.list.page(0);
                this.resizing = false;

            }.bind(this));
    };

    DynamicContent.prototype.appendItems = function(context, list) {
        
        if(list.length > 0) {
            for(var i = 0; i < list.length; i++) {
                var box = context.box.clone();
                context.fillBox(box, list[i]);
                context.owl.$element.trigger("add.owl.carousel", [box]);
            }
            context.owl.$element.trigger("refresh.owl.carousel");
        }
    };

    $.fn.owlCarousel.Constructor.Plugins['DynamicContent'] = DynamicContent;

}) (window.Zepto || window.jQuery, window, document);