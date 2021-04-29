require('../modules/course-box');
require('../modules/folder-view');
require('../modules/slider-menu');
require('../modules/text-editor');
require('../modules/modal-accordion');
import { InfoCourse } from '../modules/InfoCourse';
import { RenderDashBoardCalendar } from '../modules/DashboardCalendar';
import { DashboardVideo } from '../modules/DashboardVideo';

var Page = (function () {
  function setScroll(elem, action) {
    if (action === 'lock') {
      $(elem).addClass('no-scroll');
    } else {
      $(elem).removeClass('no-scroll');
    }
  }

  function setInteractions() {
    $('.o-wrapper').on('click', function () {
      if ($(this).hasClass('open')) {
        setScroll('.header', 'unlock');
      } else {
        setScroll('.header', 'lock');
      }
    });

    $(document).ready(function () {
      if ($('.js-dashboard-video').length) {
        DashboardVideo();
      }

      if ($('.js-dashboard-calendar').length) {
        RenderDashBoardCalendar();
      }

      if ($('.js-tabnav').length) {
        setTabnavHeight();

        if ($('.js-infocourse').length) {
          InfoCourse();
        }

        $('.tabnav__label').on('click', function () {
          var _target = $(this).attr('data-tab');

          showTabContent($(this), _target);
        });
      }
    });

    $(window).on('resize orientationchange', function () {
      if ($('.js-tabnav').length) {
        setTabnavHeight();
      }
    });
  }

  function setTabnavHeight() {
    var _maxHeight = 0;
    var _elementHeight;
    var $contentWrapper = $('.tabnav__content-wrapper');

    $.each($('.tabnav__content'), function () {
      _elementHeight = $(this).outerHeight(true);
      if (_elementHeight >= _maxHeight) {
        _maxHeight = _elementHeight;
      }
    });

    $contentWrapper.height(_maxHeight);
  }

  function showTabContent(elem, target) {
    $(elem).addClass('selected').siblings().removeClass('selected');
    $('.tabnav__content--' + target)
      .addClass('is-visible')
      .siblings()
      .removeClass('is-visible');
  }

  function checkTopMenu() {
    var _ww = $(window).width();
    var $elem = $('.header').find('.navbar-collapse');
    var $toggleButton = $('.header').find('.navbar-toggle');
    var _collapsedClass = 'in';

    if (_ww >= 1024) {
      $elem.removeClass(_collapsedClass);
      $toggleButton.addClass('collapsed');
    }
  }

  function checkNewsHeight() {
    var _height = 100;
    var $elem = $('#user-panel-carousel').find('.item');

    $elem.each(function () {
      if ($(this).height() > _height) {
        _height = $(this).height();
      }
    });

    $('#user-panel-carousel').height(_height);
  }

  function Page() {
    $(window).resize(function () {
      checkTopMenu();
    });

    checkNewsHeight();

    setInteractions();
  }

  Page.prototype.setData = function () {};

  Page.prototype.load = function () {};

  return new Page();
})();

module.exports = Page;
