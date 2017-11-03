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
    var _ajaxCallPath = (config.type === 'extended-text') ? 'extendedQuestDetails' : 'fileUploadQuestDetails';

    $.ajax({
      type: 'post',
      url: 'ajax.adm_server.php?r=lms/coursereport/' + _ajaxCallPath,
      data: _data,
      success: function (data) {
        var parsedData = JSON.parse(data);
        callback(parsedData);

        return parsedData;
      },
      error: function (e) {
        console.log('errore: ' + e.message);
        return false;
      }
    });
  };

  /**
   *
   * @param data
   * @param $elem
   */
  var buildQuestionDetails = function (data, $elem) {
    $elem.addClass('is-loaded');
    var $tableContainer = $elem.parents('table').find('.test-question__details-container');
    var _html;

    $.each(data.answers, function (i, item) {
      if (item.filePath) {
        _html = '<tr><td><div class="test-quetsion__answer"><a href="' + item.filePath + '" download>' + item.answer + '</a></div></td></tr>';
        $tableContainer.append(_html);
      } else {
        _html = '<tr><td><div class="test-quetsion__answer">' + item.answer + '</a></div></td></tr>';
        $tableContainer.append(_html);
      }
    });

    $tableContainer.addClass('test-question__details-container--is-visible');
  };

  /**
   * Function used to fetch the question details
   * @param $elem   {obj}   -   html element
   */
  var fetchQuestionDetails = function ($elem) {
    var _togglerActiveClass = 'test-question__show-more--active';
    var _tableContainerActiveClass = 'test-question__details-container--is-visible';
    var $tableContainer = $elem.parents('table').find('.test-question__details-container');

    if (!$elem.hasClass('is-loaded')) {
      loadQuestionDetails(function (data) {
        buildQuestionDetails(data, $elem);
      }, {
          idTest: $elem.attr('data-idtest'),
          idQuest: $elem.attr('data-idquest'),
          type: $elem.attr('data-type')
      });
    }

    $tableContainer.hasClass(_tableContainerActiveClass) ? $tableContainer.removeClass(_tableContainerActiveClass) : $tableContainer.addClass(_tableContainerActiveClass);
    $elem.hasClass(_togglerActiveClass) ? $elem.removeClass(_togglerActiveClass) : $elem.addClass(_togglerActiveClass);
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

