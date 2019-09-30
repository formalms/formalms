
(function ( $ ) {
    
   $.fn.paginator = function( options ) {
       
       var settings = $.extend({

           ajax_url: '',
           fill_box_callback: function() {},
           limit: 1,
           box: "",
           button: "<div class='load_more'>Load more</div>",
           offset: 0

       }, options );

       var append_items = function(context, items){

           var timer = 0;
           var list = context.data('list');

       
           load_more_button.removeClass('loading');           

           items.forEach(function(data, index) {
       
               
               var item = $(settings.box).clone();

               settings.fill_box_callback(item, data);

               

               item.queue('show', function(next) {
                    item.hide();
                    context.append(item);
                    item.delay(timer).fadeIn(200, next);
               });

               timer += 200;

               item.dequeue('show');

           });
           if( list.isComplete() ){
               load_more_button.remove();
           }

       };

       var load_more_button = $(settings.button)
       
       var thisObj = this;

       load_more_button.on("click", function(e){
           
           var list = thisObj.data('list');
           var settings = thisObj.data('settings');
           
           load_more_button.addClass("loading");
           
           list.next();

       }.bind(thisObj))

       this.after(load_more_button);

       var list = new PaginatedList({
           ajax: { url: settings.ajax_url },
           pageSize: settings.limit,
           onSuccess: { callback: append_items, context: this }
       });
       list.page(0);

       this.data('list', list);
       this.data('settings', settings);

       return this;

   };

}( jQuery ));