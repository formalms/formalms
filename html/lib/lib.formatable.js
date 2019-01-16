$.fn.dataTable.ext.errMode = 'none';

/**
 * forma.lms DataTable wrapper.
 */
function formaTable(dom, options) {

    /**
     * Self variable for scope problems.
     */
    var _thisObj = this;

    /**
     * Options for DataTable.
     */
    var _options = { };
    
    /**
     * Paginated selection.
     */
    this._selection = {
        all: false
      , rows: []
    };



    this._edits = { };

    

    /**
     * Options.
     */
    if(options.rowId !== undefined) {
        _options.rowId = options.rowId;
    }
    if(options.serverSide !== undefined) {
        _options.serverSide = options.serverSide;
    }
    if(options.ajax !== undefined) {
        _options.ajax = options.ajax;
    }    
    if(options.processing !== undefined) {
        _options.processing = options.processing;
    }
    if(options.columns !== undefined) {        
        /**
         * This defaults prevent empty name/content error.
         */
        $(options.columns).each(function() {
            if(!this.name) {
                this.name = this.data;
            }
            if(this.defaultContent === undefined) {
                this.defaultContent = '';
            }
        });

        _options.columns = options.columns;
    }
    if(options.columnsDefs !== undefined) {
        _options.columnsDefs = options.columnsDefs;
    }
    if(options.rowGroup !== undefined) {
        _options.rowGroup = options.rowGroup;
    }
    if(options.paging !== undefined) {
        _options.paging = options.paging;
    }
    if(options.pagingType !== undefined) {
        _options.pagingType = options.pagingType;
    }
    if(options.pageLength !== undefined) {
        _options.pageLength = options.pageLength;
    }
    if(options.lengthChange !== undefined) {
        _options.lengthChange = options.lengthChange;
    }
    if(options.ordering !== undefined) {
        _options.ordering = options.ordering;
    }
    if(options.orderMulti !== undefined) {
        _options.orderMulti = options.orderMulti;
    }
    if(options.order !== undefined) {
        _options.order = options.order;
    }
    if(options.orderFixed !== undefined) {
        _options.orderFixed = options.orderFixed;
    }
    if(options.dom !== undefined) {
        _options.dom = options.dom;
    }
    if(options.buttons !== undefined) {
        _options.buttons = options.buttons;
    }    
    if(options.searching !== undefined) {
        _options.searching = options.searching;
    }    
    if(options.info !== undefined) {
        _options.info = options.info;
    }
    
    /**
     * Select option extension.
     */
    if(options.select !== undefined) {
        /**
         * Use a checkbox for selection.
         */
        _options.select = {
            style:    'multi'
          , selector: 'td:first-child'
        };
        if(options.columns !== undefined) {
            _options.columns = $.merge([{ 
                data: null
              , defaultContent: ''
              , className: 'select-checkbox'
              , orderable: false
              , searchable: false
              , width: 16
            }], _options.columns);
        } else {
            _options.columnsDefs = $.merge(_options.columnsDefs, [{
                targets: 0
              , className: 'select-checkbox'
              , orderable: false 
              , searchable: false
              , width: 16
            }]);
        }

        /**
         * Custom select/deselect all buttons for correct paginated selection/deselection handling.
         */
        if(options.selectAll !== undefined) {
            _options.buttons = $.merge(_options.buttons, [{
                extend: 'selectAll'
              , action: function(e, dt, node, config) {
                    if(!_thisObj._selection.all) {
                        _thisObj._selection.rows = [];
                    }
                    _thisObj._selection.all = true;
                    dt.rows().select();
                }
            }, {
                extend: 'selectNone'
              , action: function(e, dt, node, config) {
                    if(_thisObj._selection.all) {
                        _thisObj._selection.rows = [];
                    }
                    _thisObj._selection.all = false;
                    dt.rows().deselect();
                }
            }]);
        }

        /**
         * Automatic selection for paginated table.
         */
        _options.drawCallback = function(settings) {            
            this.api().rows().every(function(rowIdx, tableLoop, rowLoop) {
                var _inrows = $.inArray(this.id(), _thisObj._selection.rows) > -1;
                if((!_thisObj._selection.all && _inrows) || (_thisObj._selection.all && !_inrows)) {
                    this.select();
                }
            });
        }
    }

    /**
     * Addictional option for adding a specified data value in row class definition.
     */
    if(options.rowClassByData !== undefined) {
        _options.rowCallback = function(row, data, index) {
            $(options.rowClassByData).each(function() {
                $(row).addClass(data[this]);
            });
        }
    }

    if(options.edit !== undefined) {
        this._edits = options.edit;
        this.pickers = {};
        this.trEditing = null;
        this.rows = [];
    } else {
        this._edits = false;
    }

    /**
     * Instance DataTable with the given options.
     */
    var datatable = this._datatable = dom.DataTable(_options);

    /**
     * Handle paginated selection.
     */
    datatable.on('select', function(e, dt, type, indexes) {
        if(type === 'row') {
            if(_thisObj._selection.all) {
                _thisObj._selection.rows = $(_thisObj._selection.rows).not(datatable.rows(indexes).ids()).get();
            } else {
                var _ids = $(datatable.rows(indexes).ids()).not(_thisObj._selection.rows).get();
                _thisObj._selection.rows = $.merge(_thisObj._selection.rows, _ids);
            }
        }
    });

    /**
     * Handle paginated deselection.
     */
    datatable.on('deselect', function(e, dt, type, indexes) {
        if(type === 'row') {
            if(_thisObj._selection.all) {
                var _ids = $(datatable.rows(indexes).ids()).not(_thisObj._selection.rows).get();
                _thisObj._selection.rows = $.merge(_thisObj._selection.rows, _ids);
            } else {
                _thisObj._selection.rows = $(_thisObj._selection.rows).not(datatable.rows(indexes).ids()).get();
            }
        }
    });
    
    if(this._edits) {
        datatable.on('draw.dt', function() {
            // Get all select dropdowns names
            var dropdowns = {};
            $.each(_thisObj._edits.columns, function(i, column) {
                if(column.type == 'select') {
                    dropdowns[column.name] = {fields: []};
                    $.each(column.fields, function(i, item) {
                        dropdowns[column.name].fields.push(item);
                    });
                }
                $(datatable.column(column.name + ':name').nodes())
                    .attr('data-field', column.name)
                    .attr('data-type', column.type);
            });

            datatable.rows().every(function() {
                _thisObj.editTable(this, dropdowns);
            });
        });
    }
}

/**
 * Retrive rows selected.
 */
formaTable.prototype.getSelection = function() {
    return this._selection;
}

/**
 * Reload table data.
 */
formaTable.prototype.reload = function() {
    return this._datatable.ajax.reload();
};

/**
 * Table-edit init.
 */
formaTable.prototype.editTable = function(row, dropdowns) {
    var _thisObj = this;

    // Init the clicked row
    var c = $(row.node()).editable({
        keyboard: true,
        dropdowns,
        maintainWidth: true,
        save: function(values) {
            _thisObj.editTableSave(row, values);
        },
    });

    this.rows.push(c);
};

/**
 * Table-edit inline edit row event.
 */

formaTable.prototype.editTableSave = function(row, values) {
    var _thisObj = this;
    var params = null;

    $.each(_thisObj._edits.columns, function(i, column) {
        if (values[column.name]) {
            var val = values[column.name];
            if (column.type == 'date') {
                val = moment(values[column.name], 'DD-MM-YYYY HH:mm').format("X");
            }

            params = _thisObj._edits.ajax.data;
            params[column.name] = val;
            params.id_lo = row.id();
        }
    });

    if (params) {
        $.ajax({
            type: _thisObj._edits.ajax.type,
            url: _thisObj._edits.ajax.url,
            data: params,
        });
    }
};

/**
 * Add FormaTable to jQuery.
 */
$.fn.FormaTable = function(options) {
    return new formaTable(this, options);
};
