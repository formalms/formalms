/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */
function init_tags(e) {
	
	try {
		var nodes = YAHOO.util.Selector.query(tag_params.query);
		if(tag_params.private_query) var pnodes = YAHOO.util.Selector.query(tag_params.private_query);
	} catch(e) {return;}

	if(!nodes) return;
	
	YAHOO.util.Event.addListener(nodes, 'click', CompileTag.show);
	if(tag_params.private_query && pnodes && pnodes.length) {		
		YAHOO.util.Event.addListener(pnodes, 'click', CompileTag.show);
  }
}

var CompileTag = {
	
	overlay: false,

	// save reference to the element
	tag_list: false,
	
	base_id: false,
	
	tag_handler: false,
	
	// show the overlay for tag editing, listener
	show: function(e) {
		YAHOO.util.Event.preventDefault(e);
		CompileTag.destroy();
		
		CompileTag.tag_handler = this.id;
		CompileTag.base_id = (this.id).replace(/handler-/, '');
		CompileTag.base_id = (CompileTag.base_id).replace(/private-/, '');
		CompileTag.tag_list = (this.id).replace(/handler-/, 'taglist-');
		
		
		
		tags = '';
		tags_node = YAHOO.util.Dom.get(CompileTag.tag_list).getElementsByTagName('b');
		if(tags_node.length) {
		
			for (var i = 0, len = tags_node.length; i < len; ++i) {
				tag_name = tags_node[i].innerHTML;

				tags += tag_name.replace(/[\s]+\([0-9]+\)/, '') + ', ';
			}
		}
		
		tags = tags.replace(/\"/g, "&quot;");
		
		CompileTag.overlay = new YAHOO.widget.Overlay("overlay-" + this.id, {visible:false,  
									zIndex:1000, 
									width:"500px",
									effect:{effect:YAHOO.widget.ContainerEffect.FADE,duration:0.35},
									context: [this.id, "tl", "bl"]} );
		
		CompileTag.overlay.setBody('<form id="form-' + CompileTag.base_id+ '" method="post" action="#" class="tags_block">'
			
			+ '<label class="newtags" for="newtags-' + CompileTag.base_id + '">'+tag_params.lang.tags+' : </label>' 
			
			+ '<div id="autocomplete_container">'
			
			+ '<input type="text" id="newtags-' + CompileTag.base_id + '" name="newtags-' + CompileTag.base_id + '" value="' + tags + '" />'
			+ '<div id="tag_suggestion_container"></div>'
			
			+ '</div>'
			+ '<br />'
			+ '<input type="hidden" id="id_resource" name="id_resource" value="' + (this.id).replace(/(.*)_/, '') + '" />'
			+ '<input type="hidden" id="resource_type" name="resource_type" value="' + tag_params.resource_type + '" />'
			
			+ '<p>'
			+tag_params.lang.tips
			+'</p>'
			
			+ '<p>'
			+ '<b>'+tag_params.lang.popular_tags+'</b> : ' + tag_params.popular_tags
			+'</p>'
			
			+ '<p>'
			+ '<b>'+tag_params.lang.user_tags+'</b> : ' + tag_params.user_tags
			+'</p>'
			
			+' <div class="align_right">'
			+ '<span class="yui-button"><span class="first-child">'
			+ '		<a id="tags_save" href="#">'+tag_params.lang.save+'</a>'
			+ '</span></span>'
			+ '&nbsp;&nbsp;'
			+ '<span class="yui-button"><span class="first-child">'
			+ '		<a id="tags_undo" href="#">'+tag_params.lang.undo+'</a>'
			+ '</span></span>'
			+ '</div>'
			+ '</form>');
		
		CompileTag.overlay.render(this.parentNode);
		
		CompileTag.set_listener();


		CompileTag.overlay.cfg.setProperty("context", [this.id, "br", "bl"]);
		CompileTag.overlay.show();	
		
		YAHOO.util.Dom.get('newtags-' + CompileTag.base_id).focus();	
	},
	
	set_listener: function() {
	
		save_listener = function(e) {
			YAHOO.util.Event.preventDefault(e);
			
			var postData = 'tags='+encodeURIComponent( YAHOO.util.Dom.get('newtags-' + CompileTag.base_id).value )
				+'&id_resource='+encodeURIComponent( YAHOO.util.Dom.get('id_resource').value )
				+'&resource_type='+encodeURIComponent( YAHOO.util.Dom.get('resource_type').value )
				+'&title='+encodeURIComponent( YAHOO.util.Dom.get('restitle-' + CompileTag.base_id).innerHTML )
				+'&sample_text='+encodeURIComponent( YAHOO.util.Dom.get('samplet-' + CompileTag.base_id).innerHTML );
			
			if((CompileTag.tag_handler).indexOf('private') >= 0) postData += '&private=1';
			
			postData += '&permalink='+encodeURIComponent( YAHOO.util.Dom.get('reslink-' + CompileTag.base_id).innerHTML );
			YAHOO.util.Connect.asyncRequest('POST', tag_params.addr+'?op=save_tag&'+tag_params.query_append, {
				success: CompileTag.successHandler
			}, postData); 
			
		}
		undo_listener = function(e) {
			YAHOO.util.Event.preventDefault(e);
			CompileTag.destroy();
		}
		YAHOO.util.Event.addListener("tags_save", "click", save_listener);
		YAHOO.util.Event.addListener("tags_undo", "click", undo_listener);
		YAHOO.util.Event.addListener('form-' + CompileTag.base_id, "submit", save_listener);
		
		var d_source = new YAHOO.util.XHRDataSource(tag_params.addr, {
			scriptQueryAppend: tag_params.query_append,
			responseType: YAHOO.util.XHRDataSource.TYPE_FLAT,
			responseSchema: {
				recordDelim: "\n"
			},
		   maxCacheEntries: 60,
		   queryMatchSubset: true
		});
		var auto_c = new YAHOO.widget.AutoComplete('newtags-' + CompileTag.base_id, "tag_suggestion_container", d_source, {
			delimChar: ",",
			maxResultsDisplayed: 6,
			minQueryLength: 3,
			queryDelay: 0.5,
			typeAhead: true,
			autoHighlight: true
		});
	},
	
	// cancel the current overlay displayed
	destroy: function() {
		
		if(!this.overlay) return;
		try {this.overlay.destroy();} catch (e) {return;}
		this.overlay = false;
		this.tag_handler = false;
		this.tag_list = false;
		this.base_id = false;
	},
	
	successHandler: function(o) {

		var parent = YAHOO.util.Dom.get(CompileTag.tag_handler).parentNode;
		
		if (o.responseText != '') {

			parent.childNodes[0].innerHTML = tag_params.lang.update_tags;
			parent.childNodes[1].textContent = ' : ';
		} else {

			parent.childNodes[0].innerHTML = tag_params.lang.add_tags;
			parent.childNodes[1].textContent = '';
		}
		YAHOO.util.Dom.get(CompileTag.tag_list).innerHTML = o.responseText;
		CompileTag.destroy();
	}
	
}
YAHOO.util.Event.onDOMReady(init_tags);