window.TestQuestion = (function ($) {

  'use strict';

  /**
   * Ajax call used to load the details of particular questions
   * @param callback   {function}   -   callback function
   * @param config   {obj}   -   config object for the ajax call
   */
  var loadQuestionDetails = function (callback, config) {

    var _data = {
      'id_test': config.idTest,
      'id_quest': config.idQuest
    };
    var _ajaxCallPath;

    (config.type == 'extended-text') ? _ajaxCallPath = 'extendedQuestDetails' : _ajaxCallPath = 'fileUploadQuestDetails';

    $.ajax({

      type: 'post',
      url: 'ajax.adm_server.php?r=lms/coursereport/' + _ajaxCallPath,
      data: _data,
      beforeSend: function () {
//        $('.loading').html(_loadingSnippet);
      },
      success: function (data) {
        $('.loading').html('');
        var parsedData = JSON.parse(data);

        console.log(parsedData);

        callback(parsedData);

        return parsedData;
      },
      error: function (e) {
        $('.loading').html('errore: ' + e.message);
        return false;
      }
    });
  };

  /**
   *
   * @param data
   */
  var buildQuestionDetails = function (data) {

  };

  /**
   * Function used to fetch the question details
   * @param $elem   {obj}   -   html element
   */
  var fetchQuestionDetails = function ($elem) {
    var _activeClass = 'test-question__show-more--active';
    var _config = {};

    if (!$elem.hasClass('is-loaded')) {
      _config.idTest = $elem.data('idtest');
      _config.idQuest = $elem.data('idquest');
      _config.type = $elem.data('type');

      loadQuestionDetails(function (data) {
        buildQuestionDetails(data);
        $elem.addClass('is-loaded');
      }, _config);
    }

    $elem.hasClass(_activeClass) ? $elem.removeClass(_activeClass) : $elem.addClass(_activeClass);
  };

  /**
   * this function binds basic interactions
   */
  var setInteractions = function () {
    $('.js-show-more').on('click', function () {
      fetchQuestionDetails($(this));
    });
  };

  $(document).ready(function () {
    setInteractions();
  })

})(jQuery);

