var $ = require('jquery');
require('slick-carousel');

var SliderMenu = (function() {
  $(document).ready(function() {
    var _$menus = $('.slider-menu');
    if (_$menus.length) {
      _$menus.slick({
        accessibility: true,
        infinite: false,
        variableWidth: true,
        draggable: false,
        prevArrow:
            '<div class="slider-menu__arrow slick-prev slick-arrow"><span class="glyphicon glyphicon-menu-left"></span></div>',
        nextArrow:
            '<div class="slider-menu__arrow slick-next slick-arrow"><span class="glyphicon glyphicon-menu-right"></span></div>',
        responsive: [
          {
            breakpoint: 1220,
            settings: {
              draggable: true
            }
          }
        ],
        // Aggiungi questo evento per impostare tabindex su 0 per tutti gli elementi della slide corrente
        onAfterChange: function() {
          _$menus.find('.slider-link').attr('tabindex', '0');
        }
      });

      // Imposta tabindex su 0 per gli elementi iniziali dello slider
      _$menus.find('.slider-link').attr('tabindex', '0');
    }

    if (checkSliderLength(_$menus)) {
      _$menus.addClass('hidden-arrows');
    }

    $(window).on('resize', function() {
      if (window.innerWidth > 767) {
        _$menus.slick('slickGoTo', 0);
      }

      if (window.innerWidth > 1024) {
        if (checkSliderLength(_$menus)) {
          _$menus.addClass('hidden-arrows');
        }
      }
    });
  });

  function checkSliderLength(menu) {
    var _itemsLength = 0;
    var _menuLength = menu.width();
    var $items = menu.find('.slick-slide');

    $items.each(function() {
      _itemsLength += Math.round($(this).width());
    });

    return _itemsLength < _menuLength;
  }
})();

module.exports = SliderMenu;
