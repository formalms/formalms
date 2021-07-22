window.Show = (function ($) {

  'use strict';


  $(document).ready(function () {

//    console.log('show loaded');

    $('.js-dashboard-graph').on('click', function () {
      var _selectedLabel = $(this).data('tab');

      $(this).addClass('selected').siblings().removeClass('selected');
      $('.graph__content--' + _selectedLabel).addClass('graph__content--visible').siblings().removeClass('graph__content--visible');
    });
  });


})(jQuery);


