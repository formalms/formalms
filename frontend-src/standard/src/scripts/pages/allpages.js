require('../modules/course-box');
require('../modules/folder-view');
require('../modules/slider-menu');
require('../modules/text-editor');
require('../modules/modal-accordion');
require('../../plugins/select2totree/js/select2totree');

import {InfoCourse} from '../modules/InfoCourse';
import {DashBoardCalendar} from '../modules/DashboardCalendar';
import {DashboardVideo} from '../modules/DashboardVideo';
import LearningObject from '../modules/Base/LearningObject';
import TeacherLearningObject from '../modules/TeacherLearningObject';
import StudentLearningObject from '../modules/StudentLearningObject';
import Lang from '../helpers/Lang';

// eslint-disable-next-line no-unused-vars
import FormaDropZone from './../components/Dropzone';
import FormaTable from './../components/FormaTable';
import ModalElement from './../components/Modal';
import Axios from 'axios';
import Select2 from 'select2';
import Chart from 'chart.js';

var Page = (function () {
    window.frontend.modules = {};
    window.frontend.helpers = {};
    window.TeacherLearningObject = TeacherLearningObject;
    window.StudentLearningObject = StudentLearningObject;
    window.frontend.modules.LearningObject = LearningObject;
    window.frontend.modules.TeacherLearningObject = TeacherLearningObject;
    window.frontend.modules.StudentLearningObject = StudentLearningObject;
    window.frontend.modules.FormaDropZone = FormaDropZone;
    window.frontend.modules.Select2 = Select2;
    window.frontend.modules.Chart = Chart;
    window.frontend.modules.FormaTable = FormaTable;
    window.frontend.modules.Modal = ModalElement;
    window.frontend.helpers.Lang = Lang;
    window.frontend.helpers.Axios = Axios;
   

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
                DashBoardCalendar();
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


            $(document).on('click','.js-scorm_lightbox', function (e) {
                e.preventDefault();
                var _src = $(this).attr('href');
                var _title = $(this).attr('title');
                //let src = el.querySelector('.fv-is-play').getAttribute('href');
                let learningObject = new window.frontend.modules.LearningObject();
                learningObject.scormLightbox(_src, _title, 'organization','#container');

            });

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

    Page.prototype.setData = function () {
    };

    Page.prototype.load = function () {
    };

    return new Page();
})();

module.exports = Page;
