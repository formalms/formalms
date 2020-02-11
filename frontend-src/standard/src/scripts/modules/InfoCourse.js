let $modal = null;
let $modalContent = null;
let $modalTitle = null;
let $modalImg = null;
let $modalRole = null;

export const InfoCourse = () => {
  const elementsBioArray = document.querySelectorAll('.js-infocourse-bio-trigger');

  for (var i = 0; i < elementsBioArray.length; i++) {
    elementsBioArray[i].addEventListener('click', onBioClick);
  }

  $modal = $('.js-infocourse-modal');
  $modal.on('hidden.bs.modal', closeOverlay);

  $modalContent = $modal.find('.js-infocourse-dynamic-content');
  $modalTitle = $('.js-infocourse-dynamic-title');
  $modalImg = $('.js-infocourse-dynamic-img');
  $modalRole = $('.js-infocourse-dynamic-role');
};

const onBioClick = (event) => {
  event.preventDefault();

  $modalContent.empty().html($(event.target).parent().find('.js-infocourse-bio-text').html());
  $modalTitle.empty().html(event.target.dataset.title);
  $modalImg.css('backgroundImage', `url(${event.target.dataset.image})`);
  $modalRole.empty().html(event.target.dataset.role);

  $modal.modal();

  return false;
};

const closeOverlay = () => {
  $modalContent.empty();
  $modalTitle.empty();
  $modalRole.empty();
  $modalImg.css('background-image', '');
  $modal.off('hidden.bs.modal', closeOverlay);
};
