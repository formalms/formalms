window.TabNav = (function ($) {

  'use strict';

  $(document).ready(function () {

    console.log('ads');

    $(document).on('click', '.js-tab-nav', function () {

      console.log('asdasd');

      var _toggledClass = $(this).data('tab');

      $(this).addClass('selected').siblings().removeClass('selected');
      $('.user-tab--' + _toggledClass).addClass('is-visible').siblings().removeClass('is-visible');

    });
  });
})(jQuery);