let $videoModal = null;
let $videoModalContent = null;

export const DashboardVideo = () => {
  const elementsArray = document.querySelectorAll('.js-dashboard-video');

  for (var i = 0; i < elementsArray.length; i++) {
    elementsArray[i].addEventListener('click', onDashboardVideoClick);
  }

  $('#dashboard-video-modal').on('hidden.bs.modal', closeOverlay);
}

const onDashboardVideoClick = (event) => {
  $videoModal = $(`.js-dashboard-video-modal[data-href=${event.target.dataset.videoType}-${event.target.dataset.videoUrl}]`);
  $videoModal.on('hidden.bs.modal', closeOverlay);

  $videoModalContent = $videoModal.find('.js-dashboard-video-dynamic-content');

  switch (event.target.dataset.videoType) {
    case 'yt':
      openYtVideo(event.target.dataset.videoUrl);
      break;

    case 'vimeo':
      openVimeoVideo(event.target.dataset.videoUrl)
      break;
  }

  showOverlay();
}

const openYtVideo = (url) => {
  $videoModalContent.empty().append(`<iframe width="100%" height="100%" style="max-width:100%;max-height:100%" src="https://www.youtube-nocookie.com/embed/${url}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`);
}

const openVimeoVideo = (url) => {
  $videoModalContent.empty().append(`<div style="padding:0;position:relative;width:100%;height:100%;"><iframe src="https://player.vimeo.com/video/${url}?title=0&byline=0&portrait=0" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe></div><script src="https://player.vimeo.com/api/player.js"></script>`)
}

const showOverlay = () => {
  $videoModal.modal();
}

const closeOverlay = () => {
  $videoModalContent.empty();
  $videoModal.off('hidden.bs.modal', closeOverlay);
  $videoModal = null;
  $videoModalContent = null;
}