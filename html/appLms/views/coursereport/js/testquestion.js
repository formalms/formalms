window.TestQuestion = (function ($) {

  'use strict';

  /**
   * Ajax call used to load the details of particular questions
   * @param callback   {function}   -   callback function
   * @param idTest   {int}   -    the id of the test
   * @param idQuest   {int}   -   the id of the question
   */
  var loadQuestionDetails = function (callback, idTest, idQuest) {

    var _data = {
      'id_test': idTest,
      'id_quest': idQuest
    };

    $.ajax({

      type: 'post',
      url: 'ajax.adm_server.php?r=lms/coursereport/fileUploaQuestDetails',
      data: _data,
      beforeSend: function () {
        $('.loading').html(_loadingSnippet);
      },
      success: function (data) {
        $('.loading').html('');
        var parsedData = JSON.parse(data);

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
   * this function binds basic interactions
   */
  var setInteractions = function () {
    $('.js-show-more').on('click', function () {

    });
  };

  $(document).ready(function () {
    setInteractions();
  })

})(jQuery);

