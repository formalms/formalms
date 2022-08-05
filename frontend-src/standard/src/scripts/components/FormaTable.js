import Lang from '../helpers/Lang';
import dt from 'datatables.net';
import 'datatables.net-dt'
import 'datatables.net-buttons'
import 'jquery-datatables-checkboxes'
import 'datatables.net-select'

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
             rows: []
         };
 
         /**
          * Draw callback functions.
          */
         this.drawCallbacks = [];
 
         /**
          * DataTable events listeners.
          */
         this.eventsListeners = [];
        

         // Init
         if(!idOrClassOrElement) {
          this.Error(`constructor() -> undefined target reference ${idOrClassOrElement}`);
        } else {
          this.Element = typeof idOrClassOrElement === 'object' ? idOrClassOrElement : document.querySelector(idOrClassOrElement);
     
        }


        this.setOptions(options, this.eventsListeners, idOrClassOrElement);

    
        this.DataTable = new dt(idOrClassOrElement,this._options);
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
         
            var selectionAttr = this._selection;
            this._options.buttons = $.merge(this._options.buttons, [{
                 extend : 'selectAll',
                  text:  Lang.Translation('_SELECT_ALL', 'standard'), 
                  action: function(e, dt) {
              
                      if (!selectionAttr.all) {
                        selectionAttr.rows = [];
                      }
                      selectionAttr.all = true;
                      dt.rows({
                          search: 'applied'
                      }).select();
                  }
              }, {
                extend : 'selectNone',
                  text: Lang.Translation('_UNSELECT_ALL', 'standard'), 
                  action: function(e, dt) {
                      if (selectionAttr.all) {
                        selectionAttr.rows = [];
                      }
                      selectionAttr.all = false;
                      dt.rows().deselect();
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

          /**
           * Automatic selection for paginated table.
           */
          this.drawCallbacks.push(function(settings, dt) {
              dt.api().rows().every(function() {
                  var _inrows = $.inArray(this.id(), this._selection.rows) > -1;
                  if ((!this._selection.all && _inrows) || (this._selection.all && !_inrows)) {
                      this.select();
                  }
              });
          });

         
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

        this.drawCallbacks.push(function(oSettings) {
          if (oSettings._iDisplayLength > (oSettings.fnRecordsDisplay() + oSettings._iDisplayStart)) {
              $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
          } else {
              $(oSettings.nTableWrapper).find('.dataTables_paginate').show();
          }
        });

        this._options.drawCallback = function(settings) {
          var dt = this;
          $(this.drawCallbacks).each(function() {
              this(settings, dt);
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

}

 /**
 * Javascript plugin FormaFileUploader
 */
Element.prototype.FormaTable = function(options) {
    new FormaTable(this, options);
  }
  
module.exports = FormaTable;