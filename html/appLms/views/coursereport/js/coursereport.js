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
	var loadUserData = function (callback, tests, filter, round_redo, pagination) {

		var _data = {
			'courseId': 'id',
			'type_filter': filter || false,
			'pagination': (pagination ? pagination.page : 0),
			'currentPage': (pagination ? pagination.current : 0)
		};

		if (round_redo) {
			if (round_redo.charAt(0) == 'a') {
				_data['round_report'] = round_redo.substring(1);
			} else {
				_data['redo_final'] = round_redo.substring(1);
			}
		}

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
				$('.js-user-detail-filter').removeAttr('disabled');
				$('.js-user-detail-filter').removeClass('is-disabled');
				var parsedData = JSON.parse(data);

				if (!$('.js-pagination').hasClass('is-loaded')) {
					$(this).empty();
					buildPagination(parsedData.pagination);
				}

				callback(parsedData);

				return parsedData;
			},
			complete: function () {
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
				"id": $(elem).data('activityid')
			});
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
	 * function use to build the pagination
	 */
	var buildPagination = function (pagination) {
		var _pages = pagination.countPages;
		var _currentPage = pagination.currentPage;
		var $container = $('.js-pagination');
		var _html = '';

		for (var i = 0; i < _pages; i++) {
			if (i === _currentPage) {
				_html += '<a class="js-pagination-goto is-active" href="javascript:void(0);" data-page="' + i + '">' + (i + 1) + '</a>';
			} else {
				_html += '<a class="js-pagination-goto" href="javascript:void(0);" data-page="' + i + '">' + (i + 1) + '</a>';
			}
		}

		$container.append(_html);
		$container.addClass('is-loaded');
	};

	/**
	 * function used to navigate the pages
	 */
	var navigatePage = function (page, current) {
		var _userData;
		var _filter = $('.js-user-level-filter').val();
		var _pagination = {
			page: page,
			currentPage: current
		};

		clearDetailTable();

		loadUserData(function (data) {
			_userData = data;
			fillTable(_userData);
		}, testData, _filter, '', _pagination);
	};

	/**
	 * function used to populate the test select
	 */
	var fillActivitiesFilter = function () {
		// var activities = testData;
		// var $filter;
		// var _selected;
		// var _option;
		//
		// for (var i = 0; i < 4; i++) {
		// 	$filter = $($('.js-test-filter')[i]);
		// 	$.each(activities, function (j, elem) {
		// 		_selected = (j === i) ? ' selected' : '';
		// 		_option = '<option value="' + elem.id + '"' + _selected + '>' + elem.name + '</option>';
		// 		$filter.append(_option);
		// 	});
		// }
	};


	/**
	 * function used to fetch the info filters of users
	 * @param callback   {function}   -   callback used to build the <select> that contains the user info
	 */
	var loadUserInfoFilter = function (callback) {
		$.ajax({

			type: 'post',
			url: 'ajax.adm_server.php?r=lms/coursereport/getUserFieldsSelector',
			success: function (data) {
				var parsedData = JSON.parse(data);

				$('.js-user-detail-filter').removeAttr('disabled');
				$('.js-user-detail-filter').removeClass('is-disabled');

				callback(parsedData);

				return parsedData;
			},
			error: function (e) {
				$('.loading').html('errore: ' + e.message);
				return false;
			}
		});
	};


	var fillUserInfoFilter = function (data) {

		var $filter = $('.js-user-detail-filter');
		$filter.empty();
		var _option;

		$.each(data, function (i, elem) {

			if (i === 'email') {
				_option = '<option value="' + i + '" selected>' + elem + '</option>';
			} else {
				_option = '<option value="' + i + '">' + elem + '</option>';
			}

			$filter.append(_option);
		});
	};

	/**
	 * function used to parse the test result
	 * @param result
	 */
	var parseResult = function (result) {

		var _parsed = [];
		var _showIcon;
		var _active;
		var _link;

		$.each(result, function (i, elem) {
			_showIcon = elem.showIcon;
			_active = elem.active;
			_link = elem.link;

			if (_active) {
				if (_showIcon) {
					_parsed.push('<a href="' + _link + '"><i class="fa fa-check"></i></a>');
				} else {
					_parsed.push('<a href="' + _link + '">' + elem.value + '</a>');
				}
			}
			else {
				if (_showIcon) {
					_parsed.push('<span><i class="fa fa-check"></i></span>');
				} else {
					_parsed.push('<span>' + elem.value + '</span>');
				}
			}
		});

		return _parsed.join(' ');
	};


	//FIXME
	/**
	 * function used to populate the student row
	 * @param   {object}   student   -   object with the student data
	 */
	var buildStudentRow = function (student) {
		var _student = '<tr class="student" data-student="' + student.id + '">';
		_student += '<td class="student__name">' + student.name + '</td>';
		_student += '<td class="student__info">' + student.email + '</td>';

		for (var i = 0; i < student.activities_results.length; i++) {
			_student += '<td class="student__test-result student__test-result--' + i + '">' + parseResult(student.activities_results[i]) + '</td>';
		}

		_student += '<td class="student__total-result">' + student.total_result + '</td>';
		_student += '</tr>';

		return _student;
	};

	var fillTable = function (data) {
		var _students = data.details['students'];
		var _redoFinal = data.details['redo-final'];
		var _roundReport = data.details['round-report'];
		var _editFinal = data.details['edit-final'];

		$('.redo-final').attr('data-reportid', _redoFinal.idReport);
		$('.round-report').attr('data-reportid', _roundReport.idReport);
		$('.edit-final').attr('data-reportid', _editFinal.idReport);
		$('.edit-final').attr('href', _editFinal.link);

		$.each(_students, function (i, elem) {
			$table.append(buildStudentRow(elem));
		});

        initDataTables();

	};

	var initDataTables = function() {
        let table = $('#table-details').DataTable();
        table.destroy();
        table = $('#table-details').DataTable( {
            paging: true,
            "language": {
                "info": "",
            }
        } );
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

	/**
	 * function used to filter users by level
	 * @param filter
	 */
	var filterUsersByLevel = function (filter) {
		var userData;

		clearPagination();
		clearDetailTable();

		loadUserData(function (data) {
			userData = data;
			fillTable(userData);
		}, testData, filter);
	};

	/**
	 * function used to recount the table in case of rounding or redoing
	 * @param filter   {string}   -   filter type with an ID
	 */
	var recountTable = function (filter) {
		var userData;

		clearPagination();
		clearDetailTable();

		loadUserData(function (data) {
			userData = data;
			fillTable(userData);
		}, testData, false, filter);
	};

	/**
	 * function used to clear the pagination
	 */
	var clearPagination = function () {
		$('.js-pagination').removeClass('is-loaded');
		$('.js-pagination').empty();
	};


	//HERE
	/**
	 * function used to clear the detail table
	 */
	var clearDetailTable = function () {

		$('.js-details-table').empty();
		$('.js-activity-name').each(function (i, elem) {
			elem.remove();
		});

		$('.js-user-level-filter').attr('disabled', true);
		$('.js-user-level-filter').addClass('is-disabled');

		$('.js-user-detail-filter').attr('disabled', true);
		$('.js-user-detail-filter').addClass('is-disabled');

	};


	var setTestVisibility = function (id, state) {
		var el = $('.js-set-detail-visibility');
		var _data = {
			'idReport': id,
			'showInDetail': state
		};

		$.ajax({

			type: 'post',
			url: 'ajax.adm_server.php?r=lms/coursereport/setVisibleInDetail',
			data: _data,
			beforeSend: function () {
				el.addClass('is-disabled');
			},
			success: function () {
				el.removeClass('is-disabled');
			},
			error: function (e) {
				console.log(e);
			}
		});
	};

	var buildActivitiesRow = function (activities) {
		var html = '';
		var el = $('.js-final-score');

		$.each(activities, function (i, elem) {
			html = '<th class="js-activity-name acivity-name">' + elem + '</th>';
			$(html).insertBefore(el);
		});
	};

	var setInteractions = function () {

		var fixHelper = function (e, ui) {
			ui.children().each(function () {
				$(this).width($(this).width());
			});
			return ui;
		};

		$('.course-sortable').sortable({
			handle: '.handle',
			helper: fixHelper
		}).disableSelection();


		$('.js-set-detail-visibility').click(function () {
			var id = $(this).attr('data-value');

			if ($(this).is(':checked')) {
				setTestVisibility(id, 1);
			} else {
				setTestVisibility(id, 0);
			}
		});
	};

	$(document).ready(function () {

		//$('#yui-main-boot').addClass('col-md-12');
		//$('#yui-main-boot').removeClass('col-md-9');


		$table = $('.js-details-table');
		var userData;
		var userInfo;

		$('.js-details').on('click', function () {
			clearDetailTable();

			testData = loadActivitiesData();

			loadUserData(function (data) {
				userData = data;
				buildActivitiesRow(data.names);
				fillTable(userData);
			}, testData);

			loadUserInfoFilter(function (data) {
				userInfo = data;
				fillUserInfoFilter(userInfo);
			});

			fillActivitiesFilter();

		});

		$('.js-finals-filter').on('click', function () {
			var _selectedFilter;

			if ($(this).hasClass('round-report')) {
				_selectedFilter = 'a' + $(this).attr('data-reportid');
			} else {
				_selectedFilter = 'r' + $(this).attr('data-reportid');
			}

			recountTable(_selectedFilter);

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

		$('body').on('click', '.js-pagination-goto', function () {
			var $elem = $(this);
			var _page = $elem.attr('data-page');
			var _current = $elem.siblings('.is-active').attr('data-page');

			$elem.siblings().removeClass('is-active');
			$elem.addClass('is-active');

			navigatePage(_page, _current);
		});


		setInteractions();

	});

})(jQuery);


