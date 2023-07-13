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
window.addEventListener('DOMContentLoaded', function() {
  manageTabNavigation('#lms_menu_container',1);
  manageTabNavigation('#middlearea',2);
  manageTabNavigation('#c-menu--slide-right',-1);
  slider = document.getElementById('c-menu--slide-right');
  slider_button = document.getElementById('c-button--slide-right');
  profile_button = document.getElementById('open_profile');

  slider_button.addEventListener("click", function() {
    manageTabNavigation('#lms_menu_container',-1);
    manageTabNavigation('#middlearea',-1);
    manageTabNavigation('#div_course',-1);
    manageTabNavigation('#footer', -1);
    manageTabNavigation('#c-menu--slide-right', 1);
    profile_button.setAttribute('aria-hidden', 'false');
  });

  profile_button.addEventListener("click", function(event) {
    manageTabNavigation('#lms_menu_container',1);
    manageTabNavigation('#middlearea',2);
    manageTabNavigation('#div_course',3);
    manageTabNavigation('#footer', 4);
    manageTabNavigation('#c-menu--slide-right', -1);
    profile_button.setAttribute('aria-hidden', 'true');
  });

  slider.addEventListener('keydown', function(event) {
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

});

function manageTabNavigation(selector, start_tab){

  if (selector.startsWith('.')) {
    divElement = document.querySelector(selector);
  } else if (selector.startsWith('#')) {
    var id = selector.slice(1);
    divElement = document.getElementById(id);
  }

  if (divElement) {
    const elements = divElement.querySelectorAll('a, button, input');
    let tabIndex = start_tab;
    elements.forEach(function(element) {
      element.setAttribute('tabindex', tabIndex);
    });
  }

}