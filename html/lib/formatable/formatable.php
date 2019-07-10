<?php 

    
    // date picker localization
    $regset = Format::instance();
    $date_format = $regset->date_token;
    $date_sep = $regset->date_sep;
    $date_format = str_replace(['%d', '%m', '%Y', '-'], ['dd', 'mm', 'yyyy', '-'], $date_format);
    $_lang = Docebo::user()->getPreference('ui.lang_code'); 
    $date_picker_param = 'data-provide="datepicker" data-date-autoclose=true data-date-language="'.$_lang.'" data-date-format="'.$date_format.'"'; 
    
    

?>
<script type="text/javascript">
                                   
$.fn.dataTable.ext.errMode = 'none';
$.fn.datepicker.noConflict();

$.fn.dataTable.Api.register( 'column().title()', function () {
    var colheader = this.header();
    return $(colheader).text().trim();
} );

                 
$.fn.dataTable.Api.register( 'column().type()', function () {
    var colnumber =  this.index()
    return this.settings()[0].aoColumns[colnumber].sType
} );


$.fn.dataTable.Api.register( 'column().searchable()', function () {
    var colnumber =  this.index()
    return this.settings()[0].aoColumns[colnumber].bSearchable
} );


$.fn.dataTable.Api.register( 'selectable_row()', function () {
    return this.column('.select-checkbox')[0].length == 1
} );


// date comparison
$.fn.dataTable.ext.search.push(
    function( settings, data, dataIndex ) {
         var ret_val = true   
         date_sep  = '<?=$date_sep?>'
         date_format_array =  '<?=$date_format?>'.split(date_sep)
         day_position =  date_format_array.indexOf("dd")
         month_position =  date_format_array.indexOf("mm")
         year_position = date_format_array.indexOf("yyyy")
         $("select[id^='selDate']").each(function(){
               id_column = $(this).attr('id').split('_')[1]
               cell_date_str = data[id_column]
               selected_date_str = $('#search_input_DateA_'+id_column).val()
               operator = parseInt($('#selDateA_'+id_column).val())
               if (selected_date_str != '') {
                   cell_date_array = cell_date_str.split(date_sep)
                   cell_date_d =  new Date(cell_date_array[year_position],
                                          cell_date_array[month_position],                     
                                          cell_date_array[day_position]).getTime()
                   selected_date_array = selected_date_str.split(date_sep)
                   selected_date_d = new Date(selected_date_array[year_position],
                                          selected_date_array[month_position],                     
                                          selected_date_array[day_position]).getTime()
                   switch (operator) {
                       case 0:  // equal
                        ret_val = (selected_date_d == cell_date_d)
                        break;
                       case 1: // greater
                        ret_val =  ( cell_date_d > selected_date_d)
                        break;
                       case 2: //  minor
                        ret_val =  ( cell_date_d < selected_date_d)
                        break;
                       case 3: // greater equal
                        ret_val =  ( cell_date_d >= selected_date_d)
                        break;
                       case 4: //  minor equal
                        ret_val =  ( cell_date_d <= selected_date_d)
                        break;
                   }
               } 
               
             }
        )
        return ret_val;
    }
);

// number comparison

$.fn.dataTable.ext.search.push(
    function( settings, data, dataIndex ) {
         var ret_val = true
         $("select[id^='selNum']").each(function(){
               id_column = $(this).attr('id').split('_')[1]
               cell_num = parseInt(data[id_column])
               selected_num = parseInt($('#search_input_NumA_'+id_column).val())
               operator = parseInt($('#selNumA_'+id_column).val())
               if (!isNaN(selected_num)) {
                   switch (operator) {
                       case 0:  // equal
                        ret_val = (cell_num == selected_num)
                        break;
                       case 1: // greater
                        ret_val =  ( cell_num > selected_num)
                        break;
                       case 2: //  minor
                        ret_val =  ( cell_num < selected_num)
                        break;
                       case 3: // greater equal
                        ret_val =  ( cell_num >= selected_num)
                        break;
                       case 4: //  minor equal
                        ret_val =  ( cell_num <= selected_num)
                        break;
                   }
               } 
               
             }
        )
        return ret_val;
    }
);




                 


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
                                if(data) {
                                    return $.datepicker.formatDate(_thisColumn.edit.format || ($.datepicker.regional[document.documentElement.lang] || $.datepicker.regional['']).dateFormat, new Date(data));
                                } else {
                                    return data;
                                }
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
                                var edit_form_date_view = $('<input name="new_value_view" value="' + $.datepicker.formatDate(_thisColumn.edit.format || ($.datepicker.regional[document.documentElement.lang] || $.datepicker.regional['']).dateFormat, cell.data() ? new Date(cell.data()) : new Date()) + '" readonly />');
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
    } else {
        _options.columnsDefs = [];
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
    } else {
        _options.dom = 'Bfrtip';
    }
    if(options.buttons !== undefined) {
        _options.buttons = options.buttons;
    } else {
        _options.buttons = [];
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
        _options.select = options.select;
        _options.select.selector = 'td:first-child';
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
        if(options.select.all === true) {
            _options.buttons = $.merge(_options.buttons, [{
                extend: 'selectAll',
                text: '<?=Lang::t('_SELECT_ALL', 'standard') ?>'
              , action: function(e, dt, node, config) {
                    if(!_thisObj._selection.all) {
                        _thisObj._selection.rows = [];
                    }
                    _thisObj._selection.all = true;
                    dt.rows({ search: 'applied' }).select();
                }
            }, {
                extend: 'selectNone',
                text: '<?=Lang::t('_UNSELECT_ALL', 'standard') ?>'
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
         * Addictional table buttons for selection actions.
         */
        if(options.selectionActions !== undefined) {
            var _selectionButtons = [];
            $(options.selectionActions.buttons).each(function() {
                var _selectionAction_ajax = this.ajax || null;
                var _selectionAction_text = this.title || '';
                _selectionButtons.push({
                    text: _selectionAction_text
                  , action: function(e, dt, node, config) {
                        _selectionAction_ajax.data = _selectionAction_ajax.data || { }
                        _selectionAction_ajax.data.selection = _thisObj._selection;
                        callAjax(_selectionAction_ajax.url, _selectionAction_ajax.data);
                    }
                });
            });
            var _selectionActions;
            if(options.selectionActions.group) {
                _selectionActions = [{
                    extend: 'collection'
                  , text: options.selectionActions.group
                  , buttons: _selectionButtons
                }];
            } else {
                _selectionActions = _selectionButtons;
            }
            _options.buttons = $.merge(_options.buttons, _selectionActions);
        }

        /**
         * Automatic selection for paginated table.
         */
        this.drawCallbacks.push(function(settings, dt) {
            dt.api().rows().every(function(rowIdx, tableLoop, rowLoop) {
                var _inrows = $.inArray(this.id(), _thisObj._selection.rows) > -1;
                if((!_thisObj._selection.all && _inrows) || (_thisObj._selection.all && !_inrows)) {
                    this.select();
                }
            });
        });
    }

    /**
     * Table action buttons.
     */
    if(options.tableActions !== undefined) {
        var _tableButtons = [];
        $(options.tableActions.buttons).each(function() {
            var _tableAction_link = this.link || null;
            var _tableAction_text = this.title || '';
            _tableButtons.push({
                text: _tableAction_text
              , action: function(e, dt, node, config) {
                    window.location.href = _tableAction_link;
                }
            });
        });
        var _tableActions;
        if(options.tableActions.group) {
            _tableActions = [{
                extend: 'collection'
              , text: options.tableActions.group
              , buttons: _tableButtons
            }];
        } else {
            _tableActions = _tableButtons;
        }
        _options.buttons = $.merge(_options.buttons, _tableActions);
    }

    /**
     * Per row action buttons.
     */
    if(options.rowActions !== undefined) {
        $(options.rowActions).each(function() {
            var _rowAction_data = this.data || null;
            var _rowAction_ajax = this.ajax || null;
            var _rowAction_link = this.link || null;
            var _rowAction_title = this.title || '';
            if(_rowAction_link && _rowAction_link.params) {
                if(_rowAction_link.href.indexOf('?') === -1) {
                    _rowAction_link.href += '?';
                }
            }
            _options.columns.push({
                data: _rowAction_data
              , title: _rowAction_title
              , orderable: false
              , searchable: false
              , className: "text-center"
              , width: 1
              , render: function(data, type, row, meta) {
                    if(type === 'display') {
                        if(_rowAction_ajax) {
                            _rowAction_ajaxUrl = _rowAction_ajax.url;
                            _rowAction_ajaxData = _rowAction_ajax.data || { };
                            if(_rowAction_ajax.params) {
                                $.each(_rowAction_ajax.params, function(param, data) {
                                    _rowAction_ajaxData[param] = row[data];
                                });
                            }
                            var _rowAction_button = $('<a href="' + _rowAction_ajaxUrl + '" class="formatable-action">' + _rowAction_title + '</a>');
                            _rowAction_button.attr('data-ajaxdata', JSON.stringify(_rowAction_ajaxData));
                            return _rowAction_button[0].outerHTML;
                        } else if(_rowAction_link) {
                            var _rowAction_linkHref = _rowAction_link.href;
                            if(_rowAction_link.params) {
                                $.each(_rowAction_link.params, function(param, data) {
                                    _rowAction_linkHref += '&' + param + '=' + row[data];
                                });
                            }
                            var _rowAction_button = $('<a href="' + _rowAction_linkHref + '">' + _rowAction_title + '</a>');
                            return _rowAction_button[0].outerHTML;
                        }
                    } else {
                        return data;
                    }
                }
            });
        });
    }

    this.drawCallbacks.push(function(oSettings, dt) {
        if(oSettings._iDisplayLength > (oSettings.fnRecordsDisplay() + oSettings._iDisplayStart)) {
            $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
        } else {
            $(oSettings.nTableWrapper).find('.dataTables_paginate').show();
        }
    });

    _options.drawCallback = function(settings) {
        var dt = this;
        $(_thisObj.drawCallbacks).each(function() {
            this(settings, dt);
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
    * Language translation
    */
   _options.language = {
                        'sSearch'  :  '<?=Lang::t('_SEARCH', 'standard')?>',
                        'oPaginate': {
                               'sFirst': '<?=Lang::t('_START', 'standard')?>',
                               'sPrevious' : '<?=Lang::t('_PREV_B', 'standard')?>',
                               'sNext': '<?=Lang::t('_NEXT', 'standard')?>',
                               'sLast' : '<?=Lang::t('_END', 'standard')?>'
                              }
                    }    
    
   if (options.language !== undefined) {
        _options.language = $.extend( _options.language, options.language )
   }    

    
    

    /**
     * Instance DataTable with the given options.
     */
    var datatable = this._datatable = dom.DataTable(_options);

    datatable.on('click', '.formatable-action', function(e) {        
        e.preventDefault();
        callAjax($(this).attr('href'), $(this).data('ajaxdata'));
    });

    var callAjax = function(ajaxUrl, ajaxData) {
        $.ajax({ 
            url: ajaxUrl
          , method: 'POST'
          , data: ajaxData
        }).done(function(response) {
            datatable.ajax.reload();
        });
    }

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
 * Retrive the full list of rows selected (only working correctly with non server-based data).
 */
formaTable.prototype.getFlatSelection = function() {
    if(this._selection.all) {
        return $(this._datatable.rows({ search: 'applied' }).ids()).not(this._selection.rows).get();
    } else {
        return this._selection.rows;
    }
}

/**
 * Reload table data.
 */
formaTable.prototype.reload = function() {
    return this._datatable.ajax.reload();
};


formaTable.prototype.searchBar = {
    
    init: function(dt){
        this.instance = $(dt).DataTable();
        this.searchBar =  '.dataTables_scrollHeadInner tr:eq(1)';
        this.initSearchBar()
        $(this.searchBar).hide()
    },
    search_string: function(ix){
        html_tag = '<select id="selString_' + ix + '" name="selString_' + ix + '">'+
                   '<option selected value="0"><?=Lang::t('_STARTS_WITH', 'standard')?></option>'+
                   '<option value="1"><?=Lang::t('_CONTAINS', 'standard')?></option>'+
                    '<option value="2">Uguale a</option>'+
                    '</select>' +
                    '<input id="inputString_' + ix + '" name="inputString_' + ix + '" type="text"  />' 
        return html_tag
        
    },
    search_num: function(ix){
        html_tag = '<select id="selNumA_' + ix + '" name="selNumA_' + ix + '">'+
                   '<option selected value="0">=</option>'+
                   '<option value="1">></option>'+
                   '<option value="2"><</option>'+
                   '<option value="3">>=</option>'+
                   '<option value="4"><=</option>'+                   
                    '</select>' +
                    '<input style="width:50px" id="search_input_NumA_' + ix + '" name="search_input_NumA_' + ix + '"  onkeypress="return event.charCode >= 48 && event.charCode <= 57" onpaste="return false"/><br>' //+
/*                    '<select id="selNumB_' + ix + '" name="selNumB_' + ix + '">'+
                   '<option selected value="0">=</option>'+
                   '<option value="1">></option>'+
                   '<option value="2"><</option>'+
                    '</select>' +
                    '<input style="width:50px" id="search_input_NumB_' + ix + '" name="search_input_NumB_' + ix + '" type="text" />'  */
        return html_tag
    },
    search_date: function(ix) {
        html_tag = '<select id="selDateA_' + ix + '" name="selDateA_' + ix + '">'+
                   '<option selected value="0">=</option>'+
                   '<option value="1">></option>'+
                   '<option value="2"><</option>'+
                   '<option value="3">>=</option>'+
                   '<option value="4"><=</option>'+                   
                    '</select>' +
                    '<input style="width:80px" id="search_input_DateA_' + ix + '" name="search_input_DateA_' + ix + '" type="text"  <?=$date_picker_param?>/><br>' //+
                    /*'<select id="selDateB_' + ix + '" name="selDateB_' + ix + '">'+
                   '<option selected value="0">=</option>'+
                   '<option value="1">></option>'+
                   '<option value="2"><</option>'+
                    '</select>' +
                    '<input style="width:80px" id="search_input_DateB_' + ix + '" name="search_input_DateB_' + ix + '" type="text"  <?=$date_picker_param?>/><br>' */
                 return html_tag        
         
    },             
    show:function(){
       if ($(this.searchBar).is(":visible") ) {
            $(this.searchBar).hide()
       } else {
            $(this.searchBar).show()
            table.columns.adjust().draw('page');
       }    
    },
    redraw:function(){
        is_visible = $(this.searchBar).is(":visible")
        $(this.searchBar).remove() 
        this.initSearchBar()
        if (!is_visible) 
            $(this.searchBar).hide()    
        // clearing current search filter, if any        
        this.instance.search('').columns().search('').draw('')
    },
    initSearchBar: function() {
       try {
            table = this.instance.table()
            $('.dataTables_scrollHeadInner thead tr').clone().appendTo( '.dataTables_scrollHeadInner thead' );
            c = ((table.selectable_row()) ? 0 : 1)
            i = 0
            _parent = this
            table.columns().every(function() {
                s_searchable = this.searchable();
                s_type = this.type();
                s_name = this.title(); 
                s_visible = this.visible();
                if (s_visible) {
                    $('.dataTables_scrollHeadInner tr:eq(1) th:eq('+c+')').removeClass() 
                    if (s_searchable) {
                        switch(s_type) {
                            case 'date':
                                $('.dataTables_scrollHeadInner tr:eq(1) th:eq('+c+')').html(_parent.search_date(i))
                                break
                            case 'num':
                                $('.dataTables_scrollHeadInner tr:eq(1) th:eq('+c+')').html(_parent.search_num(i))
                                break
                            default:
                                $('.dataTables_scrollHeadInner tr:eq(1) th:eq('+c+')').html(_parent.search_string(i)) 
                        }       
                        
                    }
                    c++
                }
                i++
            })
            this.addSearchListener()
        } catch (e) {
           console.log(e.message) 
        }             
        
    },    
    addSearchListener: function(){
        the_table = this.instance
        $("input[id^='inputString_']").on("keyup change", function(){
            
            id_column = $(this).attr('id').split('_')[1]
            var cond = $('#selString_'+id_column).val()    
            var str_search = this.value 
            if (str_search == '')
                    cond = 0
            switch (parseInt(cond)) {
                case 0:
                    str_search = "^" + str_search
                    break
                case 1:
                    str_search = "^.*" + str_search + ".*$" 
                    break
                case 2:
                    str_search = "^" + str_search + "$" 
                    break
                    
            }
         the_table.column(id_column).search( str_search ,true, false).draw()
        });
        $("select[id^='selString_']").on("change", function(){
            id_column = $(this).attr('id').split('_')[1]
            $('#inputString_'+id_column).trigger("keyup")
        })       
        $("select[id^='selDate'], input[id^='search_input_Date']").on("change", function(){
            the_table.draw();
        })
        
        $("select[id^='selNum']").on("change", function(){
            the_table.draw();
        })
        
        $("input[id^='search_input_Num']").on("keyup change", function(){
            the_table.draw();
        });
        
        
        

    }
    
}
   
 
/**
 * Add FormaTable to jQuery.
 */
$.fn.FormaTable = function(options) {
    return new formaTable(this, options);
};


</script>
