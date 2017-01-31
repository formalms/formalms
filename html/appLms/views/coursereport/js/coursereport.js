window.CourseReport = (function ($) {

    'use strict';

    var _loadingSnippet = 'Loading...';

    var $detailsTab = $('.js-details');
    var $detailsTableRow;
    var $table;

    var testData;

    var activities;

    /**
     * ajax call used to populate the details table
     * @param   {function}   callback   -   callback used to handle the data given back from the ajax
     * @param   {array}   tests   -   array with the ids of the tests
     * @param   {int}   maxColumns   -   number of columns in the detail table - FOR FUTURE IMPLEMENTATIONS
     * @param   {int}   filter   -   filter code
     */
    var loadUserData = function (callback, tests, filter) {

        var _data = {
            'courseId': 'id',
            'selected_tests': [],
            'type_filter': filter || false
        };

//        var _maxCol = maxColumns;
        var _maxCol = 4;

        $.each(tests, function (i, elem) {
          _data['selected_tests'].push(elem.id);

          if (i > (_maxCol-1)) {
            return false;
          }
        });

        $.ajax({

            type: 'post',
            url: 'ajax.adm_server.php?r=lms/coursereport/getDetailCourseReport',
            data: _data,
            beforeSend: function () {
                $('.loading').html(_loadingSnippet);
            },
            success: function (data) {
                $('.loading').html('');
                $('.js-user-level-filter').removeAttr('disabled');
                $('.js-user-level-filter').removeClass('is-disabled');
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
     * ajax call used to populate the test select in the details table
     */
    var loadActivitiesData = function () {

        var $elem = $('.activities-container');
        var _activities = [];
        var _name, _id;

        $elem.children('div').each(function (i, elem) {
          _name = $(elem).data('activityid');
          _id = $(elem).data('activityname');
          _activities.push({
            "name": $(elem).data('activityname'),
            "id": $(elem).data('activityid')});
        });

        return _activities;
    };

    /**
     * function used to update the test column
     * @param testId
     * @param testName
     * @param column
     */
    var fetchTestResults = function (testId, testName, column) {

        var _test = [];
        var _activitiesResults = [];

        _test.push({
          "name": testName,
          "id": testId
        });

        loadUserData(function (data) {

            var _students = data.details['students'];
            var _studentId, _testResult;

            $.each(_students, function (i, elem) {
                _studentId = elem.id;
                _testResult = parseResult(elem.activities_results[0]);

                _activitiesResults.push({
                  'result': _testResult,
                  'user': _studentId
                });

            });

            $.each(_activitiesResults, function (i, elem) {
              $('.student[data-student="' + elem.user + '"]').children('.student__test-result--' + column).html(elem.result);
            });

        }, _test);

        return _activitiesResults;

    };

    /**
     * function used to populate the test select
     */
    var fillActivitiesFilter = function () {
        var activities = testData;
        var $filter;
        var _selected;
        var _option;

        for (var i = 0; i < 4; i++) {
            $filter = $($('.js-test-filter')[i]);
            $.each(activities, function (j, elem) {
                _selected = (j === i) ? ' selected' : '';
                _option = '<option value="' + elem.id + '"' + _selected + '>' + elem.name + '</option>';
                $filter.append(_option);
            });
        }
    };

    /**
     * function used to parse the test result
     * @param result
     */
    var parseResult = function (result) {

        var _parsed = [];
        var _showIcon;

        $.each(result, function (i, elem) {
          _showIcon = elem.showIcon;

          if (_showIcon === 'true') {
            _parsed.push('<a href="' + elem.link + '"><i class="fa fa-check"></i></a>');
          } else {
            _parsed.push('<a href="' + elem.link + '">' + elem.value + '</a>');
          }

        });

        return _parsed.join(' ');
    };

    /**
     * function used to populate the student row
     * @param   {object}   student   -   object with the student data
     */
    var buildStudentRow = function (student) {
        var _student = '<tr class="student" data-student="' + student.id + '">';
        _student += '<td class="student__name">' + student.name + '</td>';
        _student += '<td class="student__info">' + student.email + '</td>';


        for (var i = 0; i < 4; i++) {
            _student += '<td class="student__test-result student__test-result--' + i + '">' + parseResult(student.activities_results[i]) + '</td>';
        }

        _student += '<td class="student__total-result">' + student.total_result + '</td>';
        _student += '</tr>';

        return _student;
    };

    var fillTable = function (data) {
        var students = data.details['students'];

        $.each(students, function (i, elem) {
            $table.append(buildStudentRow(elem));
        });

    };

    /**
     * function used to update user info based on the filter
     * @param   {object}   data   -   JSON with the student data
     * @param   {string}   info   -   user filter value
     */
    var updateUsersInfo = function (data, info) {
        var _students = data.details['students'];
        var _student;

        $.each($table.children('.student'), function (i, elem) {
            _student = _students[i];
            $(elem).children('.student__info').html(_student[info]);
        });
    };

    /**
     * function used to update the result column of every student based on the filtered test
     * @param   {int}   testId   -   test id
     * @param   {string}   testName   -   name of the test
     * @param   {int}   column   -   column to update
     */
    var updateUsersTestResults = function (testId, testName, column) {
        var _results = fetchTestResults(testId, testName, column);

    };

    var filterUsersByLevel = function (filter) {
        var userData;

        clearDetailTable();

        loadUserData(function (data) {
            userData = data;
            fillTable(userData);
        }, testData, filter);
    };

    /**
     * function used to clear the detail table
     */
    var clearDetailTable = function () {

        $('.js-details-table').empty();
    };


    $(document).ready(function () {

        $('#yui-main-boot').addClass('col-md-12');
        $('#yui-main-boot').removeClass('col-md-9');

        $table = $('.js-details-table');
        var userData;

        $('.js-details').on('click', function () {
            clearDetailTable();

            testData = loadActivitiesData();

            loadUserData(function (data) {
                userData = data;
                fillTable(userData);
            }, testData);

            fillActivitiesFilter();
        });

        $('.js-user-level-filter').on('change', function () {
           filterUsersByLevel($(this).val());
        });

        $('.js-user-detail-filter').on('change', function () {
            var _info = $(this).val();

            updateUsersInfo(userData, _info);
        });

        $('.js-test-filter').on('change', function () {
            var _column = $(this).data('test');
            var _testId = $(this).val();
            var _testName = $(this).text();

            updateUsersTestResults(_testId, _testName, _column);
        });

        $('.button--add').on('click', function () {
            $(this).toggleClass('active');
        });

    });

})(jQuery);


