let $videoModal = null;
let $videoModalContent = null;

export const DashboardVideo = () => {
    const elementsArray = document.querySelectorAll('.js-dashboard-video');

    for (var i = 0; i < elementsArray.length; i++) {
        elementsArray[i].addEventListener('click', onDashboardVideoClick);
    }

    $('#dashboard-video-modal').on('hidden.bs.modal', closeOverlay);
};

const onDashboardVideoClick = (event) => {

    $videoModal = $(`.js-dashboard-video-modal[data-videoId="${event.target.dataset.videoId}"]`);
    $videoModal.on('hidden.bs.modal', closeOverlay);

    $videoModalContent = $videoModal.find('.js-dashboard-video-dynamic-content');

    if (event.target.dataset.videoDataVideoUrl == 0 || !event.target.dataset.videoType == 'none') {
        return;
    }

    switch (event.target.dataset.videoType) {
        case 'yt':
            openYtVideo(event.target.dataset.videoDataVideoUrl);
            break;

        case 'vimeo':
            openVimeoVideo(event.target.dataset.videoDataVideoUrl);
            break;

        case 'mp4':
            openMp4Video(event.target.dataset.videoDataVideoUrl);
            break;

        default:
            return;
    }

    showOverlay();
};

const openYtVideo = (url) => {
    $videoModalContent
        .empty()
        .append(
            `<iframe width='100%' height='100%' style='max-width:100%;max-height:100%' src='https://www.youtube-nocookie.com/embed/${url}' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>`
        );
};

const openVimeoVideo = (url) => {
    $videoModalContent
        .empty()
        .append(
            `<div style='padding:0;position:relative;width:100%;height:100%;'><iframe src='https://player.vimeo.com/video/${url}?title=0&byline=0&portrait=0' style='position:absolute;top:0;left:0;width:100%;height:100%;' frameborder='0' allow='autoplay; fullscreen' allowfullscreen></iframe></div><script src='https://player.vimeo.com/api/player.js'></script>`
        );
};

const openMp4Video = (url) => {
    $videoModalContent.empty().append(
        `<video width="100%" controls>
      <source src="${url}" type="video/mp4">
      Your browser does not support HTML video.
    </video>`
    );
};

const showOverlay = () => {
    $videoModal.modal('show');
};

const closeOverlay = () => {
    if ($videoModalContent) {
        $videoModalContent.empty();
    }
    if ($videoModal) {
        $videoModal.off('hidden.bs.modal', closeOverlay);
    }
    $videoModal = null;
    $videoModalContent = null;
};
