let $modal = null;
let $modalContent = null;

export const InfoCourse = () => {
  console.log('init info course');

  const elementsArray = document.querySelectorAll('.js-infocourse-bio-trigger');

  for (var i = 0; i < elementsArray.length; i++) {
    elementsArray[i].addEventListener('click', onBioClick);
  }

  // $('#dashboard-video-modal').on('hidden.bs.modal', closeOverlay);
};

const onBioClick = (event) => {
  event.preventDefault();
  
  console.log('click su trigger');
  
  $modal = $('.js-infocourse-modal');
  $modal.on('hidden.bs.modal', closeOverlay);

  $modalContent = $modal.find('.js-infocourse-dynamic-content');

  // TODO: INSERIRE QUI I CONTENUTI
  // $modalContent.empty().append('<iframe width="100%" height="100%" style="max-width:100%;max-height:100%" src="https://www.youtube-nocookie.com/embed/1234" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>');

  $modal.modal();

  return false;
};

const closeOverlay = () => {
  $modalContent.empty();
  $modal.off('hidden.bs.modal', closeOverlay);
  $modal = null;
  $modalContent = null;
};
