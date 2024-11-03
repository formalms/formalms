window.TabNav = (function ($) {

  'use strict';

  $(document).ready(function () {

    $(document).on('click', '.js-tab-nav', function () {
      var _toggledClass = $(this).data('tab');
      $(this).addClass('selected').siblings().removeClass('selected');
      $('.user-tab--' + _toggledClass).addClass('is-visible').siblings().removeClass('is-visible');

    });
  });
})(jQuery);


let slider;
let slider_button;
let profile_button
window.addEventListener('DOMContentLoaded', function () {
  manageTabNavigation('#c-menu--slide-right', -1);
  slider = document.getElementById('c-menu--slide-right');
  profile_button = document.getElementById('open_profile');
  slider_button = document.getElementById('c-button--slide-right');

  if (slider_button) {
    slider_button.addEventListener("click", function () {
      manageTabNavigation('#c-menu--slide-right', 1);
      profile_button.setAttribute('aria-hidden', 'false');
    });
  }

  if (profile_button) {
    profile_button.addEventListener("click", function (event) {
      manageTabNavigation('#c-menu--slide-right', -1);
      profile_button.setAttribute('aria-hidden', 'true');
    });
  }

  if (slider) {
    slider.addEventListener('keydown', function (event) {
      if (event.key === 'Tab') {
        const focusableElements = slider.querySelectorAll('a, button, input');
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (event.shiftKey && document.activeElement === firstElement) {
          event.preventDefault();
          lastElement.focus();
        } else if (!event.shiftKey && document.activeElement === lastElement) {
          event.preventDefault();
          firstElement.focus();
        }
      }
    });
  }

  $('div.menu-area a').bind('click', function (event) {
    event.preventDefault();
    id = $(this).attr('rel');
    $('ul.float-left').hide();
    $('ul#' + id).show();
    $('div.menu-area').removeClass('menu-selected');
    $(this).parent().addClass('menu-selected');
  });

  $("#accordion").accordion({
    collapsible: true,
    active: false,
    icons: false
  });

  // feedback
  $('#container-feedback').click(function () {
    event.preventDefault();
    $(this).fadeOut(500);
  });

  // setting correct navigation with keyboard tab
  $('#c-menu--slide-right a, #c-menu--slide-right input, #c-menu--slide-right select, #c-menu--slide-right button, #c-menu--slide-right textarea').attr('tabindex',-1);
  $('#c-menu--slide-right a, #c-menu--slide-right input, #c-menu--slide-right select, #c-menu--slide-right button, #c-menu--slide-right textarea').attr('aria-hidden',true);
  $('#course_search_simple_filter_options .selectpicker').attr('tabindex',0);
  $('#course_search_simple_filter_options .selectpicker').attr('aria-label','Select');

  $("#c-button--slide-right").click(function(){
    $('#c-menu--slide-right a, #c-menu--slide-right input, #c-menu--slide-right select, #c-menu--slide-right button, #c-menu--slide-right textarea').attr('tabindex',0);
    $('#c-menu--slide-right a, #c-menu--slide-right input, #c-menu--slide-right select, #c-menu--slide-right button, #c-menu--slide-right textarea').attr('aria-hidden',false);
  });
  $(".c-menu__close").click(function(){
    $('#c-menu--slide-right a, #c-menu--slide-right input, #c-menu--slide-right select, #c-menu--slide-right button, #c-menu--slide-right textarea').attr('tabindex',-1);
    $('#c-menu--slide-right a, #c-menu--slide-right input, #c-menu--slide-right select, #c-menu--slide-right button, #c-menu--slide-right textarea').attr('aria-hidden',true);
  });


});

function manageTabNavigation(selector, start_tab) {
  if (selector.startsWith('.')) {
    divElement = document.querySelector(selector);
  } else if (selector.startsWith('#')) {
    var id = selector.slice(1);
    divElement = document.getElementById(id);
  }
  if (divElement) {
    const elements = divElement.querySelectorAll('a, button, input');
    let tabIndex = start_tab;
    elements.forEach(function (element) {
      element.setAttribute('tabindex', tabIndex);
    });
  }

}