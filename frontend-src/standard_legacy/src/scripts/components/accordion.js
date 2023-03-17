var $ = require('jquery');
var TweenMax = require('gsap');

var Accordion = (function() {
  /**
   * Resets items
   *
   * @param $items
   * @param preserveActiveState
   */
  function resetItems($items, preserveActiveState) {
    $.each($items, function(i, el) {
      var _$this = $(el);

      if (!preserveActiveState && _$this.hasClass('is-active')) {
        _$this.removeClass('is-active');
      }

      if (_$this.hasClass('has-open-subset')) {
        collapseSubset(_$this.next('.accordion__subset'));
      }
    });
  }

  /**
   * Sets an item to active
   *
   * @param $item
   */
  function setActiveItem($item) {
    var _$parentSubset = $item.parents('.accordion__subset');

    if (_$parentSubset.length) {
      $item = _$parentSubset.prev('.accordion__item');
    }

    if (!$item.hasClass('is-active')) {
      resetItems($item.siblings('.accordion__item'));
      $item.addClass('is-active');
    }
  }

  /**
   * Collapses a subset
   *
   * @param $subset
   */
  function collapseSubset($subset) {
    var _$accordion = $subset.children('.accordion');
    var _$items = _$accordion.children('.accordion__item');
    var _duration = Math.max(_$items.length * 0.1, 0.3);

    resetItems(_$items);
    $subset.prev('.accordion__item').removeClass('has-open-subset');
    $subset.removeClass('is-open');

    TweenMax.to($subset, _duration, {
      height: 0,
      ease: window.Power2.easeInOut
    });
  }

  /**
   * Expands subset
   *
   * @param $subset
   */
  function expandSubset($subset) {
    var _$accordion = $subset.children('.accordion');
    var _$items = _$accordion.children('.accordion__item');
    var _duration = Math.max(_$items.length * 0.1, 0.4);

    $subset.prev('.accordion__item').addClass('has-open-subset');
    $subset.addClass('is-open');

    TweenMax.fromTo(
      $subset,
      _duration,
      { height: 0 },
      {
        height: _$accordion.outerHeight(true),
        ease: window.Power2.easeInOut
      }
    );
  }

  /**
   * Accordion class
   */
  function Accordion(selector) {
    this.$selector = $(selector);

    // setting pre open subsets' height
    $.each(this.$selector.find('.accordion__subset.is-open'), function(i, el) {
      TweenMax.set(el, {
        height: $(el)
          .children('.accordion')
          .outerHeight(true)
      });
    });

    // toggling items' state on click
    this.$selector.find('.accordion__item').on('click', function(e) {
      e.preventDefault();
      setActiveItem($(this));
    });

    this.$selector.find('.js-accordion-toggle-subset').on(
      'click',
      function(e) {
        e.stopPropagation();

        var _$item = $(e.currentTarget).parent('.accordion__item');
        var _$subset = _$item.next('.accordion__subset');

        if (_$subset.hasClass('is-open')) {
          collapseSubset(_$subset);
        } else {
          this.closeAll(true);
          expandSubset(_$subset);
        }
      }.bind(this)
    );
  }

  /**
   *
   * @param includeSubsets
   * @returns {*}
   */
  Accordion.prototype.getItems = function(includeSubsets) {
    if (includeSubsets) {
      return this.$selector.find('.accordion__item');
    }

    return this.$selector.children('.accordion__item');
  };

  /**
   *
   * @param preserveActiveState
   */
  Accordion.prototype.closeAll = function(preserveActiveState) {
    resetItems(this.getItems(), preserveActiveState);
  };

  return Accordion;
})();

module.exports = Accordion;
