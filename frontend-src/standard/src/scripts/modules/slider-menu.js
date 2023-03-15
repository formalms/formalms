var $ = require('jquery');
require('slick-carousel');

var SliderMenu = (function() {
  $(document).ready(function() {
    var _$menus = $('.slider-menu');
    if (_$menus.length) {
      _$menus.slick({
        infinite: false,
        variableWidth: true,
        draggable: false,
        prevArrow:
          '<div class="slider-menu__arrow slick-prev slick-arrow"><span class="glyphicon glyphicon-menu-left"></span><span class="glyphicon glyphicon-menu-left"></span></div>',
        nextArrow:
          '<div class="slider-menu__arrow slick-next slick-arrow"><span class="glyphicon glyphicon-menu-right"></span><span class="glyphicon glyphicon-menu-right"></span></div>',
        responsive: [
          {
            breakpoint: 1220,
            settings: {
              draggable: true
            }
          }
        ]
      });
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
    var _itemsength = 0;
    var _menuLength = menu.width();
    var $items = menu.find('.slick-slide');

    $items.each(function() {
      _itemsength += Math.round($(this).width());
    });

    return _itemsength < _menuLength;
  }
})();

module.exports = SliderMenu;
