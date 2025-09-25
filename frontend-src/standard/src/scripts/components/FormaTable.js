import Lang from '../helpers/Lang';
import dt from 'datatables.net';
import 'datatables.net-dt'
import 'datatables.net-buttons-dt'

import 'datatables.net-select'
import 'jquery-datatables-checkboxes'
require( 'datatables.net-buttons/js/buttons.colVis.js' );
require('bootstrap-js-buttons/dist/bootstrap-js-buttons.min.js');


/**
 * FormaTable
 */
 class FormaTable {

    constructor(idOrClassOrElement = null, options =  {}) {
     
        // Properties
        this.Name = 'FormaTable';
        /**
         * Options for DataTable.
         */
        this._options = {};

         /**
          * Paginated selection.
          */
         this._selection = {
             all: false,
             rows: [],
             exclusions: []
         };
 
         /**
          * Draw callback functions.
          */
         this.drawCallbacks = [];
 
         /**
          * DataTable events listeners.
          */
         this.eventsListeners = [];

         this.searchBar = {};
        
         this.pageLength = 25;
         this.activeSearch = false;

         // Init
         if(!idOrClassOrElement) {
          this.Error(`constructor() -> undefined target reference ${idOrClassOrElement}`);
        } else {
          this.Element = typeof idOrClassOrElement === 'object' ? idOrClassOrElement : document.querySelector(idOrClassOrElement);
     
        }

        this.registerApi();

        this.setOptions(options, this.eventsListeners, idOrClassOrElement);

        this.setCallbacks();

        this.DataTable = new dt(idOrClassOrElement, this._options);

        this.setActions();

        this.addSearchBar();

        // this.searchBar.init();
    }

    callAjax(ajaxUrl, ajaxData, dt) {
        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: ajaxData
        }).done(function() {
            dt.ajax.reload();
        });
    }

    setOptions(options, eventsListeners, dom) {
        /**
         * Options.
         */

        if (options.rowId !== undefined) {
            this._options.rowId = options.rowId;
        }

        if (options.data !== undefined) {
            this._options.data = options.data
        }

        if (options.serverSide !== undefined) {
            this._options.serverSide = options.serverSide;
        }

        if (options.ajax !== undefined) {
        
            this._options.ajax = options.ajax;
            this._options.ajax.headers = {'X-Signature': window.frontend.config.signature};
        }

        if (options.processing !== undefined) {
        this._options.processing = options.processing;
        }

        if (options.searchDelay !== undefined) {
            this._options.searchDelay = options.searchDelay;
        } else {
            this._options.searchDelay = 500;
        }


        if (options.preselection !== undefined) {
            
           this._selection.rows = $.merge(this._selection.rows, options.preselection);
           
        }

        if (options.columns !== undefined) {

            
          $(options.columns).each(function() {
              /**
               * This defaults prevent empty name/content error.
               */
              if (!this.name) {
                  this.name = this.data;
              }
              if (this.defaultContent === undefined) {
                  this.defaultContent = '';
              }
              if (this.className === undefined) {
                  this.className = '';
              }
              /**
               * Edit columns.
               */

            
              if (this.edit) {
                
                  var _thisColumn = this;
                  this.className += ' ft-edit ft-edit_' + this.name;
                  if (this.render === undefined) {
                    
                      if (this.edit.type === 'select') {
                          this.render = function(data, type) {
                              if (type === 'display') {
                                  return _thisColumn.edit.options[data];
                              } else {
                                  return data;
                              }
                          };
                      } else if (this.edit.type === 'date') {
                          this.render = function(data, type) {
                              if (type === 'display') {
                                  if (data) {
                                      return $.datepicker.formatDate(_thisColumn.edit.format || ($.datepicker.regional[document.documentElement.lang] || $.datepicker.regional['']).dateFormat, new Date(data));
                                  } else {
                                      return data;
                                  }
                              } else {
                                  return data;
                              }
                          };
                      } else if (this.edit.type === 'text') {
                          this.render = function(data, type) {
                            
                              if (type === 'display') {
                                  if (data) {
                                      return data;
                                  }
                              } else {
                                  return data;
                              }
                          };
                      } else if (this.edit.type === 'textarea') {
                          this.render = function(data, type) {
                              if (type === 'display') {
                                  if (data) {
                                      return data;
                                  }
                              } else {
                                  return data;
                              }
                          };
                      }
                  }
                  eventsListeners.push({
                      event: 'click',
                      selector: 'tbody td.ft-edit_' + this.name + ':not(.ft-edit-open)',
                      callback: function(event) {
                          if (!$(event.target).closest('td.ft-edit.ft-edit-open').length) {
                              dom.DataTable.$('td.ft-edit.ft-edit-open form.ft-edit-popup').trigger('reset');
    
                          }
                          $(this).addClass('ft-edit-open');
                          var edit_form_popup = $(this).find('form.ft-edit-popup');
                          if (edit_form_popup.length > 0) {
                              $(edit_form_popup).show();
                          } else {
                              var cell = dom.DataTable.cell($(this));
                              var edit_form = $('<form class="ft-edit-popup" action="' + options.edit.url + '" method="' + (options.edit.method || 'POST') + '"></form>');
                             $.each(options.edit.data || {}, function(name, value) {
                                 edit_form.append('<input type="hidden" name="' + name + '" value="' + value + '" />');
                             });
                              edit_form.append('<input type="hidden" name="' + (options.edit.id || options.rowId || 'id') + '" value="' + dom.DataTable.row(cell.index().row).id() + '" />');
                              edit_form.append('<input type="hidden" name="col" value="' + _thisColumn.name + '" />');
                              edit_form.append('<input type="hidden" name="old_value" value="' + cell.data() + '" />');
                              if (_thisColumn.edit.type === 'select') {
                                  var edit_form_select = $('<select name="new_value" value="' + cell.data() + '"></select>');
                                  $.each(_thisColumn.edit.options, function(value, label) {
                                      var selected = '';
                                      if (cell.data() === value) {
                                          selected = ' selected="selected"';
                                      }
                                      edit_form_select.append('<option value="' + value + '" ' + selected + '>' + label + '</option>');
                                  });
                                  edit_form.append(edit_form_select);
                              } else if (_thisColumn.edit.type === 'date') {
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
                                      onSelect: function(dateText, inst) {
                                          inst.inline = true;
                                      },
                                      onClose: function(dateText, inst) {
                                          inst.inline = false;
                                      }
                                  });
                                  edit_form_date_view.click(function() {
                                      edit_form_date.datepicker($('#ui-datepicker-div').is(':visible') ? 'hide' : 'show');
                                  });
                                  edit_form.append(edit_form_date_view);
                                  edit_form.append(edit_form_date);
                              } else if (_thisColumn.edit.type === 'textarea') {
                                  //#19883
                                  edit_form.append('<textarea name="new_value">' + cell.data() + '</textarea>');
                              } else { // Default input text with type (text, number, ...)
                                  edit_form.append('<input type="' + (_thisColumn.edit.type || 'text') + '" name="new_value" value="' + cell.data() + '">');
                              }
                              edit_form.append('<div class="button-container">');
                              edit_form.find('.button-container').append('<button type="submit" class="btn btn-primary ft-edit-save">Save</button>');
                              edit_form.find('.button-container').append('<button type="reset" class="btn btn-default ft-edit-cancel">Cancel</button>');
                              $(this).append(edit_form);
                          }
                      }
                  });
              }
          });

          this._options.columns = options.columns;
       }

       if (options.columnsDefs !== undefined) {
            this._options.columnsDefs = options.columnsDefs;
        } else {
            this._options.columnsDefs = [];
        }

        if (options.rowGroup !== undefined) {
            this._options.rowGroup = options.rowGroup;
        }

        if (options.paging !== undefined) {
            this._options.paging = options.paging;
        }

        if (options.pagingType !== undefined) {
            this._options.pagingType = options.pagingType;
        }

        if (options.pageLength !== undefined) {
            this._options.pageLength = options.pageLength;
        }

        if (options.lengthChange !== undefined) {
            this._options.lengthChange = options.pageLength;
        }

        if (options.ordering !== undefined) {
            this._options.ordering = options.ordering;
        }

        if (options.orderMulti !== undefined) {
            this._options.orderMulti = options.orderMulti;
        }

        if (options.order !== undefined) {
            this._options.order = options.order;
        }

        if (options.orderFixed !== undefined) {
            this._options.orderFixed = options.order;
        }

        if (options.dom !== undefined) {
            this._options.dom = options.dom;
        } else {
            this._options.dom = 'Bfrtip';
        }

        if (options.buttons !== undefined) {
   
            this._options.buttons = options.buttons;
        } else {
         
            this._options.buttons = [];
        }

        if (options.searching !== undefined) {
            this._options.searching = options.searching;
        }

        if (options.info !== undefined) {
            this._options.info = options.info;
        }

        if (options.scrollX !== undefined) {
            this._options.scrollX = options.scrollX;
        }

        if (options.createdRow !== undefined) {
            this._options.createdRow = options.createdRow;
        }

        if (options.rowCallback !== undefined) {
            this._options.rowCallback = options.rowCallback;
        }

        if (options.stateSave !== undefined) {
            this._options.stateSave = options.stateSave;
        }

        if (options.drawCallback !== undefined) {
            this._options.drawCallback = options.drawCallback;
        }

        /**
         * Select option extension.
         */
        if (options.select !== undefined) {

          /**
           * Use a checkbox for selection.
           */
            this._options.select = options.select;
            this._options.select.selector = 'td:first-child';
            if (options.columns !== undefined) {
                this._options.columns = $.merge([{
                    data: null,
                    defaultContent: '',
                    className: 'select-checkbox',
                    orderable: false,
                    searchable: false,
                    width: 16
                }], this._options.columns);
            } else {
                this._options.columnsDefs = $.merge(this._options.columnsDefs, [{
                    targets: 0,
                    className: 'select-checkbox',
                    orderable: false,
                    searchable: false,
                    width: 16
                }]);
            }

          /**
           * Custom select/deselect all buttons for correct paginated selection/deselection handling.
           */
          if (options.select.all === true) {
         
            var _thisobj = this;
            //var dtable = this.DataTable
            //var rowId = this._options.rowId;
            this._options.buttons = $.merge(this._options.buttons, [{
                 extend : 'selectAll',
                  text:  Lang.Translation('_SELECT_ALL', 'standard'), 
                  className: 'btn btn-default',
                  action: function(e, dt) {
              
               
                      if (!_thisobj._selection.all) {
                        _thisobj._selection.rows = [];
                      }
                      _thisobj._selection.all = true;
                      _thisobj._selection.exclusions = [];
                      dt.rows({
                          search: 'applied'
                      }).select();

                     //_thisobj._selection.rows = dt.rows( { selected: true } );
                     //console.log(_thisobj._selection.rows);
                      //selectionAttr.rows = dt.rows( { selected: true } ).data().map(record => record[rowId]);
                  }
              }, {
                extend : 'selectNone',
                  text: Lang.Translation('_UNSELECT_ALL', 'standard'), 
                  className: 'btn btn-default',
                  action: function(e, dt) {
                      if (_thisobj._selection.all) {
                        _thisobj._selection.rows = [];
                      }
                      _thisobj._selection.exclusions = [];
                      _thisobj._selection.all = false;
                      dt.rows().deselect();

                      _thisobj._selection.rows = [];
                  }
              }]);
          }

          /**
           * Addictional table buttons for selection actions.
           */
          if (options.selectionActions !== undefined) {
              var _selectionButtons = [];
              $(options.selectionActions.buttons).each(function() {
                  var _selectionAction_ajax = this.ajax || null;
                  var _selectionAction_text = this.title || '';
                  _selectionButtons.push({
                      text: _selectionAction_text,
                      action: function(e, dt) {
                          _selectionAction_ajax.data = _selectionAction_ajax.data || {}
                          _selectionAction_ajax.data.selection = this._selection;
                          this.callAjax(_selectionAction_ajax.url, _selectionAction_ajax.data, dt);
                      }
                  });
              });
              var _selectionActions;
              if (options.selectionActions.group) {
                  _selectionActions = [{
                      extend: 'collection',
                      text: options.selectionActions.group,
                      buttons: _selectionButtons
                  }];
              } else {
                  _selectionActions = _selectionButtons;
              }
              this._options.buttons = $.merge(this._options.buttons, _selectionActions);

              
          }


          if(this._options.select.allPage === false) {
            $(this.Element).parent().parent().find("th.select-checkbox").removeClass("select-checkbox");
          }

         
        }

      /**
       * Table action buttons.
       */
        if (options.tableActions !== undefined) {
          var _tableButtons = [];
          $(options.tableActions.buttons).each(function() {
              var _tableAction_link = this.link || null;
              var _tableAction_text = this.title || '';
              _tableButtons.push({
                  text: _tableAction_text,
                  action: function() {
                      window.location.href = _tableAction_link;
                  }
              });
          });
          var _tableActions;
          if (options.tableActions.group) {
              _tableActions = [{
                  extend: 'collection',
                  text: options.tableActions.group,
                  buttons: _tableButtons
              }];
          } else {
              _tableActions = _tableButtons;
          }
          this._options.buttons = $.merge(this._options.buttons, _tableActions);
        }

        
        /**
        * Per row action buttons.
        */
        if (options.rowActions !== undefined) {
          $(options.rowActions).each(function() {
              var _rowAction_data = this.data || null;
              var _rowAction_ajax = this.ajax || null;
              var _rowAction_link = this.link || null;
              var _rowAction_title = this.title || '';
              if (_rowAction_link && _rowAction_link.params) {
                  if (_rowAction_link.href.indexOf('?') === -1) {
                      _rowAction_link.href += '?';
                  }
              }
              this._options.columns.push({
                  data: _rowAction_data,
                  title: _rowAction_title,
                  orderable: false,
                  searchable: false,
                  className: 'text-center',
                  width: 1,
                  render: function(data, type, row) {
                      if (type === 'display') {
                        var _rowAction_button = null;
                          if (_rowAction_ajax) {
                              var _rowAction_ajaxUrl = _rowAction_ajax.url;
                              var _rowAction_ajaxData = _rowAction_ajax.data || {};
                              if (_rowAction_ajax.params) {
                                  $.each(_rowAction_ajax.params, function(param, data) {
                                      _rowAction_ajaxData[param] = row[data];
                                  });
                              }
                              _rowAction_button = $('<a href="' + _rowAction_ajaxUrl + '" class="formatable-action">' + _rowAction_title + '</a>');
                              _rowAction_button.attr('data-ajaxdata', JSON.stringify(_rowAction_ajaxData));
                              return _rowAction_button[0].outerHTML;
                          } else if (_rowAction_link) {
                              var _rowAction_linkHref = _rowAction_link.href;
                              if (_rowAction_link.params) {
                                  $.each(_rowAction_link.params, function(param, data) {
                                      _rowAction_linkHref += '&' + param + '=' + row[data];
                                  });
                              }
                              _rowAction_button = $('<a href="' + _rowAction_linkHref + '">' + _rowAction_title + '</a>');
                              return _rowAction_button[0].outerHTML;
                          }
                      } else {
                          return data;
                      }
                  }
              });
          });
        }

        
      
        /**
       * Addictional option for adding a specified data value in row class definition.
       */
        if (options.rowClassByData !== undefined) {
            this._options.rowCallback = function(row, data) {
                $(options.rowClassByData).each(function() {
                    $(row).addClass(data[this]);
                });
            }
        }

        //  /**
        //   * Language translation
        //   */
        this._options.language = {
            'sSearch': Lang.Translation('_SEARCH', 'standard'),
            'oPaginate': {
                'sFirst': Lang.Translation('_START', 'standard'),
                'sPrevious': Lang.Translation('_PREV_B', 'standard'),
                'sNext': Lang.Translation('_NEXT', 'standard'),
                'sLast': Lang.Translation('_END', 'standard')
            }
        }

        if (options.language !== undefined) {
            this._options.language = $.extend(this._options.language, options.language)
        }
         
    }


    setCallbacks() {
        this.drawCallbacks.push(function(oSettings) {
          if (oSettings._iDisplayLength > (oSettings.fnRecordsDisplay() + oSettings._iDisplayStart)) {
              $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
          } else {
              $(oSettings.nTableWrapper).find('.dataTables_paginate').show();
          }
        });

      // this.drawCallbacks.push(function(settings, dt) {

      //     //var dtable = this.DataTable;
      //
      //     console.log(dt,this);
      //     this.api().rows().every(function() {
      //             var _inrows = $.inArray(this.id(), this._selection.rows) > -1;
      //             if ((!this._selection.all && _inrows) || (this._selection.all && !_inrows)) {
      //                 this.select();
      //             }
      //         });
      // });


        var callbacks = this.drawCallbacks;
        this._options.drawCallback = function(settings) {
           
          var dt = this;

          $(callbacks).each(function() {
          
              this(settings, dt);
          });
         
        }
    }


    setActions() {

        
        var dtable = this.DataTable;
        var _thisobj = this;
        /**
         * Automatic selection for paginated table.
         */

        this.DataTable.on('click', '.formatable-action', function(e) {
            e.preventDefault();
            this.callAjax($(this).attr('href'), $(this).data('ajaxdata'));
        });

        const updateGlobalSelector = () => {
            if(dtable.rows({"selected":true, page: "current"}).count() === dtable.rows({page: "current"}).count()) {
                $(_thisobj.Element).parent().parent().find("th.select-checkbox").addClass("selected");
            } else {
                $(_thisobj.Element).parent().parent().find("th.select-checkbox").removeClass("selected");
            }
        }

        // Selecting/deselecting all
        this.DataTable
        .on('draw', function () {
            if(dtable.selectionDirty) {
                $(_thisobj.Element).parent().parent().find('th.select-checkbox').removeClass("selected");
                dtable.DataTable.rows().deselect();
                dtable.selectionDirty = false;
            }
            updateGlobalSelector();
        }).on('deselect', function() {
            updateGlobalSelector();
        });

        $(_thisobj.Element).parent().parent().on("click", "th.select-checkbox", function() {
            if(!$(this).hasClass("selected")) {
                dtable.rows().select();
                _thisobj.selectionDirty = true;
            } else {
                dtable.rows().deselect()
                _thisobj.selectionDirty = false;
            }
            updateGlobalSelector();
        });

        /**
         * Handle paginated selection.
         */
         this.DataTable.on('select', function(e, dt, type, indexes) {
           
            if (type === 'row') {
                if (_thisobj._selection.all) {
                 
                    _thisobj._selection.rows = $(dtable.rows(indexes).ids()).get();
                    _thisobj._selection.exclusions = $(_thisobj._selection.exclusions).not(dtable.rows(indexes).ids()).get();
                    //console.log('select',_thisobj._selection.exclusions);
                } else {
                    var _ids = $(dtable.rows(indexes).ids()).not(_thisobj._selection.rows).get();
                    _thisobj._selection.rows = $.merge(_thisobj._selection.rows, _ids);
                    
                    
                }
            }
            updateGlobalSelector();
        });

        

        /**
         * Handle paginated deselection.
         */
         this.DataTable.on('deselect', function(e, dt, type, indexes) {
            if (type === 'row') {
                if (_thisobj._selection.all) {
                   
                    _thisobj._selection.rows = []
                    var _exids = $(dtable.rows(indexes).ids()).not(_thisobj._selection.exclusions).get();
                    _thisobj._selection.exclusions = $.merge(_thisobj._selection.exclusions, _exids);
                    //console.log('deselect',_thisobj._selection.exclusions);
                } else {
                    _thisobj._selection.rows = $(_thisobj._selection.rows).not(dtable.rows(indexes).ids()).get();
                }
            }
        
        });

        
        /**
         * Handle page length basing upon data length.
         */
         this.DataTable.on('xhr.dt', function (e, settings, json) {
            
            if (json!== null) {
                if(json.data.length > 0) {
                    if(_thisobj.activeSearch) {
                        dtable.page.len(json.data.length);
                        
                    } else {
                       
                        dtable.page.len(_thisobj.pageLength);
                    }
                    
                } 
            } 
        
        });


        this.DataTable.on('draw', function () {
           
        
            dtable.rows().every(function() {
              
                var _inrows = $.inArray(this.id(), _thisobj._selection.rows) > -1;
                if ((!_thisobj._selection.all && _inrows) || (_thisobj._selection.all && !_inrows)) {
                    this.select();
                }
            });
          
          // Note no return - manipulate the data directly in the JSON object.
      });

        this.DataTable.on('reset', 'tbody td.ft-edit form.ft-edit-popup', function() {
            $('input.hasDatepicker').each(function() {
                $(this).val(dtable.cell($(this).closest('td')).data());
            });
            $(this).closest('td').removeClass('ft-edit-open');
            $(this).hide();
        });

        this.DataTable.on('submit', 'tbody td.ft-edit form.ft-edit-popup', function(event) {
            event.preventDefault();
            $.ajax({
                url: $(this).attr('action'),
                method: $(this).attr('method'),
                data: $(this).serialize()
            }).done(function() {
                _thisobj.reload();
            });
            $(this).closest('td').removeClass('ft-edit-open');
            $(this).hide();
        });

        $(document).on('click', function() {
            if (!$(event.target).closest('td.ft-edit.ft-edit-open').length && !$(event.target).closest('.ui-datepicker').length) {
                dtable.$('td.ft-edit.ft-edit-open form.ft-edit-popup').trigger('reset');
            }
        });

        $(this.eventsListeners).each(function() {
            dtable.on(this.event, this.selector, this.callback);
        });
    }

    getSelection() {
        return this._selection;
    }


    addSearchBar() {
        
        var dt = this.DataTable;
        
        this.searchBar = {

            init: function() {
                this.instance = dt;
                this.searchBar = '.dataTables_scrollHeadInner tr:eq(1)';
                this.initSearchBar()
                $(this.searchBar).hide()
            },
            search_string: function(ix) {
                var html_tag = '<select id="selString_' + ix + '" name="selString_' + ix + '">' +
                    '<option selected value="0">' + Lang.Translation('_STARTS_WITH', 'standard') + ' </option>' +
                    '<option value="1">' + Lang.Translation('_CONTAINS', 'standard') + '</option>' +
                    '<option value="2">Uguale a</option>' +
                    '</select>' +
                    '<input id="inputString_' + ix + '" name="inputString_' + ix + '" type="text"  />'
                return html_tag
    
            },
            search_num: function(ix) {
                var html_tag = '<select id="selNumA_' + ix + '" name="selNumA_' + ix + '">' +
                    '<option selected value="0">=</option>' +
                    '<option value="1">></option>' +
                    '<option value="2"><</option>' +
                    '<option value="3">>=</option>' +
                    '<option value="4"><=</option>' +
                    '</select>' +
                    '<input style="width:50px" id="search_input_NumA_' + ix + '" name="search_input_NumA_' + ix + '"  onkeypress="return event.charCode >= 48 && event.charCode <= 57" onpaste="return false"/><br>' //+
             return html_tag
            },
            search_date: function(ix) {
                var html_tag = '<select id="selDateA_' + ix + '" name="selDateA_' + ix + '">' +
                    '<option selected value="0">=</option>' +
                    '<option value="1">></option>' +
                    '<option value="2"><</option>' +
                    '<option value="3">>=</option>' +
                    '<option value="4"><=</option>' +
                    '</select>' +
                    '<input style="width:80px" id="search_input_DateA_' + ix + '" name="search_input_DateA_' + ix + '" type="text"  <?php echo $date_picker_param; ?>/><br>' //+
                return html_tag
    
            },
            show: function() {
                if ($(this.searchBar).is(':visible')) {
                    $(this.searchBar).hide()
                } else {
                    $(this.searchBar).show()
                    this.instance.columns.adjust().draw('page');
                }
            },
            redraw: function() {
                var is_visible = $(this.searchBar).is(':visible')
                $(this.searchBar).remove()
                this.initSearchBar()
                if (!is_visible)
                    $(this.searchBar).hide()
                // clearing current search filter, if any        
                this.instance.search('').columns().search('').draw('')
            },
            initSearchBar: function() {
                try {
                   
                    var table = this.instance
                    $('.dataTables_scrollHeadInner thead tr').clone().appendTo('.dataTables_scrollHeadInner thead');
                    var c = ((table.selectable_row()) ? 0 : 1)
                    var i = 0
                    var _parent = this
                    table.columns().every(function() {
                        var s_searchable = this.searchable();
                        var s_type = this.type();
                        //var s_name = this.title();
                        var s_visible = this.visible();
                        if (s_visible) {
                            $('.dataTables_scrollHeadInner tr:eq(1) th:eq(' + c + ')').removeClass()
                            if (s_searchable) {
                                switch (s_type) {
                                    case 'date':
                                        $('.dataTables_scrollHeadInner tr:eq(1) th:eq(' + c + ')').html(_parent.search_date(i))
                                        break
                                    case 'num':
                                        $('.dataTables_scrollHeadInner tr:eq(1) th:eq(' + c + ')').html(_parent.search_num(i))
                                        break
                                    default:
                                        $('.dataTables_scrollHeadInner tr:eq(1) th:eq(' + c + ')').html(_parent.search_string(i))
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
            addSearchListener: function() {
               
                var the_table = this.instance
                $('input[id^="inputString_"]').on('keyup change', function() {
    
                    var id_column = $(this).attr('id').split('_')[1]
                    var cond = $('#selString_' + id_column).val()
                    var str_search = this.value
                    if (str_search == '')
                        cond = 0
                    switch (parseInt(cond)) {
                        case 0:
                            str_search = '^' + str_search
                            break
                        case 1:
                            str_search = '^.*' + str_search + '.*$'
                            break
                        case 2:
                            str_search = '^' + str_search + '$'
                            break
    
                    }
                    the_table.column(id_column).search(str_search, true, false).draw()
                });
                $('select[id^="selString_"]').on('change', function() {
                    var id_column = $(this).attr('id').split('_')[1]
                    $('#inputString_' + id_column).trigger('keyup')
                })
                $('select[id^="selDate"], input[id^="search_input_Date"]').on('change', function() {
                    the_table.draw();
                })
    
                $('select[id^="selNum"]').on('change', function() {
                    the_table.draw();
                })
    
                $('input[id^="search_input_Num"]').on('keyup change', function() {
                    the_table.draw();
                });
    
    
    
    
            }
        }
    }

    getFlatSelection() {
        if (this._selection.all) {
            return $(this.DataTable.rows({
                search: 'applied'
            }).ids()).not(this._selection.rows).get();
        } else {
            return this._selection.rows;
        }
    }

    reload() {
        return this.DataTable.ajax.reload();
    }


    registerApi() {

    

        $.fn.dataTable.ext.errMode = 'none';
        //$.fn.datepicker.noConflict();
    
        $.fn.dataTable.Api.register('column().title()', function() {
            var colheader = this.header();
            return $(colheader).text().trim();
        });
    
    
        $.fn.dataTable.Api.register('column().type()', function() {
            var colnumber = this.index()
            return this.settings()[0].aoColumns[colnumber].sType
        });
    
    
        $.fn.dataTable.Api.register('column().searchable()', function() {
            var colnumber = this.index()
            return this.settings()[0].aoColumns[colnumber].bSearchable
        });
    
    
        $.fn.dataTable.Api.register('selectable_row()', function() {
            return this.column('.select-checkbox')[0].length == 1
        });
    
    
        // date comparison
        $.fn.dataTable.ext.search.push(
            function(settings, data) {
                var ret_val = true
                var date_sep = ''
                var date_format_array = ''.split(date_sep)
                var day_position = date_format_array.indexOf('dd')
                var month_position = date_format_array.indexOf('mm')
                var year_position = date_format_array.indexOf('yyyy')
                $('select[id^="selDate"]').each(function() {
                    var id_column = $(this).attr('id').split('_')[1]
                    var cell_date_str = data[id_column]
                    var selected_date_str = $('#search_input_DateA_' + id_column).val()
                    var operator = parseInt($('#selDateA_' + id_column).val())
                    if (selected_date_str != '') {
                        var cell_date_array = cell_date_str.split(date_sep)
                        var cell_date_d = new Date(cell_date_array[year_position],
                            cell_date_array[month_position],
                            cell_date_array[day_position]).getTime()
                        var selected_date_array = selected_date_str.split(date_sep)
                        var selected_date_d = new Date(selected_date_array[year_position],
                            selected_date_array[month_position],
                            selected_date_array[day_position]).getTime()
                        switch (operator) {
                            case 0: // equal
                                ret_val = (selected_date_d == cell_date_d)
                                break;
                            case 1: // greater
                                ret_val = (cell_date_d > selected_date_d)
                                break;
                            case 2: //  minor
                                ret_val = (cell_date_d < selected_date_d)
                                break;
                            case 3: // greater equal
                                ret_val = (cell_date_d >= selected_date_d)
                                break;
                            case 4: //  minor equal
                                ret_val = (cell_date_d <= selected_date_d)
                                break;
                        }
                    }
    
                })
                return ret_val;
            }
        );
    
        // number comparison
    
        $.fn.dataTable.ext.search.push(
            function(settings, data, ) {
                var ret_val = true
                $('select[id^="selNum"]').each(function() {
                    var id_column = $(this).attr('id').split('_')[1]
                    var cell_num = parseInt(data[id_column])
                    var selected_num = parseInt($('#search_input_NumA_' + id_column).val())
                    var operator = parseInt($('#selNumA_' + id_column).val())
                    if (!isNaN(selected_num)) {
                        switch (operator) {
                            case 0: // equal
                                ret_val = (cell_num == selected_num)
                                break;
                            case 1: // greater
                                ret_val = (cell_num > selected_num)
                                break;
                            case 2: //  minor
                                ret_val = (cell_num < selected_num)
                                break;
                            case 3: // greater equal
                                ret_val = (cell_num >= selected_num)
                                break;
                            case 4: //  minor equal
                                ret_val = (cell_num <= selected_num)
                                break;
                        }
                    }
    
                })
                return ret_val;
            }
        );
    
    }

   

}

 /**
 * Javascript plugin FormaTable
 */
Element.prototype.FormaTable = function(options) {
    new FormaTable(this, options);
  }
  
export default FormaTable;