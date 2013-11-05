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

// Drag &Drop node class --------------------------------------------
YAHOO.util.DDNode = function(id, sGroup, oConfig) {
	try { 
		YAHOO.util.DDNode.superclass.constructor.apply(this, arguments);
	} catch(e) {}

};

YAHOO.extend(YAHOO.util.DDNode, YAHOO.util.DDProxy, {

	stree: false,
	/**
	 * Event handler, is called after 2 sec of mousedown on the object or 3pixel drag
	 * @param (Event) the drag event
	 */
	startDrag: function(e) {
		var Dom = YAHOO.util.Dom;
		var dragEl = this.getDragEl();
		var clickEl = this.getEl();
		Dom.setStyle(clickEl, "opacity", 0.6);

		dragEl.innerHTML = clickEl.innerHTML;
		Dom.addClass(dragEl, 'dragnode');
		/*Dom.setStyle(dragEl, "color", Dom.getStyle(clickEl, "color"));
		Dom.setStyle(dragEl, "backgroundColor", Dom.getStyle(clickEl, "backgroundColor"));
		Dom.setStyle(dragEl, "border", "2px solid gray");*/
	},

	endDrag: function(e) {
		var Dom = YAHOO.util.Dom;
		var srcEl = this.getEl();
		var proxy = this.getDragEl();

		// Show the proxy element and animate it to the src element's location
		Dom.setStyle(proxy, "visibility", "");
		var a = new YAHOO.util.Motion(
			proxy, {
				points: {
					to: Dom.getXY(srcEl)
				}
			},
			0.2,
			YAHOO.util.Easing.easeOut
		)
		var proxyid = proxy.id;
		var thisid = this.id;

		// Hide the proxy and show the source element when finished with the animation
		a.onComplete.subscribe(function() {
				Dom.setStyle(proxyid, "visibility", "hidden");
				Dom.setStyle(thisid, "opacity", 1);
			});
		a.animate();

		//this.dragdropAction(e);
	},

	/**
	 * Event handler, is called when the dragged element is hover a target element
	 * @param (Event) the drag event
	 */
	onDragEnter: function(e, id) {
		if(id == this.id) return;
		this.highlight(id);
		var dest_node = this.stree._tree.getNodeByElement(YAHOO.util.Dom.get(id));
	},

	/**
	 * Event handler, is called when the dragged element is relased on a non target element
	 * @param (Event) the drag event
	 */
	onInvalidDrop: function(e) {
		this.backToStart();
	},

	/**
	 * Event handler, is called when the dragged element leave a target element
	 * @param (Event) the drag event
	 */
	onDragOut: function(e, id) {
		if(id == this.id) return;
		//YAHOO.util.Dom.get(id).style.background = this.prev_bck;
		this.de_highlight(id);
	},

	/**
	 * Event handler, is called when the dragged element is released on a target element
	 * @param (Event) the drag event
	 */
	onDragDrop: function(e, id) {

		this.de_highlight(id);
		if(id != this.id) {
			// move the tree folder
			//stree = src_node.tree.myManager;
			var stree = this.stree;
			
			var src_node = stree._tree.getNodeByElement(YAHOO.util.Dom.get(this.id));
			var dest_node = stree._tree.getNodeByElement(YAHOO.util.Dom.get(id));
			var copy_from = src_node.parent;

			//illegal moves
			var isAncestor = function(s, d) {
				if (d.isRoot()) return false;
				if (d == s) return true;
				return isAncestor(s, d.parent);
			}

			if (dest_node == copy_from || isAncestor(src_node, dest_node)) {
				this.backToStart();
				return;
			}

			dest_node.isLeaf = false;
			var sid = src_node.contentElId;
			var label = src_node.label_name;

			

			var o = stree._dragdropEvent;
			o.eventFunction.call(o.eventScope, src_node, dest_node, {oDD: this});
/*
			stree._tree.popNode(src_node);
			src_node.appendTo(dest_node);
			
			this.backToStart();
			copy_from.refresh();
			dest_node.refresh();
			stree._alternateLines();
*/
			//re-set the drag handler
			//this.setListenerForNode(stree.root);
		} else {

			this.backToStart();
		}
	},


	onDrag: function(e) {
		var Dom = YAHOO.util.Dom;
		var srcEl = this.getEl();
		var proxy = this.getDragEl();

		var pxy = Dom.getXY(proxy), txy = Dom.getXY(e.target);
	},


	/**
	 * Reset all the drag & drop listener for a node and his childrens nodes
	 * @private
	 */
	setListenerForNode: function(node) {

		if(!node.dd_obj) node.dd_obj = new YAHOO.util.DDNode(node.contentElId, "treegroup");
		if(!node.dt_obj) node.dt_obj = new YAHOO.util.DDTarget(node.contentElId, "treegroup");

		for(var i=0; i<node.children.length; i++) {

			if(!node.children[i].dd_obj) node.children[i].dd_obj = new YAHOO.util.DDNode(node.children[i].contentElId, "treegroup");
			if(!node.children[i].dt_obj) node.children[i].dt_obj = new YAHOO.util.DDTarget(node.children[i].contentElId, "treegroup");

			if(node.children[i].children.length != 0) {

				this.setListenerForNode(node.children[i]);
			}

		}

	},

	/**
	 * Higlight an element
	 * @param (string) id
	 */
	highlight: function(id) {
		this.prev_bck = YAHOO.util.Dom.get(id).style.background;
		YAHOO.util.Dom.get(id).style.background = '#efefef';
	},

	/**
	 * Remove the iglight from the element
	 * @param (string) id
	 */
	de_highlight: function(id) {
		YAHOO.util.Dom.get(id).style.background = this.prev_bck;
	},

	/**
	 * Animate the return of the dragged item to his original position
	 * @param (string) id
	 */
	backToStart: function() {

		new YAHOO.util.Motion(this.id,
		{
			points: {
				to: this.startPos
			}
		},
		0.3,
		YAHOO.util.Easing.easeOut
		).animate();
	}

});
