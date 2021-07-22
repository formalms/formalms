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

/**
 * The DynamicUserFilter class manage the filter composition and handling
 * @module user_Selector
 * @titile Dynamic User Filter
 */
function DynamicUserFilter(containerId, oConfig) {
 
    if (containerId) {
        oConfig = oConfig || {};
        this.id = containerId;
        this.oContainerEl = YAHOO.util.Dom.get(containerId);
        this.init(oConfig);
    }
}

DynamicUserFilter.prototype = {

    /**
     * Keeps the id of the markup element
     * @property id
     * @type String
     */
    id: null,

    /**
     * Keeps the DOM Object of the container
     * @property oContainerEl
     * @type String
     */
    oContainerEl: null,

    /**
     * If true create the hidden input field that will contain the current selection
     * @property useInput
     * @type Boolean
     */
    useInput: false,

    /**
     * Keeps the DOM Object of the input hidden
     * @property useInput
     * @type Boolean
     */
    oInputEl: null,
	
	/*
     * Contains the translations
	 * @property oLang
	 * @type Object
     */
	oLang: {},/*
        _ADD: "Aggiungi",
        _REMOVE: "Rimuovi",
        _INCLUSIVE: "tutte le condizioni.",
        _EXCLUSIVE: "almeno una condizione."
    },*/

	/**
     * @property oFields
     * @type Object
     */
    oFields: {},

	/**
     * If true the conditions are mathced with AND, if false with OR
     * @property filterExclusive
     * @type Boolean
     */
    filterExclusive: true,

    /**
	 * The table element which contains the displayed filter conditions
	 * @property oFilterTable
	 * @type HTML Table Element
	 * @private
	 */
    oFilterTable: null,

    /**
	 * The filter internal list, provides a list operations abstraction
	 * @property oFilterList
	 * @type object
	 * @private
	 */
    oFilterList: {

        container: null, //the selector object, set in the init function

        list: {}, //a collection of filter conditions

        count: 0, //number of elements in the list

        filterIndexCounter: 0, //a sort of auto-increment value used for unique indexes in a variable list

		/**
		 * Add a node filter 
		 * @property add
		 * @type function
		 * @private
		 */
        add: function(oField, val) {
            var index = this.filterIndexCounter;
            var fname = "filter_"+index;
            this.list[fname] = {
                id: index,
                field: oField.id,
                type: oField.type,
                value: (val ? val : "")
            };
            this.filterIndexCounter++;
            this.count++;
            return this.list[fname];//index;
        },

		/**
		 * Remove a node filter 
		 * @property del
		 * @type function
		 * @private
		 */
        del: function(index) {
            var result, filter = "filter_"+index;
            if (this.list[filter]) {
                try {
                    delete this.list[filter];
                    result=true;
                    this.count--;
                } catch(e) {
                    result=false;
                }
            } else {
                result=false;
            }
            return result;
        },

        mod: function(filter, newValue) {

        },

        isValidFilter: function(oFilter) {
            return true;//to do ...
        },

		/**
		 * Refresh the html table with the js value
		 * @property refreshValues
		 * @type function
		 * @private
		 */
        refreshValues: function() {
            if (this.container) {
                for (var x in this.list) {
                    this.list[x].value = this.container.oFieldTypes.get( this.list[x].type ).getValue(this.container.id, this.list[x].id);
                }
            }
        }

    },

    oFieldTypes: {
        list: {},

        set: function(oFieldType) {
            this.list[oFieldType.type] = oFieldType;
        },

        get: function(type) {
            return this.list[type];
        }
    },

    getFieldById: function(id) {
        var res;
        if (this.oFields[id]) res=this.oFields[id]; else res=false;
        return res;
    },

    loadFieldTypes: function(fieldTypes) {
      for (var i=0; i<fieldTypes.length; i++) {
        this.oFieldTypes.set(fieldTypes[i]);
      }
    },
    
    loadFields: function(fields) {
      for (var i=0; i<fields.length; i++) this.oFields[fields[i].id] = fields[i];
    },

    /**
	 * Initializes the selector
	 * @method init
	 * @param {object} conf the configuration of the object
	 * @private
	 */
    init: function(conf) {
        //set configuration
        var i,t;

		if(conf.lang) this.oLang = conf.lang;

        //set fields
        if (conf.fields) {
            for (i=0; i<conf.fields.length; i++) this.oFields[conf.fields[i].id] = conf.fields[i];
        }

        //set actions for each field type
        if (conf.fieldTypes) {
            for (i=0; i<conf.fieldTypes.length; i++) this.oFieldTypes.set(conf.fieldTypes[i]);
        }

        //prepare an input hidden for use in forms
        if (conf.use_form_input) {
            this.useInput = true;
            var input = document.createElement('INPUT');
            input.id = this.id+"_input";
            input.type = "hidden";
            input.name = this.id+"_input";
            this.oContainerEl.appendChild(input);
            this.oInputEl = input;//YAHOO.util.Dom.get(this.id+"_input");
            YAHOO.util.Event.addListener(this.oInputEl.form, "submit", function(e) {
                this.oInputEl.value = encodeURI( this.toString() );
            }, this, true);
        }

        //draw
        this.render();

        //set scope for filter list object
        this.oFilterList.container = this;

        //load initial filter conditions, if exist (an initial field : {idfield, value})
        if (conf.initial_filters) {
            for (i=0; i<conf.initial_filters.length; i++) {
                t = conf.initial_filters[i]; //alert(i+') ID: '+t.id_field+'; VALUE: '+t.value);
                this.oFilterList.add(this.getFieldById(t.id_field), stripSlashes(t.value));
            }
            this.refreshTable();
        }
        
        if (conf.initial_exclusiveness) {
          switch (conf.initial_exclusiveness) {
            case "AND":this.filterExclusive = true;break;
            case "OR":this.filterExclusive = false;break;
          }
        }
    },

	/**
	 * Draw the selector
	 * @method render
	 * @private
	 */
    render: function() {
        var $D = YAHOO.util.Dom, $E = YAHOO.util.Event;

        var i, opt, 
			label = document.createElement('LABEL'), 
			sel = document.createElement('SELECT');
		label.htmlFor = "add_filter_selection";
		label.className = "add_filter";
		label.innerHTML = this.oLang._ADD_FILTER;
        sel.id = "add_filter_selection";
		sel.className = "add_filter";
        if (this.oFields!=={}) {
            for (i in this.oFields) {
                opt = document.createElement('OPTION');
                opt.value = this.oFields[i].id;
                opt.text = this.oFields[i].name;
                try {
                    sel.add(opt, null);
                } catch(e) {
                    sel.add(opt);
                }
				//sel.options.push( new Option(this.oFields[i].name, this.oFields[i].id) );
            }
        }
		
		// action frame -------------------------------------------------
        var div = document.createElement('div');
        div.id = this.id+"_action_frame";
		div.className = 'action_frame';
		div.appendChild(label);
        div.appendChild(document.createTextNode(' '));
        div.appendChild(sel);
        div.appendChild(document.createTextNode(' '));
		//div.appendChild(but);
        this.oContainerEl.appendChild(div);
		
		var but = new YAHOO.widget.Button({ 
			label:this.oLang._ADD, 
			id:"add_filter_button", 
			container:div });
		
		but.on("click", function(e) {
			this.addFilterAction( this.getFieldById( YAHOO.util.Dom.get("add_filter_selection").value ) );
			YAHOO.util.Event.stopEvent(e);
        }, this, true)
		// table that will contains the rules ---------------------------
        div = document.createElement('div');
        div.id = this.id+"_filtertable_frame";
		div.className = "filters_list";
        var tab = document.createElement('TABLE');
        tab.id = "filters_list";
        div.appendChild(tab);
        this.oContainerEl.appendChild(div);
        this.oFilterTable = $D.get("filters_list");

		// filter conditions --------------------------------------------
        div = document.createElement('div');
        div.id = this.id+"_filteroptions";
		div.className = 'filter_options';
        i = this.filterExclusive;
        div.innerHTML = ''+
			'<input type="radio" id="'+this.id+'_exclusive"'+' name="'+this.id+'_setfilterexclusive" '+(i ? ' checked="checked"' : '')+'/>'+
			'<label for="'+this.id+'_exclusive">'+this.oLang._FILTER_ALL_CONDS+'</label>'+
			'<input type="radio" id="'+this.id+'_inclusive"'+' name="'+this.id+'_setfilterexclusive" '+(i ? '' : ' checked="checked"')+'/>'+
			'<label for="'+this.id+'_inclusive">'+this.oLang._FILTER_ONE_COND+'</label>';
        this.oContainerEl.appendChild(div);
        $E.addListener(this.id+'_exclusive', "click", function(e) {
            this.filterExclusive = true;
        }, this , true);
        $E.addListener(this.id+'_inclusive', "click", function(e) {
            this.filterExclusive = false;
        }, this , true);
		
		// submit frame, not used yet ----------------------------------
        div = document.createElement('div');
        div.id = this.id+"_submit_frame";
        div.innerHTML = '';
        this.oContainerEl.appendChild(div);

    },

    refreshTable: function() {
        var i, t = this.oFilterList;
        //clear all displayed lines
        //this.oFilterTable.rows = [];
        for (i=0; i<this.oFilterTable.rows.length; i++) {this.oFilterTable.deleteRow(0);}

        //redraw the rows
        for (i in t.list) {
            this.addTableRow(t.list[i]);
        }
    },

    addTableRow: function(oFilter) {
        var findex = oFilter.id, row = this.oFilterTable.insertRow(0);
        row.id = "filter_row_"+findex;

        var oField = this.getFieldById(oFilter.field);
        var col1 = row.insertCell(-1), col2 = row.insertCell(-1), col3 = row.insertCell(-1);
        col1.className = 'first_col';
        col1.innerHTML = oField.name;

        var t = this.oFieldTypes.get(oField.type);
        t.render(this.id, findex, col2, oField.id);
        t.setValue(this.id, findex, oFilter.value);

        col3.innerHTML = '<a class="remlink" id="rembutton_'+findex+'" href="javascript:return false;">'+this.oLang._DEL+'</a>';
        YAHOO.util.Event.on("rembutton_"+findex, "click", function(e) {
            this.scope.delFilterAction(this.idFilter);return false;
					}, {
            scope: this,
            idFilter: findex
					}, true);

        return row;
    },

    deleteTableRow: function(idFilter) {
        this.oFilterTable.deleteRow(YAHOO.util.Dom.get("filter_row_"+idFilter).rowIndex);
    },

    /**
	 * Create a new filter and let user to set it
	 * @method addFilterAction
	 * @param (oField) string, type the type of filter to be created
	 * @private
	 */
    addFilterAction: function(oField) {
        //first create a new filter in internal list
        var filter = this.oFilterList.add(oField);
        this.addTableRow(filter);
    },

    delFilterAction: function(idFilter) {
        //remove from internal list and from table
        this.oFilterList.del(idFilter);
        this.deleteTableRow(idFilter);//this.oFilterTable.deleteRow(YAHOO.util.Dom.get("filter_row_"+idFilter).rowIndex);
    },

	resetFilter: function() {
		var x, t = this.oFilterList;
		//clear all displayed lines and delete internal filter objects
		for (x in t.list) {
			this.delFilterAction(t.list[x].id);
		}
	},

    toString: function() {
        var f = this.oFilterList;
        f.refreshValues();
        var x, filters = [];
        for (x in f.list) {
            filters.push( {
                id_field: f.list[x].field,
                type: f.list[x].type, //maybe redoundant, we already have the id from which we can know the type
                value: f.list[x].value
            });
        }
        return YAHOO.lang.JSON.stringify({
            exclusive: this.filterExclusive,
            filters: filters
        });
    }
	
}

//types rendering
YAHOO.namespace("dynamicFilter.renderTypes");
YAHOO.dynamicFilter.renderTypes = {

	get: function(sType, oArgs) {
		return this['_type_'+sType](oArgs);
	},

	/* text field type */
	_type_textfield: function(oParams) {
		var _TYPE = "textfield";
		return {
			type: _TYPE,
			getValue: function(id_sel, id_filter) {
				var o, id = _TYPE+"_"+id_filter+"_"+id_sel, $D = YAHOO.util.Dom;
				return YAHOO.lang.JSON.stringify({
					cond: $D.get(id+"_sel").value,
					value: $D.get(id).value
				});
			},
			setValue: function(id_sel, id_filter, newValue) {
				var o;
				if (!newValue)
					o = {cond: 0, value: ""};
				else
					o = YAHOO.lang.JSON.parse(newValue);
				var i, s, id = _TYPE+"_"+id_filter+"_"+id_sel, $D = YAHOO.util.Dom;
				$D.get(id).value = o.value;
				s = $D.get(id+"_sel");
				for (i=0; i<s.options.length; i++) {
					if (s.options[i].value == o.cond) {
						s.selectedIndex = i;
						break;
					}
				}
			},
			render: function(id_sel, id_filter, oEl, id_field) {
				var t = document.createElement("INPUT"), d = document.createElement("DIV"), s = document.createElement("SELECT");
				s.className = "condition_select";
				d.className = "textfield_container";

				d.className = _TYPE+"_container";
				t.type = _TYPE;t.id = "text_"+id_filter+"_"+id_sel;s.id = t.id+"_sel";t.className = "filter_value";

				s.options[0] = new Option(oParams._CONTAINS,0);
				s.options[1] = new Option(oParams._NOT_CONTAINS,1);
				s.options[2] = new Option(oParams._EQUAL,2);
				s.options[3] = new Option(oParams._NOT_EQUAL,3);
				
				oEl.appendChild(s);
				oEl.appendChild(document.createTextNode(" "));
				oEl.appendChild(t);
			}
		};
	},


	/* date field type */
	_type_date: function(oParams) {
		return {
			type: "date",
			getValue: function(id_sel, id_filter) {
				var o, id = "date_"+id_filter+"_"+id_sel, $D = YAHOO.util.Dom;
				return YAHOO.lang.JSON.stringify({
					cond: $D.get(id+"_sel").value,
					value: $D.get(id).value
				});
			},
			setValue: function(id_sel, id_filter, newValue) {
				var o, $D = YAHOO.util.Dom;
				if (!newValue)
					o = {cond: 0, value: ""};
				else
					o = YAHOO.lang.JSON.parse(newValue);
				var i, s, id = "date_"+id_filter+"_"+id_sel;
				$D.get(id).value = o.value;
				s = $D.get(id+"_sel");
				for (i=0; i<s.options.length; i++) {
					if (s.options[i].value == o.cond) {
						s.selectedIndex = i;
						break;
					}
				}
			},
			render: function(id_sel, id_filter, oEl, id_field) {
				var id = "date_"+id_filter+"_"+id_sel, txt = document.createElement("INPUT");
				txt.id = id;
				txt.className = "filter_value";
				txt.type = "text";

				var d = document.createElement("DIV");
				d.className = "date_container";
				var sel = document.createElement("SELECT");
				sel.id = id+"_sel";
				sel.options[0] = new Option("<",0);
				sel.options[1] = new Option("<=",1);
				sel.options[2] = new Option("=",2);
				sel.options[3] = new Option(">=",3);
				sel.options[4] = new Option(">",4);
				sel.className = "condition_select";

				d.appendChild(sel);
				d.appendChild(document.createTextNode(" "));
				d.appendChild(txt);
				oEl.appendChild(d);

				YAHOO.dateInput.setCalendar(id, "", oParams.format);
			}
		};
	},


	/* dropdown field type */
	_type_dropdown: function(oParams) {
		return {
			type: "dropdown",
			getValue: function(id_sel, id_filter) {
				return YAHOO.util.Dom.get("dropdown_"+id_filter+"_"+id_sel).value;
			},
			setValue: function(id_sel, id_filter, newValue) {
				if (!newValue) newValue=0;
				var i, s = YAHOO.util.Dom.get("dropdown_"+id_filter+"_"+id_sel);
				for (i=0; i<s.options.length; i++) {
					if (s.options[i].value == newValue) {
						s.selectedIndex = i;
						break;
					}
				}
			},
			render: function(id_sel, id_filter, oEl, id_field) {
				var i, sons = oParams, s = document.createElement("SELECT"), d = document.createElement("DIV");

				s.className = "dropdown_filter_value";
				if (id_field.split("_")[0] == "std") return; //at the moment dropdown are not allowed for standard fields

				var t_index = "field_"+id_field.split("_")[1];
				var opt, t = t_index in sons ? sons[t_index] : [];
				d.className = "dropdown_container";s.id = "dropdown_"+id_filter+"_"+id_sel;
				for (i=0; i<t.length; i++) {
					opt = document.createElement("OPTION");
					opt.value = t[i].value;
					opt.text = t[i].text;
					try {s.add(opt, null);} catch(e) {s.add(opt);}
				}
				d.appendChild(s);
				oEl.appendChild(d);
			}
		};
	}

}