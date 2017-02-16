window.TestQuestion = (function ($) {

  'use strict';

  /**
   *
   */
  var fetchQuestionsJSON = function(callback) {
    var data = [];

    $('.js-chart-container').children().each(function () {
      data.push({
        "class" : $(this).data('class'),
        "percent" : $(this).data('percent')
      });
    });

    callback(data);
  };

  /**
   *
   */
  var buildCharts = function(data) {

    console.log($('.domanda-numero-0-risposta-numero-0').length);

//    $.each(data, function (i, elem) {
//      console.log(elem);
//      new Chartist.Bar('.' + elem.class, {
//        series: [
//          elem.percent
//        ]
//      }, {
//        horizontalBars: true
//      });
//    });
  };

  $(document).ready(function () {
    fetchQuestionsJSON(function (data) {
      var charts = data;
      buildCharts(charts);
    });
  });

})(jQuery);

