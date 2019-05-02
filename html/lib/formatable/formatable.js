$.fn.dataTable.ext.errMode = 'none';
$.fn.datepicker.noConflict();

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

    /**
     * Draw callback functions.
     */
    this.drawCallbacks = [];

    /**
     * DataTable events listeners.
     */
    this.eventsListeners = [];

    /**
     * Options.
     */
    if(options.rowId !== undefined) {
        _options.rowId = options.rowId;
    }

    if (options.data !== undefined){
        _options.data = options.data
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
        $(options.columns).each(function() {
            /**
             * This defaults prevent empty name/content error.
             */
            if(!this.name) {
                this.name = this.data;
            }
            if(this.defaultContent === undefined) {
                this.defaultContent = '';
            }
            if(this.className === undefined) {
                this.className = '';
            }
            /**
             * Edit columns.
             */
            if(this.edit) {
                var _thisColumn = this;
                this.className += " ft-edit ft-edit_" + this.name;
                if(this.render === undefined) {
                    if(this.edit.type === 'select') {
                        this.render = function(data, type, row, meta) {
                            if(type === 'display') {
                                return _thisColumn.edit.options[data];
                            } else {
                                return data;
                            }
                        };
                    }
                    if(this.edit.type === 'date') {
                        this.render = function(data, type, row, meta) {
                            if(type === 'display') {
                                return $.datepicker.formatDate(_thisColumn.edit.format || ($.datepicker.regional[document.documentElement.lang] || $.datepicker.regional['']).dateFormat, new Date(data));
                            } else {
                                return data;
                            }
                        };
                    }
                }
                _thisObj.eventsListeners.push({
                    event: 'click',
                    selector: "tbody td.ft-edit_" + this.name + ":not(.ft-edit-open)",
                    callback: function(event) {
                        if(!$(event.target).closest("td.ft-edit.ft-edit-open").length) {
                            datatable.$('td.ft-edit.ft-edit-open form.ft-edit-popup').trigger("reset");
                        }
                        $(this).addClass('ft-edit-open');
                        var edit_form_popup = $(this).find('form.ft-edit-popup');
                        if(edit_form_popup.length > 0) {
                            $(edit_form_popup).show();
                        } else {
                            var cell = datatable.cell($(this));
                            var edit_form = $('<form class="ft-edit-popup" action="' + options.edit.url + '" method="' + (options.edit.method || 'POST') + '"></form>');
                            $.each(options.edit.data || { }, function(name, value) {
                                edit_form.append('<input type="hidden" name="' + name + '" value="' + value + '" />');
                            });
                            edit_form.append('<input type="hidden" name="' + (options.edit.id || options.rowId || 'id') + '" value="' + datatable.row(cell.index().row).id() + '" />');
                            edit_form.append('<input type="hidden" name="col" value="' + _thisColumn.name + '" />');
                            edit_form.append('<input type="hidden" name="old_value" value="' + cell.data() + '" />');
                            if(_thisColumn.edit.type === 'select') {
                                var edit_form_select = $('<select name="new_value" value="' + cell.data() + '"></select>');
                                $.each(_thisColumn.edit.options, function(value, label) {
                                    var selected = '';
                                    if(cell.data() === value) {
                                        selected = ' selected="selected"';
                                    }
                                    edit_form_select.append('<option value="' + value + '" ' + selected + '>' + label + '</option>');
                                });
                                edit_form.append(edit_form_select);
                            } else if(_thisColumn.edit.type === 'date') {
                                var edit_form_date = $('<input type="hidden" name="new_value" value="' + cell.data() + '" />');
                                var edit_form_date_view = $('<input name="new_value_view" value="' + $.datepicker.formatDate(_thisColumn.edit.format || ($.datepicker.regional[document.documentElement.lang] || $.datepicker.regional['']).dateFormat, new Date(cell.data())) + '" readonly />');
                                edit_form_date.datepicker({ 
                                    dateFormat: 'yy-mm-dd',
                                    altField: edit_form_date_view,
                                    altFormat: $.datepicker.regional[document.documentElement.lang].dateFormat,
                                    changeMonth: true,
                                    changeYear: true,
                                    showOtherMonths: true,
                                    selectOtherMonths: true,
                                    onSelect: function(dateText, inst) { inst.inline = true; },
                                    onClose: function(dateText, inst) { inst.inline = false; }
                                });
                                edit_form_date_view.click(function(event) {
                                    edit_form_date.datepicker($('#ui-datepicker-div').is(':visible') ? 'hide' : 'show');
                                });
                                edit_form.append(edit_form_date_view);
                                edit_form.append(edit_form_date);
                            } else {
                                edit_form.append('<input type="' + (_thisColumn.edit.type || 'text') + '" name="new_value">');
                            }
                            edit_form.append('<button type="submit" class="btn btn-primary ft-edit-save">Save</button>');
                            edit_form.append('<button type="reset" class="btn btn-default ft-edit-cancel">Cancel</button>');
                            $(this).append(edit_form);
                        }
                    }
                });
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
    if(options.scrollX !== undefined) {
        _options.scrollX = options.scrollX;
    }
    if(options.drawCallback !== undefined) {
        this.drawCallbacks.push(options.drawCallback);
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
        this.drawCallbacks.push(function(settings) {
            this.api().rows().every(function(rowIdx, tableLoop, rowLoop) {
                var _inrows = $.inArray(this.id(), _thisObj._selection.rows) > -1;
                if((!_thisObj._selection.all && _inrows) || (_thisObj._selection.all && !_inrows)) {
                    this.select();
                }
            });
        });
    }

    this.drawCallbacks.push(function(oSettings) {
        if(oSettings._iDisplayLength > (oSettings.fnRecordsDisplay() + oSettings._iDisplayStart)) {
            $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
        } else {
            $(oSettings.nTableWrapper).find('.dataTables_paginate').show();
        }
    });

    _options.drawCallback = function(settings) {
        $(_thisObj.drawCallbacks).each(function() {
            this(settings);
        });
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

    datatable.on('reset', 'tbody td.ft-edit form.ft-edit-popup', function(event) {
        $('input.hasDatepicker').each(function() {
            $(this).val(datatable.cell($(this).closest('td')).data());
        });
        $(this).closest('td').removeClass('ft-edit-open');
        $(this).hide();
    });

    datatable.on('submit', 'tbody td.ft-edit form.ft-edit-popup', function(event) {
        event.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            method: $(this).attr('method'),
            data: $(this).serialize()
        }).done(function(response) {
            console.log(response);
            _thisObj.reload();
        });
        $(this).closest('td').removeClass('ft-edit-open');
        $(this).hide();
    });

    $(document).on('click', function() {
        if(!$(event.target).closest("td.ft-edit.ft-edit-open").length && !$(event.target).closest(".ui-datepicker").length) {
            datatable.$('td.ft-edit.ft-edit-open form.ft-edit-popup').trigger("reset");;
        }
    });

    $(this.eventsListeners).each(function() {
        datatable.on(this.event, this.selector, this.callback);
    });
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
 * Add FormaTable to jQuery.
 */
$.fn.FormaTable = function(options) {
    return new formaTable(this, options);
};
