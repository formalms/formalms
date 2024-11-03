/**
 * Custom select js module
 * @module Customselect
 * @requires jquery
 */

var $ = require('jquery');

var Customselect = (function() {
  /**
   * This initialize new Customselect for each $('.js-customselect') element
   * @memberof Customselect
   * @inner
   */
  function canStart() {
    $('.js-customselect').each(function() {
      new Customselect(this);
    });
  }

  /**
   * This initialize new Customselect
   * @exports Customselect
   * @class
   * @param {jQuery} el - jQuery select $('.js-customselect')
   * @example
   * <select class="js-customselect">
   *     <option value="0">0</option>
   *     <option value="1">1</option>
   *     ...
   *     <option value="x">x</option>
   * </select>
   */
  function Customselect(el) {
    this.el = $(el);
    this.init();
  }

  /**
   * This create new tag and append it to the DOM, then wrap original select into it
   * @memberof Customselect
   */
  Customselect.prototype.init = function() {
    var _parentClass = this.el.attr('data-class') || '';
    var _showArrow = this.el.attr('data-arrow') || 'true';
    var _arrowBlock =
      _showArrow === 'true'
        ? '<i class="fa fa-chevron-down" style="float:right;"></i>'
        : '';
    this.el.css({
      display: 'block',
      opacity: 0,
      position: 'absolute',
      width: '100%',
      height: '100%',
      top: 0,
      left: 0
    });
    this.el.wrap(
      '<div class="form__customselect ' +
        _parentClass +
        '" style="position:relative;"></div>'
    );
    this.el.before(
      '<span class="form__customselect__text js-cutomselect--text"></span>' +
        _arrowBlock
    );
    this.el
      .siblings('.js-cutomselect--text')
      .html(this.el.find('option:selected').text());
    this.el.on('change', function() {
      $(this)
        .siblings('.js-cutomselect--text')
        .html(
          $(this)
            .find('option:selected')
            .text()
        );
    });
  };

  canStart();

  return Customselect;
})();

module.exports = Customselect;
