var CourseBox = (function() {
  // dates modal
  $(document)
    .on('click', '.js-course-box-open-dates-modal', function() {
      // selecting target modal
      var _$modal = $(this).siblings('.course-box__modal');
      // preventing document scroll
      $('html').addClass('no-scroll');
      // closing currently open modal (eventually)
      $('.course-box__modal.is-open').removeClass('is-open');
      // opening target modal
      _$modal.addClass('is-open');
    })
    .on('click', '.js-course-box-close-dates-modal', function() {
      var _$modal = $(this).parents('.course-box__modal');
      _$modal.removeClass('is-open');
      $('html').removeClass('no-scroll');
    });
})();

module.exports = CourseBox;
